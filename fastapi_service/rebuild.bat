@echo off
echo Rebuilding FastAPI service with updated dependencies...
echo.

echo Stopping existing container...
docker stop secureverify-fastapi 2>nul
docker rm secureverify-fastapi 2>nul

echo.
echo Attempting to build with full dependencies (including DeepFace)...
echo This may take 10-15 minutes due to large TensorFlow download...
echo.

docker build -t secureverify-fastapi .

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo Build failed! Trying with lightweight version (OpenCV only)...
    echo.
    
    echo Creating lightweight Dockerfile...
    copy Dockerfile Dockerfile.backup
    copy requirements-light.txt requirements.txt
    
    echo Building lightweight version...
    docker build -t secureverify-fastapi-light .
    
    if %ERRORLEVEL% EQU 0 (
        echo.
        echo Lightweight build successful! Starting container...
        docker run -d --name secureverify-fastapi -p 8001:8001 secureverify-fastapi-light
        echo.
        echo Container started with OpenCV-only face recognition!
        echo Service available at: http://localhost:8001
        echo Note: Advanced DeepFace features are disabled
    ) else (
        echo.
        echo Both builds failed. Please check your internet connection and try again.
        echo You can also try: docker system prune -f
    )
) else (
    echo.
    echo Full build successful! Starting container...
    docker run -d --name secureverify-fastapi -p 8001:8001 secureverify-fastapi
    echo.
    echo Container started with full DeepFace capabilities!
    echo Service available at: http://localhost:8001
)

echo.
echo Check logs with: docker logs secureverify-fastapi 