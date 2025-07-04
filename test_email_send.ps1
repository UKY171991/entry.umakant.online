# PowerShell script to test email sending functionality
Write-Host "=== Testing Email Send Functionality ===" -ForegroundColor Green
Write-Host ""

# Test with curl to simulate the AJAX request
$uri = "http://127.0.0.1:8000/emails/send/11"
$headers = @{
    "X-Requested-With" = "XMLHttpRequest"
    "Content-Type" = "application/x-www-form-urlencoded"
    "Accept" = "application/json"
}

# Get CSRF token first
Write-Host "Getting CSRF token..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/emails" -Method GET -SessionVariable session
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

# Test send email endpoint
Write-Host ""
Write-Host "Testing send email endpoint..." -ForegroundColor Yellow
$body = "_token=$csrfToken"

try {
    $response = Invoke-WebRequest -Uri $uri -Method POST -Body $body -Headers $headers -WebSession $session
    Write-Host "Response Status: $($response.StatusCode)" -ForegroundColor Green
    Write-Host "Response Content: $($response.Content)" -ForegroundColor Cyan
} catch {
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
    if ($_.Exception.Response) {
        $statusCode = $_.Exception.Response.StatusCode
        $statusDescription = $_.Exception.Response.StatusDescription
        Write-Host "Status: $statusCode $statusDescription" -ForegroundColor Red
        
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
