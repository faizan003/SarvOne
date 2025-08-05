$body = @{
    orgDID = "did:sarvone:test:00001"
    orgAddress = "0x742d35Cc6634C0532925a3b8D46a02948d9A4f6e"
    scopes = @("kyc_verification")
} | ConvertTo-Json

Write-Host "Testing approval endpoint..."
Write-Host "Payload: $body"

try {
    $response = Invoke-WebRequest -Uri "http://localhost:8003/approve_org" -Method POST -Body $body -ContentType "application/json"
    Write-Host "✅ SUCCESS: Status Code: $($response.StatusCode)"
    Write-Host "Response: $($response.Content)"
} catch {
    Write-Host "❌ ERROR: $($_.Exception.Message)"
    Write-Host "Response: $($_.Exception.Response)"
    if ($_.Exception.Response) {
        $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
        $errorContent = $reader.ReadToEnd()
        Write-Host "Error Content: $errorContent"
    }
} 