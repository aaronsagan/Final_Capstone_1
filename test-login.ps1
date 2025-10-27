# Test Login API

$body = @{
    email = "donor@example.com"
    password = "password"
} | ConvertTo-Json

$response = Invoke-RestMethod -Uri "http://localhost:8000/api/auth/login" `
    -Method Post `
    -ContentType "application/json" `
    -Body $body

Write-Host "Login successful!" -ForegroundColor Green
Write-Host "Token: $($response.token)"
Write-Host "User: $($response.user.name) ($($response.user.email))"
Write-Host "Role: $($response.user.role)"
