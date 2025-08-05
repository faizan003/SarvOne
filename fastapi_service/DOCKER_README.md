# FastAPI Service Docker Setup

This guide explains how to run the FastAPI face verification service using Docker.

## Prerequisites

1. **Docker Desktop** installed on your system
2. **Docker Compose** (usually included with Docker Desktop)

## Quick Start

### Option 1: Using the batch script (Windows)
```bash
docker-run.bat
```

### Option 2: Manual Docker commands
```bash
# Build and run the service
docker-compose up --build

# Or run in detached mode
docker-compose up --build -d
```

## What's Included

The Docker setup includes:

- **Python 3.10** (compatible with DeepFace and TensorFlow)
- **DeepFace** for real face recognition and verification
- **OpenCV** for image processing
- **TensorFlow** and **Keras** for deep learning models
- **All necessary system dependencies** for computer vision libraries

## Port Configuration

- **FastAPI Service**: Port 8001
- **Laravel Frontend**: Port 8000 (runs separately)

## Directory Structure

```
fastapi_service/
├── uploads/          # Uploaded images (mounted volume)
├── logs/             # Application logs (mounted volume)
├── Dockerfile        # Container configuration
├── docker-compose.yml # Multi-container setup
└── requirements.txt  # Python dependencies
```

## Environment Variables

- `PYTHONPATH=/app`: Sets Python path
- `PYTHONUNBUFFERED=1`: Ensures Python output is not buffered

## Health Check

The service includes a health check endpoint at `/health` that runs every 30 seconds.

## Stopping the Service

```bash
# Stop the service
docker-compose down

# Stop and remove volumes
docker-compose down -v
```

## Troubleshooting

### Build Issues
If you encounter build issues:

1. **Clear Docker cache**:
   ```bash
   docker system prune -a
   ```

2. **Rebuild without cache**:
   ```bash
   docker-compose build --no-cache
   ```

### Port Conflicts
If port 8001 is already in use:
```bash
# Check what's using the port
netstat -ano | findstr :8001

# Or modify docker-compose.yml to use a different port
```

### Memory Issues
The DeepFace models require significant memory. If you encounter memory issues:

1. **Increase Docker memory limit** in Docker Desktop settings
2. **Use a machine with at least 4GB RAM**

## Development

### Viewing Logs
```bash
# View real-time logs
docker-compose logs -f

# View logs for specific service
docker-compose logs fastapi-service
```

### Accessing Container Shell
```bash
# Access running container
docker-compose exec fastapi-service bash

# Or start a new container for debugging
docker run -it --rm fastapi_service_fastapi-service bash
```

## API Endpoints

Once running, the service will be available at:
- **Health Check**: `http://localhost:8001/health`
- **Face Verification**: `http://localhost:8001/verify-face`
- **Documentation**: `http://localhost:8001/docs`

## Performance Notes

- **First run**: DeepFace will download models (~500MB), which may take several minutes
- **Subsequent runs**: Models are cached and startup is faster
- **Memory usage**: ~2-3GB RAM recommended for optimal performance 