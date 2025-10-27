Write-Host "Testing Charity Registration..." -ForegroundColor Cyan
Write-Host ""

# Test with JSON data to see validation errors
$testData = @{
    primary_first_name = "John"
    primary_last_name = "Doe"
    primary_email = "newcharity@example.com"
    primary_phone = "09123456789"
    password = "password123"
    password_confirmation = "password123"
    organization_name = "Test Charity"
    registration_number = "REG123"
    tax_id = "TAX123"
} | ConvertTo-Json

try {
    $result = Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/auth/register-charity" `
        -Method Post `
        -ContentType "application/json" `
        -Body $testData
    
    Write-Host "Success!" -ForegroundColor Green
    $result | Format-List
} catch {
    Write-Host "Failed with status:" $_.Exception.Response.StatusCode -ForegroundColor Red
    Write-Host ""
    
    $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
    $responseBody = $reader.ReadToEnd() | ConvertFrom-Json
    
    Write-Host "Message:" $responseBody.message -ForegroundColor Yellow
    
    if ($responseBody.errors) {
        Write-Host ""
        Write-Host "Validation Errors:" -ForegroundColor Yellow
        $responseBody.errors | Format-List
    }
}
