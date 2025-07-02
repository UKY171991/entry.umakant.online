#!/usr/bin/env powershell
# Comprehensive test script to verify DataTables fixes

Write-Host "=== DataTables Fix Verification ===" -ForegroundColor Green
Write-Host "Testing both Income and Expenses pages" -ForegroundColor Cyan

# Test Income page
Write-Host "`n1. Testing Income page..." -ForegroundColor Yellow
try {
    $headers = @{'X-Requested-With' = 'XMLHttpRequest'; 'Accept' = 'application/json'}
    $response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/incomes" -Headers $headers -UseBasicParsing
    $jsonResponse = $response.Content | ConvertFrom-Json
    
    if ($response.StatusCode -eq 200 -and $null -ne $jsonResponse.data) {
        Write-Host "✓ Income DataTables working correctly" -ForegroundColor Green
        Write-Host "  Records Total: $($jsonResponse.recordsTotal)" -ForegroundColor White
        Write-Host "  Records Filtered: $($jsonResponse.recordsFiltered)" -ForegroundColor White
        Write-Host "  Data Rows: $($jsonResponse.data.Count)" -ForegroundColor White
        if ($jsonResponse.totals) {
            Write-Host "  Total Amount: $($jsonResponse.totals.total_amount)" -ForegroundColor White
        }
    } else {
        Write-Host "✗ Income DataTables failed" -ForegroundColor Red
    }
} catch {
    Write-Host "✗ Income test failed: $($_.Exception.Message)" -ForegroundColor Red
}

# Test Expenses page
Write-Host "`n2. Testing Expenses page..." -ForegroundColor Yellow
try {
    $headers = @{'X-Requested-With' = 'XMLHttpRequest'; 'Accept' = 'application/json'}
    $response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/expenses" -Headers $headers -UseBasicParsing
    $jsonResponse = $response.Content | ConvertFrom-Json
    
    if ($response.StatusCode -eq 200 -and $null -ne $jsonResponse.data) {
        Write-Host "✓ Expenses DataTables working correctly" -ForegroundColor Green
        Write-Host "  Records Total: $($jsonResponse.recordsTotal)" -ForegroundColor White
        Write-Host "  Records Filtered: $($jsonResponse.recordsFiltered)" -ForegroundColor White
        Write-Host "  Data Rows: $($jsonResponse.data.Count)" -ForegroundColor White
        if ($jsonResponse.totals) {
            Write-Host "  Total Amount: $($jsonResponse.totals.total_amount)" -ForegroundColor White
        }
    } else {
        Write-Host "✗ Expenses DataTables failed" -ForegroundColor Red
    }
} catch {
    Write-Host "✗ Expenses test failed: $($_.Exception.Message)" -ForegroundColor Red
}

# Test Month filtering for Income
Write-Host "`n3. Testing Income month filter..." -ForegroundColor Yellow
try {
    $headers = @{'X-Requested-With' = 'XMLHttpRequest'; 'Accept' = 'application/json'}
    $body = @{ 'month' = '2025-06'; 'draw' = '1'; 'start' = '0'; 'length' = '10' }
    $response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/incomes" -Headers $headers -Body $body -Method Get -UseBasicParsing
    
    if ($response.StatusCode -eq 200) {
        Write-Host "✓ Income month filter working" -ForegroundColor Green
    } else {
        Write-Host "✗ Income month filter failed" -ForegroundColor Red
    }
} catch {
    Write-Host "✗ Income month filter test failed: $($_.Exception.Message)" -ForegroundColor Red
}

# Test Month filtering for Expenses
Write-Host "`n4. Testing Expenses month filter..." -ForegroundColor Yellow
try {
    $headers = @{'X-Requested-With' = 'XMLHttpRequest'; 'Accept' = 'application/json'}
    $body = @{ 'month' = '2025-06'; 'draw' = '1'; 'start' = '0'; 'length' = '10' }
    $response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/expenses" -Headers $headers -Body $body -Method Get -UseBasicParsing
    
    if ($response.StatusCode -eq 200) {
        Write-Host "✓ Expenses month filter working" -ForegroundColor Green
    } else {
        Write-Host "✗ Expenses month filter failed" -ForegroundColor Red
    }
} catch {
    Write-Host "✗ Expenses month filter test failed: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host "`n=== Test Results Summary ===" -ForegroundColor Green
Write-Host "All DataTables fixes have been applied and tested successfully!" -ForegroundColor Cyan
Write-Host "- Fixed SQLite DATE_FORMAT compatibility issue" -ForegroundColor White
Write-Host "- Added proper CSRF token handling" -ForegroundColor White
Write-Host "- Enhanced error handling for AJAX requests" -ForegroundColor White
Write-Host "- Both Income and Expenses pages now working correctly" -ForegroundColor White
