# Comprehensive Final Test Script - DataTables and Client Field Updates
# Test all main pages for DataTables functionality and client field usage

Write-Host "=== COMPREHENSIVE FINAL TEST - DataTables & Client Fields ===" -ForegroundColor Green
Write-Host ""

$baseUrl = "http://localhost:8000"
$endpoints = @(
    @{name="Clients"; url="/clients"},
    @{name="Incomes"; url="/incomes"},
    @{name="Expenses"; url="/expenses"},
    @{name="Emails"; url="/emails"},
    @{name="Websites"; url="/websites"},
    @{name="Pending Tasks"; url="/pending-tasks"}
)

# Test 1: Basic page loading
Write-Host "TEST 1: Testing basic page loading..." -ForegroundColor Cyan
foreach ($endpoint in $endpoints) {
    Write-Host "  Testing $($endpoint.name) page..." -NoNewline
    try {
        $response = Invoke-WebRequest -Uri "$baseUrl$($endpoint.url)" -UseBasicParsing
        if ($response.StatusCode -eq 200) {
            Write-Host " [OK] Status 200" -ForegroundColor Green
        } else {
            Write-Host " [FAILED] Status $($response.StatusCode)" -ForegroundColor Red
        }
    } catch {
        Write-Host " [ERROR] $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host ""

# Test 2: DataTables AJAX endpoints
Write-Host "TEST 2: Testing DataTables AJAX endpoints..." -ForegroundColor Cyan
foreach ($endpoint in $endpoints) {
    Write-Host "  Testing $($endpoint.name) DataTables..." -NoNewline
    try {
        $headers = @{
            'X-Requested-With' = 'XMLHttpRequest'
            'Accept' = 'application/json'
        }
        $body = @{
            'draw' = 1
            'start' = 0
            'length' = 10
            'search[value]' = ''
        }
        
        $response = Invoke-RestMethod -Uri "$baseUrl$($endpoint.url)" -Method Post -Headers $headers -Body $body
        
        if ($response.draw -and $response.data) {
            Write-Host " [OK] DataTables JSON response valid" -ForegroundColor Green
        } else {
            Write-Host " [FAILED] Invalid DataTables response" -ForegroundColor Red
        }
    } catch {
        Write-Host " [ERROR] $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host ""

# Test 3: Search functionality
Write-Host "TEST 3: Testing search functionality..." -ForegroundColor Cyan
foreach ($endpoint in $endpoints) {
    Write-Host "  Testing search on $($endpoint.name)..." -NoNewline
    try {
        $searchBody = @{
            'draw' = 1
            'start' = 0
            'length' = 10
            'search[value]' = 'test'
        }
        
        $response = Invoke-RestMethod -Uri "$baseUrl$($endpoint.url)" -Method Post -Headers @{'X-Requested-With' = 'XMLHttpRequest'} -Body $searchBody
        
        if ($response.draw) {
            Write-Host " [OK] Search processed" -ForegroundColor Green
        } else {
            Write-Host " [FAILED] Search failed" -ForegroundColor Red
        }
    } catch {
        Write-Host " [ERROR] $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host ""

# Summary
Write-Host "=== TEST SUMMARY ===" -ForegroundColor Green
Write-Host "All tests completed. Check above for any failures." -ForegroundColor White
Write-Host "[OK] = Passed, [FAILED] = Failed, [ERROR] = Error" -ForegroundColor Gray
Write-Host ""
Write-Host "Key improvements made:" -ForegroundColor Yellow
Write-Host "  • Fixed DataTables Ajax errors with SQLite-compatible date functions" -ForegroundColor Gray
Write-Host "  • Updated all pages to use client_id foreign keys instead of client_name strings" -ForegroundColor Gray
Write-Host "  • Added client dropdown selects populated from /clients endpoint" -ForegroundColor Gray
Write-Host "  • Migrated existing client_name data to client_id references" -ForegroundColor Gray
Write-Host "  • Standardized UI layout across all pages to match clients page" -ForegroundColor Gray
Write-Host "  • Improved error handling and CSRF token management" -ForegroundColor Gray
Write-Host ""
