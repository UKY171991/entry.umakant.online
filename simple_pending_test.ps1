Write-Host "Testing Pending Tasks Functionality" -ForegroundColor Green

# Test 1: Check if page loads
Write-Host "`n1. Testing page load..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/pending-tasks" -Method GET -UseBasicParsing
    if ($response.StatusCode -eq 200) {
        Write-Host "✓ Page loads successfully" -ForegroundColor Green
        
        if ($response.Content -match 'viewTask') {
            Write-Host "✓ View button JavaScript found" -ForegroundColor Green
        } else {
            Write-Host "✗ View button JavaScript not found" -ForegroundColor Red
        }
        
        if ($response.Content -match 'viewModal') {
            Write-Host "✓ View modal found" -ForegroundColor Green
        } else {
            Write-Host "✗ View modal not found" -ForegroundColor Red
        }
    }
} catch {
    Write-Host "✗ Error: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 2: Check AJAX endpoint
Write-Host "`n2. Testing AJAX endpoint..." -ForegroundColor Yellow
try {
    $headers = @{'X-Requested-With' = 'XMLHttpRequest'}
    $response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/pending-tasks" -Method GET -Headers $headers -UseBasicParsing
    if ($response.StatusCode -eq 200) {
        $data = $response.Content | ConvertFrom-Json
        Write-Host "✓ AJAX endpoint working" -ForegroundColor Green
        Write-Host "  Records: $($data.recordsTotal)" -ForegroundColor Cyan
        
        if ($data.data.Count -gt 0 -and $data.data[0].action -match 'viewTask') {
            Write-Host "✓ View button in actions" -ForegroundColor Green
        } else {
            Write-Host "✗ View button not in actions" -ForegroundColor Red
        }
    }
} catch {
    Write-Host "✗ Error: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host "`nTest completed!" -ForegroundColor Green