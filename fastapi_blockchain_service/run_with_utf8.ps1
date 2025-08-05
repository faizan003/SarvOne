# Set console to UTF-8 encoding
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8
[Console]::InputEncoding = [System.Text.Encoding]::UTF8

# Set environment variables for Python UTF-8 support
$env:PYTHONIOENCODING = "utf-8"
$env:PYTHONUTF8 = "1"

# Change to the correct directory
Set-Location -Path "D:\hackathon\secureverify\fastapi_blockchain_service"

# Activate virtual environment
& ".\blockchain_env\Scripts\Activate.ps1"

# Run the FastAPI service
uvicorn main:app --host 0.0.0.0 --port 8003 