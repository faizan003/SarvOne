@echo off
echo Building and running FastAPI service with Docker...
echo.

REM Create necessary directories
if not exist "uploads" mkdir uploads
if not exist "logs" mkdir logs

REM Build and run with docker-compose
docker-compose up --build

pause 