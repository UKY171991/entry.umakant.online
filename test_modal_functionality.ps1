# Test Client Modal Functionality
Write-Host "===== Testing Client Modal Functionality =====" -ForegroundColor Green

# Test 1: Check if the clients page loads
Write-Host "`n1. Testing if clients page loads..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/clients" -UseBasicParsing
    if ($response.StatusCode -eq 200) {
        Write-Host "✓ Clients page loads successfully (Status: $($response.StatusCode))" -ForegroundColor Green
    } else {
        Write-Host "✗ Clients page failed to load (Status: $($response.StatusCode))" -ForegroundColor Red
        exit 1
    }
} catch {
    Write-Host "✗ Error loading clients page: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

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

# Test 4: Check modal functionality code
Write-Host "`n4. Checking modal functionality..." -ForegroundColor Yellow

if ($html -match 'createNewClient.*click') {
    Write-Host "✓ Add Client button click handler found" -ForegroundColor Green
} else {
    Write-Host "✗ Add Client button click handler NOT found" -ForegroundColor Red
}

if ($html -match 'saveBtn.*click') {
    Write-Host "✓ Save button click handler found" -ForegroundColor Green
} else {
    Write-Host "✗ Save button click handler NOT found" -ForegroundColor Red
}

if ($html -match 'modal.*show') {
    Write-Host "✓ Modal show functionality found" -ForegroundColor Green
} else {
    Write-Host "✗ Modal show functionality NOT found" -ForegroundColor Red
}

# Test 5: Check CSRF token
Write-Host "`n5. Checking CSRF protection..." -ForegroundColor Yellow

if ($html -match '_token') {
    Write-Host "✓ CSRF token found" -ForegroundColor Green
} else {
    Write-Host "✗ CSRF token NOT found" -ForegroundColor Red
}

# Test 6: Test AJAX endpoint for DataTables
Write-Host "`n6. Testing DataTables AJAX endpoint..." -ForegroundColor Yellow

try {
    $headers = @{
        'X-Requested-With' = 'XMLHttpRequest'
        'Accept' = 'application/json'
    }
    
    $ajaxResponse = Invoke-WebRequest -Uri "http://127.0.0.1:8000/clients" -Headers $headers -UseBasicParsing
    if ($ajaxResponse.StatusCode -eq 200) {
        Write-Host "✓ DataTables AJAX endpoint responds successfully" -ForegroundColor Green
        
        # Try to parse JSON response
        try {
            $jsonData = $ajaxResponse.Content | ConvertFrom-Json
            if ($null -ne $jsonData.data) {
                Write-Host "✓ Valid JSON response with data array" -ForegroundColor Green
            } else {
                Write-Host "✗ JSON response missing data array" -ForegroundColor Red
            }
        } catch {
            Write-Host "✗ Invalid JSON response" -ForegroundColor Red
        }
    } else {
        Write-Host "✗ DataTables AJAX endpoint failed (Status: $($ajaxResponse.StatusCode))" -ForegroundColor Red
    }
} catch {
    Write-Host "✗ Error testing AJAX endpoint: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host "`n===== Modal Functionality Test Complete =====" -ForegroundColor Green
Write-Host "`nTo manually test the modal:" -ForegroundColor Cyan
Write-Host "1. Open http://127.0.0.1:8000/clients in your browser" -ForegroundColor White
Write-Host "2. Click the 'Add Client' button" -ForegroundColor White
Write-Host "3. Fill in the form fields" -ForegroundColor White
Write-Host "4. Click 'Save Client'" -ForegroundColor White
Write-Host "5. Verify the client appears in the table" -ForegroundColor White
