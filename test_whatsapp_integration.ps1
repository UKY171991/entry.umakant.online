# Test WhatsApp functionality in email templates
Write-Host "Testing WhatsApp integration in email templates..." -ForegroundColor Green

# Test 1: Create a test email record with phone number
Write-Host "`n1. Testing WhatsApp message generation..." -ForegroundColor Yellow
try {
    $response1 = Invoke-RestMethod -Uri "http://127.0.0.1:8000/emails/whatsapp-message" -Method POST -Body @{
        template = "website_proposal"
        client_name = "Test Client"
        project_name = "Test Website Project"
        estimated_cost = "75000"
        timeframe = "6 weeks"
        notes = "Professional business website with e-commerce functionality"
    } -ContentType "application/x-www-form-urlencoded"

    if ($response1.message -match "Website Development Proposal" -and $response1.message -match "CodeApka") {
        Write-Host "✓ Website Proposal WhatsApp message generated correctly" -ForegroundColor Green
    } else {
        Write-Host "✗ Website Proposal WhatsApp message has issues" -ForegroundColor Red
    }
} catch {
    Write-Host "✗ Error testing Website Proposal WhatsApp message: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 2: Test Pathology Management WhatsApp message
Write-Host "`n2. Testing Pathology Management WhatsApp message..." -ForegroundColor Yellow
try {
    $response2 = Invoke-RestMethod -Uri "http://127.0.0.1:8000/emails/whatsapp-message" -Method POST -Body @{
        template = "pathology_management"
        client_name = "Dr. Kumar's Lab"
        project_name = "Pathology Management System"
        estimated_cost = "500000"
        timeframe = "12 weeks"
        notes = "Complete lab management with LIS integration"
    } -ContentType "application/x-www-form-urlencoded"

    if ($response2.message -match "Pathology Management System" -and $response2.message -match "Lab Test Catalog") {
        Write-Host "✓ Pathology Management WhatsApp message generated correctly" -ForegroundColor Green
    } else {
        Write-Host "✗ Pathology Management WhatsApp message has issues" -ForegroundColor Red
    }
} catch {
    Write-Host "✗ Error testing Pathology Management WhatsApp message: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 3: Test Hospital Management WhatsApp message
Write-Host "`n3. Testing Hospital Management WhatsApp message..." -ForegroundColor Yellow
try {
    $response3 = Invoke-RestMethod -Uri "http://127.0.0.1:8000/emails/whatsapp-message" -Method POST -Body @{
        template = "hospital_management"
        client_name = "City Hospital"
        project_name = "Hospital Management System"
        estimated_cost = "1500000"
        timeframe = "20 weeks"
        notes = "Complete HMS with EMR and billing integration"
    } -ContentType "application/x-www-form-urlencoded"

    if ($response3.message -match "Hospital Management System" -and $response3.message -match "Patient Registration") {
        Write-Host "✓ Hospital Management WhatsApp message generated correctly" -ForegroundColor Green
    } else {
        Write-Host "✗ Hospital Management WhatsApp message has issues" -ForegroundColor Red
    }
} catch {
    Write-Host "✗ Error testing Hospital Management WhatsApp message: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host "`n=== WHATSAPP INTEGRATION TESTING COMPLETE ===" -ForegroundColor Green
Write-Host "WhatsApp features added:" -ForegroundColor Cyan
Write-Host "- Phone number field in email records" -ForegroundColor Cyan
Write-Host "- WhatsApp message generation for all templates" -ForegroundColor Cyan
Write-Host "- WhatsApp send buttons in email table" -ForegroundColor Cyan
Write-Host "- WhatsApp preview in template modal" -ForegroundColor Cyan
Write-Host "- Professional WhatsApp message formatting" -ForegroundColor Cyan
Write-Host "- Direct WhatsApp link generation with messages" -ForegroundColor Cyan
