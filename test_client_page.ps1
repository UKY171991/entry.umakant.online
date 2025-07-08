Write-Host "Starting test for client page data integrity..."

$url = "http://localhost:8000/clients"

# Define expected client data structure
$expectedHeaders = @("NAME", "EMAIL", "PHONE", "ADDRESS", "Action")

# Make a request to the clients page
$response = Invoke-WebRequest -Uri $url -UseBasicParsing

# Check if the request was successful
if ($response.StatusCode -ne 200) {
    Write-Host "Test failed: Could not access the clients page. Status code: $($response.StatusCode)" -ForegroundColor Red
    exit 1
}

$content = $response.Content

# Check for expected table headers
$headersFound = $true
foreach ($header in $expectedHeaders) {
    if ($content -notmatch $header) {
        Write-Host "Test failed: Expected header '$header' not found in the response." -ForegroundColor Red
        $headersFound = $false
    }
}

if (-not $headersFound) {
    exit 1
}

# Check that no email-specific data is present (like template names)
$emailSpecificContent = @("pathology_management", "hospital_management", "last_email_sent_at")
$emailDataFound = $false
foreach ($item in $emailSpecificContent) {
    if ($content -match $item) {
        Write-Host "Test failed: Found email-specific data ('$item') on the clients page." -ForegroundColor Red
        $emailDataFound = $true
    }
}

if ($emailDataFound) {
    exit 1
}

Write-Host "Test passed: The clients page appears to be loading correctly with the expected headers and no email-specific data." -ForegroundColor Green
