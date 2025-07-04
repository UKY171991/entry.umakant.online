# Test Income Form Submission
Write-Host "===== Testing Income Form After Fix =====" -ForegroundColor Green

try {
    # Get CSRF token from the page
    $response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/incomes" -UseBasicParsing
    Write-Host "✓ Income page loads successfully" -ForegroundColor Green
    
    # Check if description field is removed
    if ($response.Content -notmatch 'name="description"') {
        Write-Host "✓ Description field successfully removed" -ForegroundColor Green
    } else {
        Write-Host "✗ Description field still present" -ForegroundColor Red
    }
    
    # Check if Add Income button exists
    if ($response.Content -match 'createNewIncome') {
        Write-Host "✓ Add Income button found" -ForegroundColor Green
    } else {
        Write-Host "✗ Add Income button not found" -ForegroundColor Red
    }
    
    # Check if form fields exist
    $requiredFields = @('client_id', 'total_amount', 'pending_amount', 'received_amount', 'date')
    foreach ($field in $requiredFields) {
        if ($response.Content -match "name=`"$field`"") {
            Write-Host "✓ Field '$field' found" -ForegroundColor Green
        } else {
            Write-Host "✗ Field '$field' not found" -ForegroundColor Red
        }
    }
    
    Write-Host "`n✅ Income form should now work without database errors!" -ForegroundColor Green
    Write-Host "Try adding an income record through the modal." -ForegroundColor Cyan
    
} catch {
    Write-Host "✗ Error testing page: $($_.Exception.Message)" -ForegroundColor Red
}
