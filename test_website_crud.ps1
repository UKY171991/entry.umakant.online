# PowerShell script to test website functionality
Write-Host "=== Testing Website CRUD Functionality ===" -ForegroundColor Green
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

# Test creating a new website
Write-Host ""
Write-Host "Testing website creation..." -ForegroundColor Yellow
$createData = @{
    "_token" = $csrfToken
    "client_id" = "11"
    "website_url" = "https://test-website.com"
    "status" = "UP"
}

$body = ($createData.GetEnumerator() | ForEach-Object { "$($_.Key)=$($_.Value)" }) -join "&"

try {
    $response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/websites" -Method POST -Body $body -ContentType "application/x-www-form-urlencoded" -WebSession $session -Headers @{"X-Requested-With"="XMLHttpRequest"}
    Write-Host "Create Response Status: $($response.StatusCode)" -ForegroundColor Green
    Write-Host "Create Response: $($response.Content)" -ForegroundColor Cyan
} catch {
    Write-Host "Create Error: $($_.Exception.Message)" -ForegroundColor Red
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

# Test editing website with ID 2
Write-Host ""
Write-Host "Testing website edit (ID 2)..." -ForegroundColor Yellow
$editData = @{
    "_token" = $csrfToken
    "_method" = "PUT"
    "client_id" = "11"
    "website_url" = "https://updated-website.com"
    "status" = "DOWN"
}

$editBody = ($editData.GetEnumerator() | ForEach-Object { "$($_.Key)=$($_.Value)" }) -join "&"

try {
    $response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/websites/2" -Method POST -Body $editBody -ContentType "application/x-www-form-urlencoded" -WebSession $session -Headers @{"X-Requested-With"="XMLHttpRequest"}
    Write-Host "Edit Response Status: $($response.StatusCode)" -ForegroundColor Green
    Write-Host "Edit Response: $($response.Content)" -ForegroundColor Cyan
} catch {
    Write-Host "Edit Error: $($_.Exception.Message)" -ForegroundColor Red
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
