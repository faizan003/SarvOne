# SecureVerify Face Verification API

A FastAPI-based face verification service that compares selfies with video frames using DeepFace for identity verification.

## Features

- **Face Verification**: Compare selfie images with video frames
- **Multiple AI Models**: Support for VGG-Face, Facenet, OpenFace, and DeepFace
- **Video Processing**: Extract frames from videos for face analysis
- **REST API**: Easy integration with web applications
- **Docker Support**: Containerized deployment ready

## API Endpoints

### Health Check
```
GET /health
```

### Verify Selfie vs Video
```
POST /verify-selfie-video
```
**Parameters:**
- `selfie`: Image file (JPEG, PNG)
- `video`: Video file (MP4, WebM)

**Response:**
```json
{
  "verified": true,
  "score": 0.87,
  "trust_score": 87.0,
  "distance": 0.13,
  "threshold": 0.68,
  "model_used": "Facenet",
  "status": "success",
  "message": "Face verification completed successfully"
}
```

### Verify Two Images
```
POST /verify-images
```
**Parameters:**
- `image1`: First image file
- `image2`: Second image file

## Installation

### Method 1: Local Installation

1. **Create virtual environment:**
```bash
python -m venv venv
source venv/bin/activate  # On Windows: venv\Scripts\activate
```

2. **Install dependencies:**
```bash
pip install -r requirements.txt
```

3. **Run the service:**
```bash
python start.py
```

### Method 2: Docker

1. **Build the image:**
```bash
docker build -t secureverify-face-api .
```

2. **Run the container:**
```bash
docker run -p 8000:8000 secureverify-face-api
```

### Method 3: Docker Compose

```bash
docker-compose up
```

## Configuration

### Environment Variables

- `HOST`: Server host (default: 0.0.0.0)
- `PORT`: Server port (default: 8000)
- `RELOAD`: Enable auto-reload (default: True)
- `LOG_LEVEL`: Log level (default: info)

## Laravel Integration

### 1. Configure Laravel

Add to your `.env` file:
```env
FASTAPI_URL=http://localhost:8000
```

### 2. Laravel Controller Example

```php
use Illuminate\Support\Facades\Http;

// Call the face verification API
$response = Http::attach(
    'selfie', fopen($selfiePath, 'r'), 'selfie.jpg'
)->attach(
    'video', fopen($videoPath, 'r'), 'video.webm'
)->post(config('app.fastapi_url') . '/verify-selfie-video');

if ($response->successful()) {
    $result = $response->json();
    // Handle successful verification
} else {
    // Handle error
}
```

## Face Verification Models

The API supports multiple face recognition models:

- **Facenet** (default): Fast and accurate
- **VGG-Face**: High accuracy, slower
- **OpenFace**: Lightweight
- **DeepFace**: Deep learning based

## Technical Details

### Face Verification Process

1. **Input Processing**: Accept selfie image and video file
2. **Frame Extraction**: Extract middle frame from video
3. **Face Detection**: Detect faces in both images
4. **Feature Extraction**: Extract facial features using AI model
5. **Comparison**: Calculate similarity distance
6. **Verification**: Compare against threshold for final result

### Distance Metrics

- **Cosine** (default): Measures angle between feature vectors
- **Euclidean**: Measures straight-line distance
- **Euclidean L2**: Normalized euclidean distance

### Performance

- **Processing Time**: ~2-5 seconds per verification
- **Accuracy**: 95%+ with good quality images
- **Memory Usage**: ~1-2GB RAM
- **Storage**: Temporary files auto-cleaned

## Error Handling

The API handles various error scenarios:

- Invalid file formats
- Missing faces in images
- Corrupted video files
- Model loading failures
- Network timeouts

## Security Considerations

- All uploaded files are temporarily stored and automatically deleted
- No persistent storage of user data
- Input validation for file types and sizes
- Rate limiting recommended for production

## Troubleshooting

### Common Issues

1. **Model Download**: First run may take time downloading AI models
2. **Memory Issues**: Ensure sufficient RAM (2GB+)
3. **Permission Errors**: Check file permissions
4. **Port Conflicts**: Ensure port 8000 is available

### Logs

Check logs for detailed error information:
```bash
docker logs <container_name>
```

## Development

### Running in Development

```bash
python start.py
# or
uvicorn main:app --reload --host 0.0.0.0 --port 8000
```

### Testing

```bash
# Test health endpoint
curl http://localhost:8000/health

# Test with files
curl -X POST \
  -F "selfie=@selfie.jpg" \
  -F "video=@video.mp4" \
  http://localhost:8000/verify-selfie-video
```

## License

This project is part of the SecureVerify identity verification system. 