# Test Charity Registration API
Write-Host "Testing Charity Registration Validation..." -ForegroundColor Cyan

# Create test data with minimal required fields
$formData = @{
    # Primary contact (required)
    primary_first_name = "John"
    primary_middle_initial = "D"
    primary_last_name = "Doe"
    primary_position = "Director"
    primary_email = "testcharity@example.com"
    primary_phone = "09123456789"
    password = "password123"
    password_confirmation = "password123"
    
    # Organization (required)
    organization_name = "Test Charity Organization"
    registration_number = "REG-12345"
    tax_id = "TAX-67890"
    
    # Optional fields
    mission_statement = "Help people in need"
    vision_statement = "A better world for all"
    description = "We provide assistance to communities"
    street_address = "123 Main St"
    barangay = "Barangay 1"
    city = "Manila"
    province = "Metro Manila"
    region = "NCR"
    nonprofit_category = "Community Development"
    accept_terms = "true"
    confirm_truthfulness = "true"
}

# Convert to multipart/form-data style
$boundary = [System.Guid]::NewGuid().ToString()
$LF = "`r`n"
$bodyLines = @()

foreach ($key in $formData.Keys) {
    $bodyLines += "--$boundary"
    $bodyLines += "Content-Disposition: form-data; name=`"$key`""
    $bodyLines += ""
    $bodyLines += $formData[$key]
}

$bodyLines += "--$boundary--"
$bodyString = $bodyLines -join $LF

try {
    $response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/api/auth/register-charity" `
        -Method Post `
        -ContentType "multipart/form-data; boundary=$boundary" `
        -Body $bodyString
    
    Write-Host "✓ Registration successful!" -ForegroundColor Green
    $response.Content | ConvertFrom-Json | Format-List
} catch {
    $errorResponse = $_.ErrorDetails.Message | ConvertFrom-Json
    Write-Host "✗ Validation failed!" -ForegroundColor Red
    Write-Host ""
    Write-Host "Errors:" -ForegroundColor Yellow
    
    if ($errorResponse.errors) {
        foreach ($field in $errorResponse.errors.PSObject.Properties) {
            Write-Host "  - $($field.Name):" -ForegroundColor Red
            foreach ($error in $field.Value) {
                Write-Host "    $error" -ForegroundColor White
            }
        }
    } else {
        Write-Host $errorResponse.message -ForegroundColor White
    }
}
