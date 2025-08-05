@echo off
REM Set console to UTF-8 encoding
chcp 65001 > nul

REM Set Python to use UTF-8 encoding
set PYTHONIOENCODING=utf-8

REM Activate virtual environment
call blockchain_env\Scripts\activate

REM Run the FastAPI service
uvicorn main:app --host 0.0.0.0 --port 8003

pause 