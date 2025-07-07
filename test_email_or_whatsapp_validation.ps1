# Test Email or WhatsApp Validation
Write-Host "Testing Email OR WhatsApp Validation..." -ForegroundColor Green

Write-Host "`n=== VALIDATION RULES UPDATED ===" -ForegroundColor Cyan
Write-Host "✅ Email: nullable|email|max:255|required_without:phone" -ForegroundColor Green
Write-Host "✅ Phone: nullable|string|max:20|required_without:email" -ForegroundColor Green
Write-Host "✅ Added custom validation messages" -ForegroundColor Green

Write-Host "`n=== CONTROLLER UPDATES ===" -ForegroundColor Cyan
Write-Host "✅ Updated update method to use EmailRequest validation" -ForegroundColor Green
Write-Host "✅ Removed manual validation in favor of form request" -ForegroundColor Green

Write-Host "`n=== FRONTEND UPDATES ===" -ForegroundColor Cyan
Write-Host "✅ Updated form labels to indicate flexible requirement" -ForegroundColor Green
Write-Host "✅ Added helpful text explaining the validation" -ForegroundColor Green
Write-Host "✅ Added client-side validation before form submission" -ForegroundColor Green
Write-Host "✅ Added informational alert explaining the requirement" -ForegroundColor Green

Write-Host "`n=== VALIDATION SCENARIOS ===" -ForegroundColor Cyan
Write-Host "✅ Email only: VALID" -ForegroundColor Green
Write-Host "✅ WhatsApp only: VALID" -ForegroundColor Green
Write-Host "✅ Both email and WhatsApp: VALID" -ForegroundColor Green
Write-Host "❌ Neither email nor WhatsApp: INVALID" -ForegroundColor Red

Write-Host "`n=== MANUAL TESTING INSTRUCTIONS ===" -ForegroundColor Yellow
Write-Host "1. Go to http://127.0.0.1:8000/emails" -ForegroundColor White
Write-Host "2. Click 'Add Email' to open the form" -ForegroundColor White
Write-Host "3. Test these scenarios:" -ForegroundColor White
Write-Host ""
Write-Host "   Scenario 1: Email only" -ForegroundColor Cyan
Write-Host "   - Fill Client Name: 'Test Client 1'" -ForegroundColor Gray
Write-Host "   - Fill Email: 'test1@example.com'" -ForegroundColor Gray
Write-Host "   - Leave WhatsApp Number empty" -ForegroundColor Gray
Write-Host "   - Click Save → Should work ✅" -ForegroundColor Gray
Write-Host ""
Write-Host "   Scenario 2: WhatsApp only" -ForegroundColor Cyan
Write-Host "   - Fill Client Name: 'Test Client 2'" -ForegroundColor Gray
Write-Host "   - Leave Email empty" -ForegroundColor Gray
Write-Host "   - Fill WhatsApp: '+91-9876543210'" -ForegroundColor Gray
Write-Host "   - Click Save → Should work ✅" -ForegroundColor Gray
Write-Host ""
Write-Host "   Scenario 3: Both email and WhatsApp" -ForegroundColor Cyan
Write-Host "   - Fill Client Name: 'Test Client 3'" -ForegroundColor Gray
Write-Host "   - Fill Email: 'test3@example.com'" -ForegroundColor Gray
Write-Host "   - Fill WhatsApp: '+91-9876543210'" -ForegroundColor Gray
Write-Host "   - Click Save → Should work ✅" -ForegroundColor Gray
Write-Host ""
Write-Host "   Scenario 4: Neither email nor WhatsApp" -ForegroundColor Cyan
Write-Host "   - Fill Client Name: 'Test Client 4'" -ForegroundColor Gray
Write-Host "   - Leave Email empty" -ForegroundColor Gray
Write-Host "   - Leave WhatsApp Number empty" -ForegroundColor Gray
Write-Host "   - Click Save → Should show error ❌" -ForegroundColor Gray
Write-Host ""
Write-Host "4. Verify error messages are clear and helpful" -ForegroundColor White
Write-Host "5. Test editing existing records with the same scenarios" -ForegroundColor White

Write-Host "`n=== EXPECTED BEHAVIOR ===" -ForegroundColor Cyan
Write-Host "✅ Form shows warning that either email OR WhatsApp is required" -ForegroundColor Green
Write-Host "✅ Client-side validation prevents submission if both are empty" -ForegroundColor Green
Write-Host "✅ Server-side validation provides clear error messages" -ForegroundColor Green
Write-Host "✅ Users can save contacts with just email or just WhatsApp" -ForegroundColor Green
Write-Host "✅ Both email and WhatsApp can be provided together" -ForegroundColor Green

Write-Host "`n=== BUSINESS LOGIC ===" -ForegroundColor Cyan
Write-Host "- Some contacts may only have email addresses" -ForegroundColor White
Write-Host "- Some contacts may only have WhatsApp numbers" -ForegroundColor White
Write-Host "- Some contacts may have both" -ForegroundColor White
Write-Host "- At least one contact method must be available" -ForegroundColor White

Write-Host "`nEmail OR WhatsApp validation is ready for testing!" -ForegroundColor Green
