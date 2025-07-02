# Test all endpoints
$baseUrl = "http://localhost:8000"

Write-Host "Testing Laravel endpoints..." -ForegroundColor Green

# Test main pages
$pages = @(
    "/",
    "/clients",
    "/incomes",
    "/expenses", 
    "/emails",
    "/websites",
    "/pending-tasks"
)

foreach ($page in $pages) {
    try {
        $response = Invoke-WebRequest -Uri "$baseUrl$page" -Method GET -TimeoutSec 10
        Write-Host "✓ $page - Status: $($response.StatusCode)" -ForegroundColor Green
    }
    catch {
        Write-Host "✗ $page - Error: $($_.Exception.Message)" -ForegroundColor Red
    }
}

# Test API endpoints
Write-Host "`nTesting API endpoints..." -ForegroundColor Yellow

$apiEndpoints = @(
    "/clients/data",
    "/incomes/data", 
    "/expenses/data",
    "/emails/data",
    "/websites/data",
    "/pending-tasks/data"
)

foreach ($endpoint in $apiEndpoints) {
    try {
        $response = Invoke-WebRequest -Uri "$baseUrl$endpoint" -Method GET -TimeoutSec 10
        $json = $response.Content | ConvertFrom-Json
        Write-Host "✓ $endpoint - Status: $($response.StatusCode), Records: $($json.data.Count)" -ForegroundColor Green
    }
    catch {
        Write-Host "✗ $endpoint - Error: $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host "`nTest completed!" -ForegroundColor Cyan
