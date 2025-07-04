# Test Income Modal Functionality
Write-Host "===== Testing Income Modal Functionality =====" -ForegroundColor Green

# Test 1: Check if the incomes page loads
Write-Host "`n1. Testing if incomes page loads..." -ForegroundColor Yellow
$response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/incomes" -UseBasicParsing
Write-Host "✓ Incomes page loads successfully (Status: $($response.StatusCode))" -ForegroundColor Green

# Test 2: Check for required elements in the HTML
Write-Host "`n2. Checking for required HTML elements..." -ForegroundColor Yellow
$html = $response.Content

# Check for Add Income button
if ($html -match 'id="createNewIncome"') {
    Write-Host "✓ Add Income button found" -ForegroundColor Green
} else {
    Write-Host "✗ Add Income button NOT found" -ForegroundColor Red
}

# Check for modal structure
if ($html -match 'id="ajaxModel"') {
    Write-Host "✓ Modal container found" -ForegroundColor Green
} else {
    Write-Host "✗ Modal container NOT found" -ForegroundColor Red
}

# Check for form elements
if ($html -match 'id="incomeForm"') {
    Write-Host "✓ Income form found" -ForegroundColor Green
} else {
    Write-Host "✗ Income form NOT found" -ForegroundColor Red
}

# Check for save button
if ($html -match 'id="saveBtn"') {
    Write-Host "✓ Save button found" -ForegroundColor Green
} else {
    Write-Host "✗ Save button NOT found" -ForegroundColor Red
}

# Check for required form fields
$requiredFields = @('client_id', 'total_amount', 'pending_amount', 'received_amount', 'date')
foreach ($field in $requiredFields) {
    if ($html -match "name=`"$field`"") {
        Write-Host "✓ Field '$field' found" -ForegroundColor Green
    } else {
        Write-Host "✗ Field '$field' NOT found" -ForegroundColor Red
    }
}

Write-Host "`n===== Income Modal Test Complete =====" -ForegroundColor Green
Write-Host "`nTo manually test the modal:" -ForegroundColor Cyan
Write-Host "1. Open http://127.0.0.1:8000/incomes in your browser" -ForegroundColor White
Write-Host "2. Click the 'Add Income' button" -ForegroundColor White
Write-Host "3. Fill in the form fields" -ForegroundColor White
Write-Host "4. Click 'Save Income'" -ForegroundColor White
Write-Host "5. Verify the income appears in the table" -ForegroundColor White
