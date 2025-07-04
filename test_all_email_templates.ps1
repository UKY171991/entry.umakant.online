# Test all email templates to ensure they work correctly
Write-Host "Testing all email templates..." -ForegroundColor Green

# Test 1: Website Proposal Template
Write-Host "`n1. Testing Website Proposal template..." -ForegroundColor Yellow
$response1 = Invoke-RestMethod -Uri "http://127.0.0.1:8000/emails/preview" -Method POST -Body @{
    template_type = "website_proposal"
    client_name = "Test Client"
    subject = "Website Development Proposal"
    message = "Test message for proposal"
} -ContentType "application/x-www-form-urlencoded"

if ($response1 -match "CodeApka" -and $response1 -match "uky171991@gmail.com") {
    Write-Host "✓ Website Proposal template works correctly" -ForegroundColor Green
} else {
    Write-Host "✗ Website Proposal template has issues" -ForegroundColor Red
}

# Test 2: Project Status Update Template
Write-Host "`n2. Testing Project Status Update template..." -ForegroundColor Yellow
$response2 = Invoke-RestMethod -Uri "http://127.0.0.1:8000/emails/preview" -Method POST -Body @{
    template_type = "project_status_update"
    client_name = "Test Client"
    subject = "Project Status Update"
    message = "Your project is progressing well"
} -ContentType "application/x-www-form-urlencoded"

if ($response2 -match "CodeApka" -and $response2 -match "uky171991@gmail.com") {
    Write-Host "✓ Project Status Update template works correctly" -ForegroundColor Green
} else {
    Write-Host "✗ Project Status Update template has issues" -ForegroundColor Red
}

# Test 3: Project Completion Template
Write-Host "`n3. Testing Project Completion template..." -ForegroundColor Yellow
$response3 = Invoke-RestMethod -Uri "http://127.0.0.1:8000/emails/preview" -Method POST -Body @{
    template_type = "project_completion"
    client_name = "Test Client"
    subject = "Project Completed"
    message = "Your project has been completed successfully"
} -ContentType "application/x-www-form-urlencoded"

if ($response3 -match "CodeApka" -and $response3 -match "uky171991@gmail.com") {
    Write-Host "✓ Project Completion template works correctly" -ForegroundColor Green
} else {
    Write-Host "✗ Project Completion template has issues" -ForegroundColor Red
}

# Test 4: General Inquiry Template
Write-Host "`n4. Testing General Inquiry template..." -ForegroundColor Yellow
$response4 = Invoke-RestMethod -Uri "http://127.0.0.1:8000/emails/preview" -Method POST -Body @{
    template_type = "general_inquiry"
    client_name = "Test Client"
    subject = "Thank you for your inquiry"
    message = "We received your inquiry and will respond soon"
} -ContentType "application/x-www-form-urlencoded"

if ($response4 -match "CodeApka" -and $response4 -match "uky171991@gmail.com") {
    Write-Host "✓ General Inquiry template works correctly" -ForegroundColor Green
} else {
    Write-Host "✗ General Inquiry template has issues" -ForegroundColor Red
}

# Test 5: Website Development Update Template
Write-Host "`n5. Testing Website Development Update template..." -ForegroundColor Yellow
$response5 = Invoke-RestMethod -Uri "http://127.0.0.1:8000/emails/preview" -Method POST -Body @{
    template_type = "website_development_update"
    client_name = "Test Client"
    subject = "Development Update"
    message = "Development progress update"
} -ContentType "application/x-www-form-urlencoded"

if ($response5 -match "CodeApka" -and $response5 -match "uky171991@gmail.com") {
    Write-Host "✓ Website Development Update template works correctly" -ForegroundColor Green
} else {
    Write-Host "✗ Website Development Update template has issues" -ForegroundColor Red
}

Write-Host "All email template tests completed!" -ForegroundColor Green
Write-Host "All templates should now have consistent contact information:" -ForegroundColor Cyan
Write-Host "- Email: uky171991@gmail.com" -ForegroundColor Cyan
Write-Host "- Phone: +91-9453619260" -ForegroundColor Cyan
Write-Host "- Website: https://codeapka.com" -ForegroundColor Cyan
Write-Host "- Company: CodeApka" -ForegroundColor Cyan
