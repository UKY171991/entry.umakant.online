# PowerShell script to test website check status functionality
Write-Host "=== Testing Website Check Status Functionality ===" -ForegroundColor Green
Write-Host ""

# Get CSRF token
Write-Host "Getting CSRF token..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/websites" -Method GET -SessionVariable session
    $csrfToken = ""
    if ($response.Content -match 'name="csrf-token" content="([^"]+)"') {
        $csrfToken = $matches[1]
        Write-Host "CSRF Token: $csrfToken" -ForegroundColor Green
    } else {
        Write-Host "Could not extract CSRF token" -ForegroundColor Red
        exit 1
    }
} catch {
    Write-Host "Error getting CSRF token: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

# Test website status check for website ID 2
Write-Host ""
Write-Host "Testing website status check (ID 2)..." -ForegroundColor Yellow
$checkData = @{
    "_token" = $csrfToken
}

$body = ($checkData.GetEnumerator() | ForEach-Object { "$($_.Key)=$($_.Value)" }) -join "&"

try {
    $response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/websites/2/test" -Method POST -Body $body -ContentType "application/x-www-form-urlencoded" -WebSession $session -Headers @{"X-Requested-With"="XMLHttpRequest"}
    Write-Host "Status Check Response Status: $($response.StatusCode)" -ForegroundColor Green
    Write-Host "Status Check Response: $($response.Content)" -ForegroundColor Cyan
} catch {
    Write-Host "Status Check Error: $($_.Exception.Message)" -ForegroundColor Red
    if ($_.Exception.Response) {
        $statusCode = $_.Exception.Response.StatusCode
        Write-Host "Status: $statusCode" -ForegroundColor Red
        
        try {
            $errorContent = $_.Exception.Response.GetResponseStream()
            $reader = New-Object System.IO.StreamReader($errorContent)
            $responseText = $reader.ReadToEnd()
            Write-Host "Error Content: $responseText" -ForegroundColor Red
        } catch {
            Write-Host "Could not read error response" -ForegroundColor Red
        }
    }
}

Write-Host ""
Write-Host "=== Test Complete ===" -ForegroundColor Green
