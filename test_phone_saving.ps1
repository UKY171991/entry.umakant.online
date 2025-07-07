# Test WhatsApp phone number saving functionality
Write-Host "Testing WhatsApp phone number saving..." -ForegroundColor Green

# Test 1: Create a new email record with phone number
Write-Host "`n1. Testing phone number saving via API..." -ForegroundColor Yellow
try {
    $response1 = Invoke-RestMethod -Uri "http://127.0.0.1:8000/emails" -Method POST -Body @{
        client_name = "Test WhatsApp Client"
        email = "testwhatsapp@example.com"
        phone = "+919876543210"
        email_template = "website_proposal"
        project_name = "Test WhatsApp Project"
        estimated_cost = "50000"
        timeframe = "4 weeks"
        notes = "Testing WhatsApp phone number saving"
        _token = ""
    } -ContentType "application/x-www-form-urlencoded"

    Write-Host "✓ Email record created successfully" -ForegroundColor Green
    Write-Host "Response: $($response1 | ConvertTo-Json)" -ForegroundColor Cyan
} catch {
    Write-Host "✗ Error creating email record: $($_.Exception.Message)" -ForegroundColor Red
    if ($_.Exception.Response) {
        $responseBody = $_.Exception.Response.GetResponseStream()
        $reader = New-Object System.IO.StreamReader($responseBody)
        $responseText = $reader.ReadToEnd()
        Write-Host "Response Body: $responseText" -ForegroundColor Yellow
    }
}

# Test 2: Check if we can retrieve the record
Write-Host "`n2. Testing if phone number was saved..." -ForegroundColor Yellow
try {
    $response2 = Invoke-RestMethod -Uri "http://127.0.0.1:8000/emails" -Method GET
    Write-Host "✓ Retrieved emails data" -ForegroundColor Green
} catch {
    Write-Host "✗ Error retrieving emails: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host "`n=== PHONE NUMBER SAVING TEST COMPLETE ===" -ForegroundColor Green
