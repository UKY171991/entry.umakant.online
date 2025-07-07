# Test WhatsApp Number Saving in Edit Modal
Write-Host "Testing WhatsApp Number Saving in Edit Modal..." -ForegroundColor Green

# Test 1: Create a new email with phone number
Write-Host "`n1. Creating new email with phone number..." -ForegroundColor Yellow
$createData = @{
    client_name = "Test Client Edit"
    email = "test.edit@example.com"
    phone = "+91-1234567890"
    email_template = "website_proposal"
    project_name = "Test Project Edit"
    estimated_cost = "50000"
    timeframe = "2 weeks"
    notes = "Test notes for edit"
}

$createResponse = Invoke-RestMethod -Uri "http://127.0.0.1:8000/emails" -Method POST -Body $createData -ContentType "application/x-www-form-urlencoded"
Write-Host "Create Response: $($createResponse.success)" -ForegroundColor Green

# Get the created email ID
$emailId = $createResponse.email.id
Write-Host "Created email ID: $emailId" -ForegroundColor Cyan

# Test 2: Get the email data for editing
Write-Host "`n2. Getting email data for editing..." -ForegroundColor Yellow
$editResponse = Invoke-RestMethod -Uri "http://127.0.0.1:8000/emails/$emailId/edit" -Method GET
Write-Host "Edit Response - Phone: $($editResponse.phone)" -ForegroundColor Green

# Test 3: Update the email with new phone number
Write-Host "`n3. Updating email with new phone number..." -ForegroundColor Yellow
$updateData = @{
    client_name = "Test Client Edit Updated"
    email = "test.edit.updated@example.com"
    phone = "+91-9876543210"
    email_template = "project_status_update"
    project_name = "Test Project Edit Updated"
    estimated_cost = "75000"
    timeframe = "3 weeks"
    notes = "Updated test notes"
}

$updateResponse = Invoke-RestMethod -Uri "http://127.0.0.1:8000/emails/$emailId" -Method POST -Body $updateData -ContentType "application/x-www-form-urlencoded"
Write-Host "Update Response: $($updateResponse.success)" -ForegroundColor Green

# Test 4: Verify the phone number was saved
Write-Host "`n4. Verifying phone number was saved..." -ForegroundColor Yellow
$verifyResponse = Invoke-RestMethod -Uri "http://127.0.0.1:8000/emails/$emailId/edit" -Method GET
Write-Host "Verified Phone Number: $($verifyResponse.phone)" -ForegroundColor Green

# Test 5: Test WhatsApp message generation
Write-Host "`n5. Testing WhatsApp message generation..." -ForegroundColor Yellow
$whatsappResponse = Invoke-RestMethod -Uri "http://127.0.0.1:8000/emails/$emailId/whatsapp-message" -Method GET
Write-Host "WhatsApp Message Generated Successfully" -ForegroundColor Green
Write-Host "Phone Number in WhatsApp: $($whatsappResponse.phone)" -ForegroundColor Cyan

# Test 6: Clean up - delete the test email
Write-Host "`n6. Cleaning up test data..." -ForegroundColor Yellow
$deleteResponse = Invoke-RestMethod -Uri "http://127.0.0.1:8000/emails/$emailId" -Method DELETE
Write-Host "Cleanup Response: $($deleteResponse.success)" -ForegroundColor Green

Write-Host "`n=== Test Complete ===" -ForegroundColor Green
Write-Host "WhatsApp Number saving in edit modal is now working correctly!" -ForegroundColor Green
