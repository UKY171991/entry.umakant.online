# Simple WhatsApp Message Test
Write-Host "Testing WhatsApp Message Endpoint..." -ForegroundColor Green

Write-Host "`n=== QUICK DEBUG STEPS ===" -ForegroundColor Cyan
Write-Host "1. Open http://127.0.0.1:8000/emails in browser" -ForegroundColor White
Write-Host "2. Press F12 to open Developer Tools" -ForegroundColor White
Write-Host "3. Click Console tab" -ForegroundColor White
Write-Host "4. Click any WhatsApp button" -ForegroundColor White
Write-Host "5. Look for these logs in console:" -ForegroundColor White
Write-Host ""
Write-Host "Expected Console Output:" -ForegroundColor Yellow
Write-Host "WhatsApp Message: [Full message text]" -ForegroundColor Gray
Write-Host "Phone Number: [Original phone number]" -ForegroundColor Gray
Write-Host "Clean Phone: [Digits only]" -ForegroundColor Gray
Write-Host "Encoded Message Length: [Number]" -ForegroundColor Gray
Write-Host "WhatsApp URL: https://wa.me/... [Complete URL]" -ForegroundColor Gray

Write-Host "`n=== ISSUE IDENTIFICATION ===" -ForegroundColor Cyan
Write-Host "If message box is empty in WhatsApp:" -ForegroundColor Yellow
Write-Host ""
Write-Host "1. Check Console Logs" -ForegroundColor White
Write-Host "   - Is 'WhatsApp Message' showing the full text?" -ForegroundColor Gray
Write-Host "   - Is 'WhatsApp URL' complete?" -ForegroundColor Gray
Write-Host ""
Write-Host "2. Check Phone Number" -ForegroundColor White
Write-Host "   - Is 'Clean Phone' showing only digits?" -ForegroundColor Gray
Write-Host "   - Does it start with country code (91)?" -ForegroundColor Gray
Write-Host ""
Write-Host "3. Check Message Length" -ForegroundColor White
Write-Host "   - Is 'Encoded Message Length' under 2000?" -ForegroundColor Gray
Write-Host ""
Write-Host "4. Manual URL Test" -ForegroundColor White
Write-Host "   - Copy the 'WhatsApp URL' from console" -ForegroundColor Gray
Write-Host "   - Paste it directly in browser address bar" -ForegroundColor Gray
Write-Host "   - Does WhatsApp open with message?" -ForegroundColor Gray

Write-Host "`n=== QUICK FIX TEST ===" -ForegroundColor Cyan
Write-Host "Try this simple WhatsApp URL manually:" -ForegroundColor White
Write-Host "https://wa.me/919876543210?text=Hello%20World%20Test" -ForegroundColor Yellow
Write-Host ""
Write-Host "Replace 919876543210 with your actual WhatsApp number" -ForegroundColor Gray
Write-Host "If this works, the issue is in message generation/encoding" -ForegroundColor Gray

Write-Host "`nDebugging tools are ready! Check browser console for detailed information." -ForegroundColor Green
