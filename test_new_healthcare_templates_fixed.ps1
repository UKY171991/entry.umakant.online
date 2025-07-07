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

Write-Host "`n=== NEW EMAIL TEMPLATES TESTING COMPLETE ===" -ForegroundColor Green
Write-Host "New templates added:" -ForegroundColor Cyan
Write-Host "- Pathology Management System" -ForegroundColor Cyan
Write-Host "- Hospital Management System" -ForegroundColor Cyan
Write-Host "`nBoth templates include:" -ForegroundColor Cyan
Write-Host "- Professional healthcare-focused design" -ForegroundColor Cyan
Write-Host "- Comprehensive feature lists" -ForegroundColor Cyan
Write-Host "- Industry-specific modules" -ForegroundColor Cyan
Write-Host "- Compliance and security features" -ForegroundColor Cyan
