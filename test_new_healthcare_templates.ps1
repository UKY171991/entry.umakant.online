# Test the new pathology and hospital management email templates
Write-Host "Testing new email templates: Pathology Management and Hospital Management..." -ForegroundColor Green

# Test 1: Pathology Management Template
Write-Host "`n1. Testing Pathology Management template..." -ForegroundColor Yellow
try {
    $response1 = Invoke-RestMethod -Uri "http://127.0.0.1:8000/emails/template-preview" -Method POST -Body @{
        template = "pathology_management"
        client_name = "Dr. Kumar's Pathology Lab"
        project_name = "Advanced Pathology Management System"
        estimated_cost = "500000"
        timeframe = "12-16 weeks"
        notes = "Complete lab management system with barcode integration, LIS connectivity, and mobile app for pathologists"
    } -ContentType "application/x-www-form-urlencoded"

    if ($response1 -match "CodeApka" -and $response1 -match "uky171991@gmail.com" -and $response1 -match "Pathology Management System") {
        Write-Host "✓ Pathology Management template works correctly" -ForegroundColor Green
    } else {
        Write-Host "✗ Pathology Management template has issues" -ForegroundColor Red
    }
} catch {
    Write-Host "✗ Error testing Pathology Management template: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 2: Hospital Management Template
Write-Host "`n2. Testing Hospital Management template..." -ForegroundColor Yellow
try {
    $response2 = Invoke-RestMethod -Uri "http://127.0.0.1:8000/emails/template-preview" -Method POST -Body @{
        template = "hospital_management"
        client_name = "City General Hospital"
        project_name = "Comprehensive Hospital Management System"
        estimated_cost = "1500000"
        timeframe = "20-24 weeks"
        notes = "Full HMS with EMR, OPD, IPD, pharmacy, lab integration, billing, and mobile applications"
    } -ContentType "application/x-www-form-urlencoded"

    if ($response2 -match "CodeApka" -and $response2 -match "uky171991@gmail.com" -and $response2 -match "Hospital Management System") {
        Write-Host "✓ Hospital Management template works correctly" -ForegroundColor Green
    } else {
        Write-Host "✗ Hospital Management template has issues" -ForegroundColor Red
    }
} catch {
    Write-Host "✗ Error testing Hospital Management template: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 3: Test email sending functionality for Pathology Management
Write-Host "`n3. Testing Pathology Management email sending..." -ForegroundColor Yellow
try {
    $response3 = Invoke-RestMethod -Uri "http://127.0.0.1:8000/emails/send-template" -Method POST -Body @{
        template = "pathology_management"
        client_name = "Test Lab"
        email = "uky171991@gmail.com"
        project_name = "Test Pathology System"
        estimated_cost = "300000"
        timeframe = "10 weeks"
        notes = "Test pathology management system"
    } -ContentType "application/x-www-form-urlencoded"

    if ($response3 -match "successfully") {
        Write-Host "✓ Pathology Management email sent successfully" -ForegroundColor Green
    } else {
        Write-Host "✗ Pathology Management email sending failed" -ForegroundColor Red
    }
} catch {
    Write-Host "✗ Error sending Pathology Management email: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 4: Test email sending functionality for Hospital Management
Write-Host "`n4. Testing Hospital Management email sending..." -ForegroundColor Yellow
try {
    $response4 = Invoke-RestMethod -Uri "http://127.0.0.1:8000/emails/send-template" -Method POST -Body @{
        template = "hospital_management"
        client_name = "Test Hospital"
        email = "uky171991@gmail.com"
        project_name = "Test Hospital System"
        estimated_cost = "800000"
        timeframe = "16 weeks"
        notes = "Test hospital management system with EMR and billing"
    } -ContentType "application/x-www-form-urlencoded"

    if ($response4 -match "successfully") {
        Write-Host "✓ Hospital Management email sent successfully" -ForegroundColor Green
    } else {
        Write-Host "✗ Hospital Management email sending failed" -ForegroundColor Red
    }
} catch {
    Write-Host "✗ Error sending Hospital Management email: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host "`n=== NEW EMAIL TEMPLATES TESTING COMPLETE ===" -ForegroundColor Green
Write-Host "New templates added:" -ForegroundColor Cyan
Write-Host "- 🔬 Pathology Management System" -ForegroundColor Cyan
Write-Host "- 🏥 Hospital Management System" -ForegroundColor Cyan
Write-Host "`nBoth templates include:" -ForegroundColor Cyan
Write-Host "- Professional healthcare-focused design" -ForegroundColor Cyan
Write-Host "- Comprehensive feature lists" -ForegroundColor Cyan
Write-Host "- Industry-specific modules" -ForegroundColor Cyan
Write-Host "- Compliance and security features" -ForegroundColor Cyan
Write-Host "- Contact information: uky171991@gmail.com and +91-9453619260" -ForegroundColor Cyan
