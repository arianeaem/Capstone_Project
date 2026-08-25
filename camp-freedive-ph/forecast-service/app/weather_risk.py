"""
Weather risk scoring + booking assessment logic.

Extracted from weather_risk_assessment.ipynb. This module intentionally
does NOT import xgboost or load any trained model files — assess_booking()
(the function used by the booking flow) only needs live Open-Meteo data
and the fixed scoring rules below. If get_risk_assessment(horizon) (the
XGBoost-model path) is ever needed, it can be added back in separately —
see the notebook's cell 2 for that function if required.
"""

import numpy as np
import pandas as pd
import requests

# ---------------------------------------------------------------------------
# Site + API configuration
# ---------------------------------------------------------------------------

LAT = 13.7481
LON = 120.9408

SITE_TIMEZONE = "Asia/Manila"
MAX_BOOKING_DAYS = 16

BOOKING_MARINE_URL = "https://marine-api.open-meteo.com/v1/marine"
BOOKING_WEATHER_URL = "https://api.open-meteo.com/v1/forecast"

OPEN_WATER_WINDOWS = (("09:30", "12:00"), ("15:30", "17:30"))

BOOKING_MARINE_VARIABLES = {
    "wave_height": "wave_height",
    "wave_period": "wave_period",
    "swell_height": "swell_wave_height",
    "wind_wave_height": "wind_wave_height",
    "ocean_current": "ocean_current_velocity",  # required field, no zero-fallback
}
BOOKING_WEATHER_VARIABLES = {
    "rain": "rain",
    "sea_level_pressure": "pressure_msl",
    "wind_speed": "wind_speed_10m",
    "wind_direction": "wind_direction_10m",
}

# ---------------------------------------------------------------------------
# Scoring functions (0-4 risk score per variable)
# ---------------------------------------------------------------------------


def score_wave_height(v):
    if v < 0.3:
        return 0
    elif v < 0.5:
        return 1
    elif v < 0.8:
        return 2
    elif v < 1.0:
        return 3
    else:
        return 4


def score_swell_height(v):
    return score_wave_height(v)


def score_wind_wave_height(v):
    if v < 0.2:
        return 0
    elif v < 0.4:
        return 1
    elif v < 0.6:
        return 2
    elif v < 0.8:
        return 3
    else:
        return 4


def score_wave_period(v):
    if v > 7:
        return 0
    elif v > 5:
        return 1
    elif v > 3:
        return 2
    elif v > 2:
        return 3
    else:
        return 4


def score_ocean_current(v):
    if v < 0.1:
        return 0
    elif v < 0.3:
        return 1
    elif v < 0.5:
        return 2
    elif v < 0.8:
        return 3
    else:
        return 4


def score_rain(v):
    if v < 1:
        return 0
    elif v < 5:
        return 1
    elif v < 15:
        return 2
    elif v < 30:
        return 3
    else:
        return 4


def score_sea_level_pressure(v):
    if v > 1012:
        return 0
    elif v > 1008:
        return 1
    elif v > 1004:
        return 2
    elif v > 1000:
        return 3
    else:
        return 4


def score_wind_direction(deg):
    if (0 <= deg <= 90) or (315 < deg <= 360):
        return 0
    elif deg <= 135:
        return 1
    elif deg <= 180 or deg > 270:
        return 2
    else:
        return 3


WEIGHTS = {
    "wave_height": 0.160,
    "wave_period": 0.138,
    "swell_height": 0.138,
    "ocean_current": 0.138,
    "wind_wave_height": 0.128,
    "rain": 0.085,
    "sea_level_pressure": 0.085,
    "tide_height": 0.085,
    "wind_direction": 0.043,
}


def compute_weighted_risk_score(scores):
    max_possible = 4 * sum(WEIGHTS.values())
    weighted_sum = sum(scores[var] * WEIGHTS[var] for var in WEIGHTS)
    return (weighted_sum / max_possible) * 100


def classify_risk(weighted_pct):
    if weighted_pct <= 20:
        return "Very Safe"
    elif weighted_pct <= 40:
        return "Safe"
    elif weighted_pct <= 60:
        return "Moderate"
    elif weighted_pct <= 80:
        return "High Risk"
    else:
        return "Critical Risk"


def check_overrides(
    tcws_signal: int = 0,
    gale_warning: bool = False,
    thunderstorm_advisory: bool = False,
    typhoon_within_distance: bool = False,
    tsunami_warning: bool = False,
) -> bool:
    """
    Returns True if ANY override condition is active, per PRD:
    - TCWS Signal #3 or higher
    - Gale Warning
    - Thunderstorm/Lightning Advisory
    - Typhoon within predefined safety distance
    - Tsunami Warning
    """
    return (
        tcws_signal >= 3
        or gale_warning
        or thunderstorm_advisory
        or typhoon_within_distance
        or tsunami_warning
    )


MEANING_MAP = {
    "Very Safe": "Proceed with planned operations.",
    "Safe": "Proceed with caution.",
    "Moderate": "Proceed only after risk assessment; monitor conditions closely.",
    "High Risk": (
        "Postpone or cancel unless there is a compelling operational reason "
        "and experienced personnel can manage the risk."
    ),
    "Critical Risk": "Cancel all freedive operations.",
}


def assess_weather_risk(scores: dict, overrides: dict = None) -> dict:
    """
    Combines weighted scoring with override logic.

    scores: dict of variable -> risk score (0-4)
    overrides: dict of override flags (see check_overrides args), or None
    """
    if overrides is None:
        overrides = {}

    if check_overrides(**overrides):
        return {
            "weighted_score_pct": None,  # score is ignored per PRD when override triggers
            "classification": "Critical Risk",
            "recommended_action": MEANING_MAP["Critical Risk"],
            "override_triggered": True,
        }

    weighted_pct = compute_weighted_risk_score(scores)
    classification = classify_risk(weighted_pct)

    return {
        "weighted_score_pct": round(weighted_pct, 2),
        "classification": classification,
        "recommended_action": MEANING_MAP[classification],
        "override_triggered": False,
    }


# ---------------------------------------------------------------------------
# Booking-window helpers
# ---------------------------------------------------------------------------


def _time_to_minutes(value):
    hour, minute = map(int, value.split(":"))
    return hour * 60 + minute


def validate_open_water_window(start_time, end_time):
    start_minutes = _time_to_minutes(start_time)
    end_minutes = _time_to_minutes(end_time)

    for window_start, window_end in OPEN_WATER_WINDOWS:
        if (
            start_minutes >= _time_to_minutes(window_start)
            and end_minutes <= _time_to_minutes(window_end)
            and start_minutes < end_minutes
        ):
            return f"{window_start}-{window_end}"

    allowed_windows = ", ".join(
        f"{window_start}-{window_end}" for window_start, window_end in OPEN_WATER_WINDOWS
    )
    raise ValueError(
        f"Dive window {start_time}-{end_time} is outside the open-water windows: {allowed_windows}"
    )


def _booking_timestamps(planned_date, dive_start, dive_end, now=None):
    start_date = pd.Timestamp(f"{planned_date} {dive_start}", tz=SITE_TIMEZONE)
    end_date = pd.Timestamp(f"{planned_date} {dive_end}", tz=SITE_TIMEZONE)
    if end_date <= start_date:
        raise ValueError("dive_end must be later than dive_start on the same date.")

    current_time = pd.Timestamp.now(tz=SITE_TIMEZONE) if now is None else pd.Timestamp(now)
    if current_time.tzinfo is None:
        current_time = current_time.tz_localize(SITE_TIMEZONE)
    else:
        current_time = current_time.tz_convert(SITE_TIMEZONE)

    if start_date < current_time:
        raise ValueError(f"The planned dive time has already passed: {start_date}.")

    return start_date, end_date, current_time


def _booking_recheck_schedule(start_date, current_time):
    checkpoints = {
        "booking": current_time,
        "72_hours_before": start_date - pd.Timedelta(hours=72),
        "24_hours_before": start_date - pd.Timedelta(hours=24),
        "dive_day": start_date.normalize(),
        "before_entry": start_date,
    }
    return {
        name: timestamp.isoformat()
        for name, timestamp in checkpoints.items()
        if timestamp >= current_time
    }


def _fetch_booking_hourly_forecast(start_date, end_date):
    days_ahead = max(
        1,
        int(np.ceil((end_date - pd.Timestamp.now(tz=SITE_TIMEZONE)).total_seconds() / 86400)),
    )
    if days_ahead > MAX_BOOKING_DAYS:
        raise ValueError(
            f"This booking is {days_ahead} days away. Forecasts are supported only up to "
            f"{MAX_BOOKING_DAYS} days ahead; recheck closer to the dive date."
        )

    forecast_days = min(MAX_BOOKING_DAYS, max(1, days_ahead + 1))
    marine_params = {
        "latitude": LAT,
        "longitude": LON,
        "hourly": ",".join(BOOKING_MARINE_VARIABLES.values()),
        "timezone": SITE_TIMEZONE,
        "forecast_days": forecast_days,
        "cell_selection": "sea",
    }
    weather_params = {
        "latitude": LAT,
        "longitude": LON,
        "hourly": ",".join(BOOKING_WEATHER_VARIABLES.values()),
        "timezone": SITE_TIMEZONE,
        "wind_speed_unit": "ms",
        "forecast_days": forecast_days,
    }

    marine_response = requests.get(BOOKING_MARINE_URL, params=marine_params, timeout=30)
    marine_response.raise_for_status()
    marine_data = marine_response.json()["hourly"]
    weather_response = requests.get(BOOKING_WEATHER_URL, params=weather_params, timeout=30)
    weather_response.raise_for_status()
    weather_data = weather_response.json()["hourly"]

    marine_times = pd.to_datetime(marine_data["time"]).tz_localize(SITE_TIMEZONE)
    weather_times = pd.to_datetime(weather_data["time"]).tz_localize(SITE_TIMEZONE)
    marine_frame = pd.DataFrame(
        {name: marine_data[api_name] for name, api_name in BOOKING_MARINE_VARIABLES.items()},
        index=marine_times,
    )
    weather_frame = pd.DataFrame(
        {name: weather_data[api_name] for name, api_name in BOOKING_WEATHER_VARIABLES.items()},
        index=weather_times,
    )
    forecast = marine_frame.join(weather_frame, how="inner")
    forecast = forecast[(forecast.index >= start_date.ceil("h")) & (forecast.index < end_date)]

    required_columns = [*BOOKING_MARINE_VARIABLES, *BOOKING_WEATHER_VARIABLES]
    if forecast.empty:
        raise ValueError("No hourly forecast data was returned for the requested booking window.")
    missing_columns = [column for column in required_columns if column not in forecast.columns]
    if missing_columns:
        raise ValueError(f"Booking forecast is missing required columns: {missing_columns}")
    if forecast[required_columns].isna().any().any():
        raise ValueError("The booking window contains missing forecast values.")
    return forecast


def _score_booking_hour(row, tide_score=0):
    forecasts = {
        "wave_height": row["wave_height"],
        "wave_period": row["wave_period"],
        "swell_height": row["swell_height"],
        "ocean_current": row["ocean_current"],
        "wind_wave_height": row["wind_wave_height"],
        "rain": row["rain"],
        "sea_level_pressure": row["sea_level_pressure"],
        "wind_direction": row["wind_direction"],
    }
    scores = {
        "wave_height": score_wave_height(forecasts["wave_height"]),
        "wave_period": score_wave_period(forecasts["wave_period"]),
        "swell_height": score_swell_height(forecasts["swell_height"]),
        "ocean_current": score_ocean_current(forecasts["ocean_current"]),
        "wind_wave_height": score_wind_wave_height(forecasts["wind_wave_height"]),
        "rain": score_rain(forecasts["rain"]),
        "sea_level_pressure": score_sea_level_pressure(forecasts["sea_level_pressure"]),
        "tide_height": tide_score,
        "wind_direction": score_wind_direction(forecasts["wind_direction"]),
    }
    assessment = assess_weather_risk(scores)
    return forecasts, scores, assessment


def assess_booking(
    planned_date,
    dive_start,
    dive_end,
    overrides=None,
    tide_score=0,
    now=None,
    verbose=False,
):
    """
    Full per-window booking assessment. Call this once per window
    (AM or PM) — day-level aggregation ("worst window wins" between
    a day's two windows) happens on the Laravel side, not here.
    """
    open_water_window = validate_open_water_window(dive_start, dive_end)
    start_date, end_date, current_time = _booking_timestamps(
        planned_date, dive_start, dive_end, now=now
    )
    forecast = _fetch_booking_hourly_forecast(start_date, end_date)

    hourly_assessments = []
    for timestamp, row in forecast.iterrows():
        forecasts, scores, assessment = _score_booking_hour(row, tide_score=tide_score)
        hourly_assessments.append(
            {
                "timestamp": timestamp.isoformat(),
                "forecasts": forecasts,
                "scores": scores,
                "weighted_score_pct": assessment["weighted_score_pct"],
                "classification": assessment["classification"],
                "recommended_action": assessment["recommended_action"],
            }
        )

    if overrides and check_overrides(**overrides):
        session_assessment = {
            "weighted_score_pct": None,
            "classification": "Critical Risk",
            "recommended_action": MEANING_MAP["Critical Risk"],
            "override_triggered": True,
        }
        worst_hour = None
    else:
        worst_hour = max(hourly_assessments, key=lambda item: item["weighted_score_pct"])
        session_assessment = {
            "weighted_score_pct": worst_hour["weighted_score_pct"],
            "classification": worst_hour["classification"],
            "recommended_action": worst_hour["recommended_action"],
            "override_triggered": False,
        }

    result = {
        "planned_date": str(planned_date),
        "dive_start": dive_start,
        "dive_end": dive_end,
        "open_water_window": open_water_window,
        "lead_time_hours": round((start_date - current_time).total_seconds() / 3600, 2),
        "forecast_source": "Open-Meteo hourly forecast",
        "hourly_assessments": hourly_assessments,
        "worst_hour": worst_hour["timestamp"] if worst_hour else None,
        "assessment": session_assessment,
        "recheck_schedule": _booking_recheck_schedule(start_date, current_time),
        "tide_note": "tide_score is currently a caller-supplied value; default 0 is a placeholder.",
    }

    if verbose:
        print(f"Booking: {start_date} to {end_date}")
        print(f"Lead time: {result['lead_time_hours']} hours")
        print(f"Hours assessed: {len(hourly_assessments)}")
        print(f"Worst hour: {result['worst_hour']}")
        print(f"Final assessment: {session_assessment}")

    return result
