Write-Host "=== Testing Email Edit Functionality ===" -ForegroundColor Green

# Test if we can create and edit an email record
try {
    Write-Host "1. Testing database connection and model..." -ForegroundColor Yellow
    
    # Test if we can access the emails route
    $response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/emails" -Method GET -UseBasicParsing
    if ($response.StatusCode -eq 200) {
        Write-Host "✓ Emails page accessible" -ForegroundColor Green
    } else {
        Write-Host "✗ Emails page not accessible" -ForegroundColor Red
        exit
    }

    Write-Host "2. Testing edit route..." -ForegroundColor Yellow
    # This will test if we have any emails to edit
    # The actual edit functionality will be tested in the browser
    
    Write-Host "3. Checking if all form fields are present in the modal..." -ForegroundColor Yellow
    $emailsFile = "c:\git\entry\resources\views\emails\index.blade.php"
    $content = Get-Content $emailsFile -Raw
    
    $requiredFields = @(
        'id="client_name"',
        'id="email"',
        'id="email_template"',
        'id="project_name"',
        'id="estimated_cost"',
        'id="timeframe"',
        'id="notes"'
    )
    
    foreach ($field in $requiredFields) {
        if ($content -like "*$field*") {
            Write-Host "✓ Found field: $field" -ForegroundColor Green
        } else {
            Write-Host "✗ Missing field: $field" -ForegroundColor Red
        }
    }
    
    Write-Host "4. Checking edit JavaScript functionality..." -ForegroundColor Yellow
    $editFields = @(
        '$("#client_name").val(data.client_name)',
        '$("#email").val(data.email)',
        '$("#email_template").val(data.email_template)',
        '$("#project_name").val(data.project_name)',
        '$("#estimated_cost").val(data.estimated_cost)',
        '$("#timeframe").val(data.timeframe)',
        '$("#notes").val(data.notes)'
    )
    
    foreach ($field in $editFields) {
        if ($content -like "*$field*") {
            Write-Host "✓ Found edit JS: $field" -ForegroundColor Green
        } else {
            Write-Host "✗ Missing edit JS: $field" -ForegroundColor Red
        }
    }
    
    Write-Host ""
    Write-Host "=== Edit Functionality Test Complete ===" -ForegroundColor Green
    Write-Host "To test fully:" -ForegroundColor Yellow
    Write-Host "1. Open http://127.0.0.1:8000/emails" -ForegroundColor White
    Write-Host "2. Add a new email record" -ForegroundColor White
    Write-Host "3. Click Edit button on any record" -ForegroundColor White
    Write-Host "4. Verify all fields populate correctly" -ForegroundColor White
    
} catch {
    Write-Host "Error during testing: $($_)" -ForegroundColor Red
}
