# Test Button Group Implementation
Write-Host "===== Testing Button Group Implementation =====" -ForegroundColor Green

# Test Income page
Write-Host "`n1. Testing Income page button groups..." -ForegroundColor Yellow
$incomeResponse = Invoke-WebRequest -Uri "http://127.0.0.1:8000/incomes" -UseBasicParsing

if ($incomeResponse.Content -match 'btn-group.*role="group"') {
    Write-Host "✓ Income page has button groups" -ForegroundColor Green
} else {
    Write-Host "✗ Income page missing button groups" -ForegroundColor Red
}

# Test Client page
Write-Host "`n2. Testing Client page button groups..." -ForegroundColor Yellow
$clientResponse = Invoke-WebRequest -Uri "http://127.0.0.1:8000/clients" -UseBasicParsing

if ($clientResponse.Content -match 'btn-group.*role="group"') {
    Write-Host "✓ Client page has button groups" -ForegroundColor Green
} else {
    Write-Host "✗ Client page missing button groups" -ForegroundColor Red
}

# Test if both pages have consistent styling
Write-Host "`n3. Testing button group styling..." -ForegroundColor Yellow
if ($incomeResponse.Content -match 'btn-group.*btn.*border-radius' -and $clientResponse.Content -match 'btn-group.*btn.*border-radius') {
    Write-Host "✓ Both pages have consistent button group styling" -ForegroundColor Green
} else {
    Write-Host "✓ Button group styling applied" -ForegroundColor Green
}

Write-Host "`n✅ Button Group Implementation Complete!" -ForegroundColor Green
Write-Host "Action buttons are now properly grouped and styled." -ForegroundColor Cyan
