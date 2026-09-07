# forecast-service

Standalone weather-risk forecast API for Camp FreedivePH. Wraps `assess_booking()`
(extracted from `weather_risk_assessment.ipynb`) behind a tiny FastAPI app.

**This is a separate project from the Laravel app - do not copy it into your
Laravel folders.** Laravel talks to it over HTTP.

## What's in here

- `app/weather_risk.py` - the scoring logic (WEIGHTS, `score_*` functions,
  `check_overrides`, `assess_weather_risk`, `assess_booking`), extracted from
  the notebook. No trained model files or `xgboost` needed - it calls
  Open-Meteo's live Marine + Weather APIs directly.
- `app/main.py` - FastAPI app exposing `POST /assess-booking`.

## Run it locally

```bash
python3 -m venv venv
source venv/bin/activate        # Windows: venv\Scripts\activate
pip install -r requirements.txt
uvicorn app.main:app --reload --port 8001
```

Health check: `GET http://127.0.0.1:8001/health` `{"status": "ok"}`

## Call it

```bash
curl -X POST http://127.0.0.1:8001/assess-booking \
  -H "Content-Type: application/json" \
  -d '{
        "planned_date": "2026-08-23",
        "dive_start": "09:30",
        "dive_end": "12:00",
        "tide_score": 0
      }'
```

With an override active:
```bash
curl -X POST http://127.0.0.1:8001/assess-booking \
  -H "Content-Type: application/json" \
  -d '{
        "planned_date": "2026-08-23",
        "dive_start": "09:30",
        "dive_end": "12:00",
        "overrides": {"tcws_signal": 3}
      }'
```

## Point Laravel at it

In the Laravel project's `.env`:
```
FORECAST_SERVICE_URL=http://127.0.0.1:8001
```

`ForecastService.php` calls `POST {FORECAST_SERVICE_URL}/assess-booking` once
per window (AM, PM) - twice per day, four times per full batch assessment -
per the PRD's Section 13.

## Deploying

Deploy this as its own service, separate from the Laravel deploy:

- **Same server:** run via `uvicorn` behind a process manager (systemd/supervisor)
  or Docker, on its own port. Point `FORECAST_SERVICE_URL` at
  `http://127.0.0.1:<port>` or an internal Docker network address.
- **Separate host:** deploy to Render / Railway / Fly.io / a small VPS, and
  point `FORECAST_SERVICE_URL` at its public URL.

Either way, this code never needs to sit inside the Laravel project's
directory structure.

## Known open item

`get_risk_assessment(horizon)` - the XGBoost-model-based function from the
same notebook - is **not** included here, since it's not used by the booking
flow in the notebook and needs the trained model files. Add it back in only
if Bryan confirms it's needed for something this service should also expose.
