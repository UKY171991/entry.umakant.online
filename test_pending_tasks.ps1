# PowerShell script to test pending tasks functionality
Write-Host "=== Testing Pending Tasks CRUD Functionality ===" -ForegroundColor Green
Write-Host ""

# Get CSRF token
Write-Host "Getting CSRF token..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/pending-tasks" -Method GET -SessionVariable session
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

# Test creating a new pending task
Write-Host ""
Write-Host "Testing pending task creation..." -ForegroundColor Yellow
$createData = @{
    "_token" = $csrfToken
    "task_name" = "Test Task Creation"
    "client_id" = "11"
    "description" = "This is a test task to verify the creation functionality works properly"
    "due_date" = "2025-07-10"
    "status" = "Pending"
    "payment" = "100"
    "payment_status" = "Unpaid"
}

$body = ($createData.GetEnumerator() | ForEach-Object { "$($_.Key)=$($_.Value)" }) -join "&"

try {
    $response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/pending-tasks" -Method POST -Body $body -ContentType "application/x-www-form-urlencoded" -WebSession $session -Headers @{"X-Requested-With"="XMLHttpRequest"}
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

Write-Host ""
Write-Host "=== Test Complete ===" -ForegroundColor Green
