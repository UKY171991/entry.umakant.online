# Test Expense Page Functionality
Write-Host "===== Testing Expense Page Fixes =====" -ForegroundColor Green

# Test 1: Check if the expenses page loads
Write-Host "`n1. Testing if expenses page loads..." -ForegroundColor Yellow
$response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/expenses" -UseBasicParsing
Write-Host "✓ Expenses page loads successfully (Status: $($response.StatusCode))" -ForegroundColor Green

# Test 2: Check for required elements
Write-Host "`n2. Checking for required HTML elements..." -ForegroundColor Yellow
$html = $response.Content

# Check for Add Expense button
if ($html -match 'createNewExpense') {
    Write-Host "✓ Add Expense button found" -ForegroundColor Green
} else {
    Write-Host "✗ Add Expense button NOT found" -ForegroundColor Red
}

# Check for modal
if ($html -match 'id="ajaxModel"') {
    Write-Host "✓ Modal found" -ForegroundColor Green
} else {
    Write-Host "✗ Modal NOT found" -ForegroundColor Red
}

# Check for CSRF token
if ($html -match '@csrf') {
    Write-Host "✓ CSRF token found" -ForegroundColor Green
} else {
    Write-Host "✗ CSRF token NOT found" -ForegroundColor Red
}

# Check for Toastr
if ($html -match 'toastr') {
    Write-Host "✓ Toastr library found" -ForegroundColor Green
} else {
    Write-Host "✗ Toastr library NOT found" -ForegroundColor Red
}

# Check for DataTables
if ($html -match 'DataTable') {
    Write-Host "✓ DataTables initialization found" -ForegroundColor Green
} else {
    Write-Host "✗ DataTables initialization NOT found" -ForegroundColor Red
}

# Test 3: Test AJAX endpoint
Write-Host "`n3. Testing DataTables AJAX endpoint..." -ForegroundColor Yellow
try {
    $headers = @{
        'X-Requested-With' = 'XMLHttpRequest'
        'Accept' = 'application/json'
    }
    
    $ajaxResponse = Invoke-WebRequest -Uri "http://127.0.0.1:8000/expenses" -Headers $headers -UseBasicParsing
    if ($ajaxResponse.StatusCode -eq 200) {
        Write-Host "✓ AJAX endpoint responds successfully" -ForegroundColor Green
    } else {
        Write-Host "✗ AJAX endpoint failed" -ForegroundColor Red
    }
} catch {
    Write-Host "✗ Error testing AJAX endpoint: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host "`n✅ Expense Page Testing Complete!" -ForegroundColor Green
Write-Host "Try adding an expense through the modal now." -ForegroundColor Cyan
