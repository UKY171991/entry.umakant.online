# Comprehensive CRUD Operations Test Script
# Tests all main pages for CRUD functionality

$baseUrl = "http://127.0.0.1:8000"
$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession

Write-Host "=== COMPREHENSIVE CRUD OPERATIONS TEST ===" -ForegroundColor Green
Write-Host "Testing Laravel application at: $baseUrl" -ForegroundColor Yellow
Write-Host ""

# Function to test HTTP endpoint
function Test-Endpoint {
    param(
        [string]$url,
        [string]$method = "GET",
        [hashtable]$headers = @{},
        [string]$body = $null
    )
    
    try {
        $response = if ($body) {
            Invoke-WebRequest -Uri $url -Method $method -Headers $headers -Body $body -WebSession $session -TimeoutSec 10
        } else {
            Invoke-WebRequest -Uri $url -Method $method -Headers $headers -WebSession $session -TimeoutSec 10
        }
        return @{
            StatusCode = $response.StatusCode
            Content = $response.Content
            Success = $true
        }
    }
    catch [System.Net.WebException] {
        $statusCode = [int]$_.Exception.Response.StatusCode
        return @{
            StatusCode = $statusCode
            Content = $_.Exception.Message
            Success = $false
        }
    }
    catch {
        return @{
            StatusCode = 0
            Content = $_.Exception.Message
            Success = $false
        }
    }
}

# Function to extract CSRF token from HTML
function Get-CSRFToken {
    param([string]$html)
    
    if ($html -match 'name="csrf-token" content="([^"]+)"') {
        return $matches[1]
    }
    return $null
}

Write-Host "1. Testing Main Pages Load" -ForegroundColor Cyan
Write-Host "================================" -ForegroundColor Cyan

$pages = @(
    @{Name="Dashboard"; Url="/dashboard"},
    @{Name="Clients"; Url="/clients"},
    @{Name="Income"; Url="/incomes"},
    @{Name="Expenses"; Url="/expenses"},
    @{Name="Email"; Url="/emails"},
    @{Name="Websites"; Url="/websites"},
    @{Name="Pending Tasks"; Url="/pending-tasks"}
)

$csrfToken = $null

foreach ($page in $pages) {
    $result = Test-Endpoint -url "$baseUrl$($page.Url)"
    $status = if ($result.Success -and $result.StatusCode -eq 200) { "✓ PASS" } else { "✗ FAIL ($($result.StatusCode))" }
    Write-Host "$($page.Name): $status" -ForegroundColor $(if ($result.Success -and $result.StatusCode -eq 200) { "Green" } else { "Red" })
    
    # Extract CSRF token from first successful page
    if ($result.Success -and $result.StatusCode -eq 200 -and !$csrfToken) {
        $csrfToken = Get-CSRFToken -html $result.Content
        if ($csrfToken) {
            Write-Host "  CSRF Token extracted: $($csrfToken.Substring(0,10))..." -ForegroundColor Gray
        }
    }
}

Write-Host ""
Write-Host "2. Testing DataTables AJAX Endpoints" -ForegroundColor Cyan
Write-Host "=====================================" -ForegroundColor Cyan

$dataTablesEndpoints = @(
    @{Name="Clients DataTable"; Url="/clients"; Headers=@{"X-Requested-With"="XMLHttpRequest"}},
    @{Name="Income DataTable"; Url="/incomes"; Headers=@{"X-Requested-With"="XMLHttpRequest"}},
    @{Name="Expenses DataTable"; Url="/expenses"; Headers=@{"X-Requested-With"="XMLHttpRequest"}},
    @{Name="Email DataTable"; Url="/emails"; Headers=@{"X-Requested-With"="XMLHttpRequest"}},
    @{Name="Websites DataTable"; Url="/websites"; Headers=@{"X-Requested-With"="XMLHttpRequest"}},
    @{Name="Pending Tasks DataTable"; Url="/pending-tasks"; Headers=@{"X-Requested-With"="XMLHttpRequest"}}
)

foreach ($endpoint in $dataTablesEndpoints) {
    $result = Test-Endpoint -url "$baseUrl$($endpoint.Url)" -headers $endpoint.Headers
    $status = if ($result.Success -and $result.StatusCode -eq 200) { "✓ PASS" } else { "✗ FAIL ($($result.StatusCode))" }
    Write-Host "$($endpoint.Name): $status" -ForegroundColor $(if ($result.Success -and $result.StatusCode -eq 200) { "Green" } else { "Red" })
    
    # Check if response contains expected DataTables structure
    if ($result.Success -and $result.StatusCode -eq 200) {
        if ($result.Content -match '"data":\s*\[' -and $result.Content -match '"recordsTotal"') {
            Write-Host "  DataTables JSON structure: ✓ Valid" -ForegroundColor Green
        } else {
            Write-Host "  DataTables JSON structure: ✗ Invalid" -ForegroundColor Yellow
        }
    }
}

Write-Host ""
Write-Host "3. Testing Create/Edit Form Endpoints" -ForegroundColor Cyan
Write-Host "=====================================" -ForegroundColor Cyan

$createEndpoints = @(
    @{Name="Clients Create"; Url="/clients/create"},
    @{Name="Income Create"; Url="/incomes/create"},
    @{Name="Expenses Create"; Url="/expenses/create"},
    @{Name="Email Create"; Url="/emails/create"},
    @{Name="Websites Create"; Url="/websites/create"},
    @{Name="Pending Tasks Create"; Url="/pending-tasks/create"}
)

foreach ($endpoint in $createEndpoints) {
    $result = Test-Endpoint -url "$baseUrl$($endpoint.Url)"
    $status = if ($result.Success -and $result.StatusCode -eq 200) { "✓ PASS" } else { "✗ FAIL ($($result.StatusCode))" }
    Write-Host "$($endpoint.Name): $status" -ForegroundColor $(if ($result.Success -and $result.StatusCode -eq 200) { "Green" } else { "Red" })
}

Write-Host ""
Write-Host "4. Testing API Endpoints Structure" -ForegroundColor Cyan
Write-Host "==================================" -ForegroundColor Cyan

# Test first item edit endpoints (assuming ID=1 exists from seeders)
$editEndpoints = @(
    @{Name="Client Edit"; Url="/clients/1/edit"},
    @{Name="Income Edit"; Url="/incomes/1/edit"},
    @{Name="Expense Edit"; Url="/expenses/1/edit"},
    @{Name="Email Edit"; Url="/emails/1/edit"},
    @{Name="Website Edit"; Url="/websites/1/edit"},
    @{Name="Pending Task Edit"; Url="/pending-tasks/1/edit"}
)

foreach ($endpoint in $editEndpoints) {
    $result = Test-Endpoint -url "$baseUrl$($endpoint.Url)" -headers @{"X-Requested-With"="XMLHttpRequest"}
    $status = if ($result.Success -and $result.StatusCode -eq 200) { "✓ PASS" } else { "✗ FAIL ($($result.StatusCode))" }
    Write-Host "$($endpoint.Name): $status" -ForegroundColor $(if ($result.Success -and $result.StatusCode -eq 200) { "Green" } else { "Red" })
    
    # Check if response contains JSON data
    if ($result.Success -and $result.StatusCode -eq 200) {
        try {
            $json = $result.Content | ConvertFrom-Json
            if ($json.id) {
                Write-Host "  JSON Response: ✓ Valid (ID: $($json.id))" -ForegroundColor Green
            } else {
                Write-Host "  JSON Response: ✗ Missing ID field" -ForegroundColor Yellow
            }
        } catch {
            Write-Host "  JSON Response: ✗ Invalid JSON" -ForegroundColor Yellow
        }
    }
}

Write-Host ""
Write-Host "5. Summary" -ForegroundColor Cyan
Write-Host "==========" -ForegroundColor Cyan

Write-Host "Test completed. Check individual results above." -ForegroundColor Yellow
Write-Host "Note: This script tests endpoint availability and basic structure." -ForegroundColor Gray
Write-Host "For full CRUD testing, use the browser interface with the running server." -ForegroundColor Gray
Write-Host ""
Write-Host "Server is running at: $baseUrl" -ForegroundColor Green
Write-Host "Ready for manual CRUD testing in browser!" -ForegroundColor Green

Write-Host ""
Write-Host "=== END OF CRUD OPERATIONS TEST ===" -ForegroundColor Green
