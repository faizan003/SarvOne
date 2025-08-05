@echo off
echo Starting SecureVerify Face Verification API on port 8001...
cd /d "%~dp0"
call venv\Scripts\activate.bat
python -c "import uvicorn; uvicorn.run('main:app', host='127.0.0.1', port=8001, log_level='info')"
pause 