@echo off
echo Building FastAPI service with retry logic...
echo.

echo Stopping existing container...
docker stop secureverify-fastapi 2>nul
docker rm secureverify-fastapi 2>nul

echo.
echo Attempt 1: Building with increased timeout...
docker build --build-arg PIP_TIMEOUT=600 --build-arg PIP_RETRIES=5 -t secureverify-fastapi .

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo Attempt 1 failed. Trying with different pip settings...
    echo.
    
    echo Attempt 2: Building with pip cache disabled and different mirrors...
    docker build --build-arg PIP_TIMEOUT=900 --build-arg PIP_RETRIES=10 --no-cache -t secureverify-fastapi .
    
    if %ERRORLEVEL% NEQ 0 (
        echo.
        echo Both attempts failed. Trying lightweight version...
        echo.
        
        copy requirements-light.txt requirements.txt
        docker build -t secureverify-fastapi-light .
        
        if %ERRORLEVEL% EQU 0 (
            echo.
            echo Lightweight build successful!
            docker run -d --name secureverify-fastapi -p 8001:8001 secureverify-fastapi-light
            echo Container started with OpenCV-only features.
        ) else (
            echo.
            echo All builds failed. Please check your internet connection.
            echo You may need to:
            echo 1. Check your internet connection
            echo 2. Try: docker system prune -f
            echo 3. Try: docker builder prune -f
            echo 4. Use a VPN if you're behind a corporate firewall
        )
    ) else (
        echo.
        echo Build successful on attempt 2!
        docker run -d --name secureverify-fastapi -p 8001:8001 secureverify-fastapi
    )
) else (
    echo.
    echo Build successful on first attempt!
    docker run -d --name secureverify-fastapi -p 8001:8001 secureverify-fastapi
)

echo.
echo Service should be available at: http://localhost:8001
echo Check logs with: docker logs secureverify-fastapi 