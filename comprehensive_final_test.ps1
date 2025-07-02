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
            Write-Host " ✓ OK (200)" -ForegroundColor Green
        } else {
            Write-Host " ✗ Failed ($($response.StatusCode))" -ForegroundColor Red
        }
    } catch {
        Write-Host " ✗ Error: $($_.Exception.Message)" -ForegroundColor Red
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
            Write-Host " ✓ OK (DataTables JSON response)" -ForegroundColor Green
        } else {
            Write-Host " ✗ Invalid DataTables response" -ForegroundColor Red
        }
    } catch {
        Write-Host " ✗ Error: $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host ""

# Test 3: Client endpoints test
Write-Host "TEST 3: Testing client-related functionality..." -ForegroundColor Cyan

# Test clients endpoint
Write-Host "  Testing /clients endpoint..." -NoNewline
try {
    $clientsResponse = Invoke-RestMethod -Uri "$baseUrl/clients" -Headers @{'X-Requested-With' = 'XMLHttpRequest'; 'Accept' = 'application/json'} -Method Post -Body @{'draw'=1; 'start'=0; 'length'=10}
    if ($clientsResponse.data -and $clientsResponse.data.Count -ge 0) {
        Write-Host " ✓ OK ($($clientsResponse.data.Count) clients found)" -ForegroundColor Green
        $hasClients = $clientsResponse.data.Count -gt 0
    } else {
        Write-Host " ✗ No clients data" -ForegroundColor Red
        $hasClients = $false
    }
} catch {
    Write-Host " ✗ Error: $($_.Exception.Message)" -ForegroundColor Red
    $hasClients = $false
}

if ($hasClients) {
    Write-Host "  Sample client data:" -ForegroundColor Yellow
    $clientsResponse.data | Select-Object -First 3 | ForEach-Object {
        Write-Host "    - Client: $($_.client_name)" -ForegroundColor Gray
    }
}

Write-Host ""

# Test 4: Pages that should use client relationships
Write-Host "TEST 4: Testing pages with client relationships..." -ForegroundColor Cyan
$clientPages = @("emails", "websites", "pending-tasks")

foreach ($page in $clientPages) {
    Write-Host "  Testing $page with client relationship..." -NoNewline
    try {
        $pageResponse = Invoke-RestMethod -Uri "$baseUrl/$page" -Headers @{'X-Requested-With' = 'XMLHttpRequest'} -Method Post -Body @{'draw'=1; 'start'=0; 'length'=10}
        
        if ($pageResponse.data) {
            # Check if the data structure looks correct (should have client_name from relationship)
            $hasClientData = $pageResponse.data | Where-Object { $_.client_name -and $_.client_name -ne 'N/A' } | Measure-Object | Select-Object -ExpandProperty Count
            Write-Host " ✓ OK ($hasClientData records with client data)" -ForegroundColor Green
        } else {
            Write-Host " ✗ No data returned" -ForegroundColor Red
        }
    } catch {
        Write-Host " ✗ Error: $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host ""

# Test 5: Search functionality
Write-Host "TEST 5: Testing search functionality..." -ForegroundColor Cyan
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
            Write-Host " ✓ OK (Search processed)" -ForegroundColor Green
        } else {
            Write-Host " ✗ Search failed" -ForegroundColor Red
        }
    } catch {
        Write-Host " ✗ Error: $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host ""

# Summary
Write-Host "=== TEST SUMMARY ===" -ForegroundColor Green
Write-Host "All tests completed. Check above for any failures marked with ✗" -ForegroundColor White
Write-Host "✓ = Passed, ✗ = Failed" -ForegroundColor Gray
Write-Host ""
Write-Host "Key improvements made:" -ForegroundColor Yellow
Write-Host "  • Fixed DataTables Ajax errors with SQLite-compatible date functions" -ForegroundColor Gray
Write-Host "  • Updated all pages to use client_id foreign keys instead of client_name strings" -ForegroundColor Gray
Write-Host "  • Added client dropdown selects populated from /clients endpoint" -ForegroundColor Gray
Write-Host "  • Migrated existing client_name data to client_id references" -ForegroundColor Gray
Write-Host "  • Standardized UI layout across all pages to match clients page" -ForegroundColor Gray
Write-Host "  • Improved error handling and CSRF token management" -ForegroundColor Gray
Write-Host ""
