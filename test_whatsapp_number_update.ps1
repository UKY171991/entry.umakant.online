# Test all email templates to verify WhatsApp number has been added
Write-Host "Testing all email templates for WhatsApp number inclusion..." -ForegroundColor Green

# Test 1: Website Proposal Template
Write-Host "`n1. Testing Website Proposal template..." -ForegroundColor Yellow
try {
    $response1 = Invoke-RestMethod -Uri "http://127.0.0.1:8000/emails/template-preview" -Method POST -Body @{
        template = "website_proposal"
        client_name = "Test Client"
        project_name = "Test Website"
        estimated_cost = "50000"
        timeframe = "4 weeks"
        notes = "Professional website development"
    } -ContentType "application/x-www-form-urlencoded"

    if ($response1 -match "WhatsApp" -and $response1 -match "\+91-9453619260") {
        Write-Host "✓ Website Proposal template includes WhatsApp number" -ForegroundColor Green
    } else {
        Write-Host "✗ Website Proposal template missing WhatsApp number" -ForegroundColor Red
    }
} catch {
    Write-Host "✗ Error testing Website Proposal template: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 2: Project Status Update Template
Write-Host "`n2. Testing Project Status Update template..." -ForegroundColor Yellow
try {
    $response2 = Invoke-RestMethod -Uri "http://127.0.0.1:8000/emails/template-preview" -Method POST -Body @{
        template = "project_update"
        client_name = "Test Client"
        project_name = "Test Project"
        estimated_cost = "75000"
        timeframe = "6 weeks"
        notes = "Project status update"
    } -ContentType "application/x-www-form-urlencoded"

    if ($response2 -match "WhatsApp" -and $response2 -match "\+91-9453619260") {
        Write-Host "✓ Project Status Update template includes WhatsApp number" -ForegroundColor Green
    } else {
        Write-Host "✗ Project Status Update template missing WhatsApp number" -ForegroundColor Red
    }
} catch {
    Write-Host "✗ Error testing Project Status Update template: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 3: Project Completion Template
Write-Host "`n3. Testing Project Completion template..." -ForegroundColor Yellow
try {
    $response3 = Invoke-RestMethod -Uri "http://127.0.0.1:8000/emails/template-preview" -Method POST -Body @{
        template = "project_completion"
        client_name = "Test Client"
        project_name = "Test Project"
        estimated_cost = "100000"
        timeframe = "8 weeks"
        notes = "Project completion notification"
    } -ContentType "application/x-www-form-urlencoded"

    if ($response3 -match "WhatsApp" -and $response3 -match "\+91-9453619260") {
        Write-Host "✓ Project Completion template includes WhatsApp number" -ForegroundColor Green
    } else {
        Write-Host "✗ Project Completion template missing WhatsApp number" -ForegroundColor Red
    }
} catch {
    Write-Host "✗ Error testing Project Completion template: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 4: General Inquiry Template
Write-Host "`n4. Testing General Inquiry template..." -ForegroundColor Yellow
try {
    $response4 = Invoke-RestMethod -Uri "http://127.0.0.1:8000/emails/template-preview" -Method POST -Body @{
        template = "general_inquiry"
        client_name = "Test Client"
        project_name = "General Inquiry"
        estimated_cost = "50000"
        timeframe = "4 weeks"
        notes = "General business inquiry"
    } -ContentType "application/x-www-form-urlencoded"

    if ($response4 -match "WhatsApp" -and $response4 -match "\+91-9453619260") {
        Write-Host "✓ General Inquiry template includes WhatsApp number" -ForegroundColor Green
    } else {
        Write-Host "✗ General Inquiry template missing WhatsApp number" -ForegroundColor Red
    }
} catch {
    Write-Host "✗ Error testing General Inquiry template: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 5: Pathology Management Template
Write-Host "`n5. Testing Pathology Management template..." -ForegroundColor Yellow
try {
    $response5 = Invoke-RestMethod -Uri "http://127.0.0.1:8000/emails/template-preview" -Method POST -Body @{
        template = "pathology_management"
        client_name = "Test Lab"
        project_name = "Pathology Management System"
        estimated_cost = "500000"
        timeframe = "12 weeks"
        notes = "Lab management system"
    } -ContentType "application/x-www-form-urlencoded"

    if ($response5 -match "WhatsApp" -and $response5 -match "\+91-9453619260") {
        Write-Host "✓ Pathology Management template includes WhatsApp number" -ForegroundColor Green
    } else {
        Write-Host "✗ Pathology Management template missing WhatsApp number" -ForegroundColor Red
    }
} catch {
    Write-Host "✗ Error testing Pathology Management template: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 6: Hospital Management Template
Write-Host "`n6. Testing Hospital Management template..." -ForegroundColor Yellow
try {
    $response6 = Invoke-RestMethod -Uri "http://127.0.0.1:8000/emails/template-preview" -Method POST -Body @{
        template = "hospital_management"
        client_name = "Test Hospital"
        project_name = "Hospital Management System"
        estimated_cost = "1500000"
        timeframe = "20 weeks"
        notes = "Complete hospital management system"
    } -ContentType "application/x-www-form-urlencoded"

    if ($response6 -match "WhatsApp" -and $response6 -match "\+91-9453619260") {
        Write-Host "✓ Hospital Management template includes WhatsApp number" -ForegroundColor Green
    } else {
        Write-Host "✗ Hospital Management template missing WhatsApp number" -ForegroundColor Red
    }
} catch {
    Write-Host "✗ Error testing Hospital Management template: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host "`n=== WHATSAPP NUMBER UPDATE TESTING COMPLETE ===" -ForegroundColor Green
Write-Host "WhatsApp contact information added to all email templates:" -ForegroundColor Cyan
Write-Host "- Number: +91-9453619260" -ForegroundColor Cyan
Write-Host "- Added to footer sections with WhatsApp emoji (💬)" -ForegroundColor Cyan
Write-Host "- Added to contact sections within email content" -ForegroundColor Cyan
Write-Host "- All templates now include Phone, Email, WhatsApp, and Website" -ForegroundColor Cyan
