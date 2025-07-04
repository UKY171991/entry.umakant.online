# Test Client Modal Functionality
Write-Host "===== Testing Client Modal Functionality =====" -ForegroundColor Green

# Test 1: Check if the clients page loads
Write-Host "`n1. Testing if clients page loads..." -ForegroundColor Yellow
$response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/clients" -UseBasicParsing
Write-Host "✓ Clients page loads successfully (Status: $($response.StatusCode))" -ForegroundColor Green

# Test 2: Check for required elements in the HTML
Write-Host "`n2. Checking for required HTML elements..." -ForegroundColor Yellow
$html = $response.Content

# Check for Add Client button
if ($html -match 'id="createNewClient"') {
    Write-Host "✓ Add Client button found" -ForegroundColor Green
} else {
    Write-Host "✗ Add Client button NOT found" -ForegroundColor Red
}

# Check for modal structure
if ($html -match 'id="ajaxModel"') {
    Write-Host "✓ Modal container found" -ForegroundColor Green
} else {
    Write-Host "✗ Modal container NOT found" -ForegroundColor Red
}

# Check for form elements
if ($html -match 'id="clientForm"') {
    Write-Host "✓ Client form found" -ForegroundColor Green
} else {
    Write-Host "✗ Client form NOT found" -ForegroundColor Red
}

# Check for save button
if ($html -match 'id="saveBtn"') {
    Write-Host "✓ Save button found" -ForegroundColor Green
} else {
    Write-Host "✗ Save button NOT found" -ForegroundColor Red
}

# Check for required form fields
$requiredFields = @('name', 'email', 'phone', 'address')
foreach ($field in $requiredFields) {
    if ($html -match "name=`"$field`"") {
        Write-Host "✓ Field '$field' found" -ForegroundColor Green
    } else {
        Write-Host "✗ Field '$field' NOT found" -ForegroundColor Red
    }
}

# Test 3: Check for JavaScript dependencies
Write-Host "`n3. Checking for JavaScript dependencies..." -ForegroundColor Yellow

if ($html -match 'jquery') {
    Write-Host "✓ jQuery found" -ForegroundColor Green
} else {
    Write-Host "✗ jQuery NOT found" -ForegroundColor Red
}

if ($html -match 'bootstrap') {
    Write-Host "✓ Bootstrap found" -ForegroundColor Green
} else {
    Write-Host "✗ Bootstrap NOT found" -ForegroundColor Red
}

if ($html -match 'datatables') {
    Write-Host "✓ DataTables found" -ForegroundColor Green
} else {
    Write-Host "✗ DataTables NOT found" -ForegroundColor Red
}

if ($html -match 'toastr') {
    Write-Host "✓ Toastr found" -ForegroundColor Green
} else {
    Write-Host "✗ Toastr NOT found" -ForegroundColor Red
}

Write-Host "`n===== Modal Test Complete =====" -ForegroundColor Green
Write-Host "`nTo manually test the modal:" -ForegroundColor Cyan
Write-Host "1. Open http://127.0.0.1:8000/clients in your browser" -ForegroundColor White
Write-Host "2. Click the 'Add Client' button" -ForegroundColor White
Write-Host "3. Fill in the form fields" -ForegroundColor White
Write-Host "4. Click 'Save Client'" -ForegroundColor White
