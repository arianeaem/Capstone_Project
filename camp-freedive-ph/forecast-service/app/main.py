"""
Tiny FastAPI wrapper around assess_booking().

Run locally:
    uvicorn app.main:app --reload --port 8001

Laravel's ForecastService calls:
    POST http://<this-service>/assess-booking

...once per window (AM window, PM window) per day, per the PRD.
"""

from typing import Optional

from fastapi import FastAPI, HTTPException
from pydantic import BaseModel, Field

from .weather_risk import assess_booking

app = FastAPI(title="Camp FreedivePH — Weather Risk Forecast Service")


class Overrides(BaseModel):
    tcws_signal: int = 0
    gale_warning: bool = False
    thunderstorm_advisory: bool = False
    typhoon_within_distance: bool = False
    tsunami_warning: bool = False


class AssessBookingRequest(BaseModel):
    planned_date: str = Field(..., examples=["2026-08-23"])
    dive_start: str = Field(..., examples=["09:30"])
    dive_end: str = Field(..., examples=["12:00"])
    overrides: Optional[Overrides] = None
    tide_score: int = 0


@app.get("/health")
def health():
    return {"status": "ok"}


@app.post("/assess-booking")
def assess_booking_endpoint(payload: AssessBookingRequest):
    try:
        overrides_dict = payload.overrides.model_dump() if payload.overrides else None
        result = assess_booking(
            planned_date=payload.planned_date,
            dive_start=payload.dive_start,
            dive_end=payload.dive_end,
            overrides=overrides_dict,
            tide_score=payload.tide_score,
            verbose=False,
        )
        return result
    except ValueError as exc:
        # Validation-style errors from assess_booking() (bad window, past
        # date, booking too far out, missing forecast data, etc.) surface
        # as 422s so Laravel can show them as user-facing validation errors.
        raise HTTPException(status_code=422, detail=str(exc)) from exc
