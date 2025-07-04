#!/usr/bin/env pwsh

Write-Host "Testing All Application Functionality" -ForegroundColor Green
Write-Host "=====================================" -ForegroundColor Green
Write-Host ""

# Define base URL
$baseUrl = "http://127.0.0.1:8000"

# Test pages
$pages = @(
    @{name="Dashboard"; url="/dashboard"},
    @{name="Clients"; url="/clients"},
    @{name="Income"; url="/incomes"},
    @{name="Expenses"; url="/expenses"},
    @{name="Emails"; url="/emails"},
    @{name="Websites"; url="/websites"},
    @{name="Pending Tasks"; url="/pending-tasks"}
)

Write-Host "Testing Page Accessibility:" -ForegroundColor Yellow
Write-Host "===========================" -ForegroundColor Yellow

foreach ($page in $pages) {
    try {
        $response = Invoke-WebRequest -Uri "$baseUrl$($page.url)" -Method GET -TimeoutSec 10
        if ($response.StatusCode -eq 200) {
            Write-Host "✅ $($page.name): OK (Status: $($response.StatusCode))" -ForegroundColor Green
        } else {
            Write-Host "⚠️  $($page.name): Warning (Status: $($response.StatusCode))" -ForegroundColor Yellow
        }
    }
    catch {
        Write-Host "❌ $($page.name): ERROR - $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "Testing AJAX Endpoints:" -ForegroundColor Yellow
Write-Host "=======================" -ForegroundColor Yellow

# Test AJAX endpoints
$ajaxEndpoints = @(
    @{name="Clients DataTable"; url="/clients"; headers=@{"X-Requested-With"="XMLHttpRequest"}},
    @{name="Income DataTable"; url="/incomes"; headers=@{"X-Requested-With"="XMLHttpRequest"}},
    @{name="Expenses DataTable"; url="/expenses"; headers=@{"X-Requested-With"="XMLHttpRequest"}},
    @{name="Emails DataTable"; url="/emails"; headers=@{"X-Requested-With"="XMLHttpRequest"}},
    @{name="Websites DataTable"; url="/websites"; headers=@{"X-Requested-With"="XMLHttpRequest"}},
    @{name="Pending Tasks DataTable"; url="/pending-tasks"; headers=@{"X-Requested-With"="XMLHttpRequest"}}
)

foreach ($endpoint in $ajaxEndpoints) {
    try {
        $response = Invoke-WebRequest -Uri "$baseUrl$($endpoint.url)" -Method GET -Headers $endpoint.headers -TimeoutSec 10
        if ($response.StatusCode -eq 200) {
            $content = $response.Content | ConvertFrom-Json
            if ($content.data -is [Array]) {
                Write-Host "✅ $($endpoint.name): OK (Returned $($content.data.Count) records)" -ForegroundColor Green
            } else {
                Write-Host "✅ $($endpoint.name): OK (Valid JSON response)" -ForegroundColor Green
            }
        } else {
            Write-Host "⚠️  $($endpoint.name): Warning (Status: $($response.StatusCode))" -ForegroundColor Yellow
        }
    }
    catch {
        Write-Host "❌ $($endpoint.name): ERROR - $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "Summary:" -ForegroundColor Cyan
Write-Host "========" -ForegroundColor Cyan
Write-Host "✅ = Working correctly" -ForegroundColor Green
Write-Host "⚠️  = Needs attention" -ForegroundColor Yellow
Write-Host "❌ = Error/Not working" -ForegroundColor Red
Write-Host ""
Write-Host "Test completed. Check individual results above." -ForegroundColor Cyan
