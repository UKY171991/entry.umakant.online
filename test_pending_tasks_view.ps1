#!/usr/bin/env pwsh

Write-Host "Testing Pending Tasks View and Edit Modal Functionality" -ForegroundColor Green
Write-Host "============================================================" -ForegroundColor Green

# Test 1: Check if pending tasks page loads
Write-Host "`n1. Testing pending tasks page load..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/pending-tasks" -Method GET -UseBasicParsing
    if ($response.StatusCode -eq 200) {
        Write-Host "✓ Pending tasks page loads successfully" -ForegroundColor Green
        
        # Check if view button is present in the response
        if ($response.Content -match 'viewTask') {
            Write-Host "✓ View button functionality is present in JavaScript" -ForegroundColor Green
        } else {
            Write-Host "✗ View button functionality not found in page" -ForegroundColor Red
        }
        
        # Check if view modal is present
        if ($response.Content -match 'viewModal') {
            Write-Host "✓ View modal is present in the page" -ForegroundColor Green
        } else {
            Write-Host "✗ View modal not found in page" -ForegroundColor Red
        }
    } else {
        Write-Host "✗ Failed to load pending tasks page. Status: $($response.StatusCode)" -ForegroundColor Red
    }
} catch {
    Write-Host "✗ Error loading pending tasks page: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 2: Check DataTables AJAX endpoint
Write-Host "`n2. Testing DataTables AJAX endpoint..." -ForegroundColor Yellow
try {
    $headers = @{
        'X-Requested-With' = 'XMLHttpRequest'
        'Content-Type' = 'application/json'
    }
    
    $response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/pending-tasks" -Method GET -Headers $headers -UseBasicParsing
    if ($response.StatusCode -eq 200) {
        $jsonResponse = $response.Content | ConvertFrom-Json
        if ($jsonResponse.data) {
            Write-Host "✓ DataTables AJAX endpoint working" -ForegroundColor Green
            Write-Host "  - Total records: $($jsonResponse.recordsTotal)" -ForegroundColor Cyan
            Write-Host "  - Filtered records: $($jsonResponse.recordsFiltered)" -ForegroundColor Cyan
            
            # Check if view button is in the action column
            if ($jsonResponse.data.Count -gt 0) {
                $firstRow = $jsonResponse.data[0]
                if ($firstRow.action -match 'viewTask') {
                    Write-Host "✓ View button is present in action column" -ForegroundColor Green
                } else {
                    Write-Host "✗ View button not found in action column" -ForegroundColor Red
                }
            }
        } else {
            Write-Host "✗ Invalid DataTables response format" -ForegroundColor Red
        }
    } else {
        Write-Host "✗ DataTables AJAX failed. Status: $($response.StatusCode)" -ForegroundColor Red
    }
} catch {
    Write-Host "✗ Error testing DataTables AJAX: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 3: Test individual task endpoints (if tasks exist)
Write-Host "`n3. Testing individual task endpoints..." -ForegroundColor Yellow
try {
    # First get the list to find a task ID
    $headers = @{
        'X-Requested-With' = 'XMLHttpRequest'
        'Content-Type' = 'application/json'
    }
    
    $response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/pending-tasks" -Method GET -Headers $headers -UseBasicParsing
    $jsonResponse = $response.Content | ConvertFrom-Json
    
    if ($jsonResponse.data.Count -gt 0) {
        # Extract task ID from the first row's action buttons
        $actionHtml = $jsonResponse.data[0].action
        if ($actionHtml -match 'data-id="(\d+)"') {
            $taskId = $matches[1]
            Write-Host "  Found task ID: $taskId" -ForegroundColor Cyan
            
            # Test show endpoint (for view modal)
            try {
                $showResponse = Invoke-WebRequest -Uri "http://127.0.0.1:8000/pending-tasks/$taskId" -Method GET -UseBasicParsing
                if ($showResponse.StatusCode -eq 200) {
                    $taskData = $showResponse.Content | ConvertFrom-Json
                    Write-Host "✓ Show endpoint working (for view modal)" -ForegroundColor Green
                    Write-Host "  - Task name: $($taskData.task_name)" -ForegroundColor Cyan
                    Write-Host "  - Client included: $($taskData.client -ne $null)" -ForegroundColor Cyan
                } else {
                    Write-Host "✗ Show endpoint failed. Status: $($showResponse.StatusCode)" -ForegroundColor Red
                }
            } catch {
                Write-Host "✗ Error testing show endpoint: $($_.Exception.Message)" -ForegroundColor Red
            }
            
            # Test edit endpoint (for edit modal)
            try {
                $editResponse = Invoke-WebRequest -Uri "http://127.0.0.1:8000/pending-tasks/$taskId/edit" -Method GET -UseBasicParsing
                if ($editResponse.StatusCode -eq 200) {
                    $editData = $editResponse.Content | ConvertFrom-Json
                    Write-Host "✓ Edit endpoint working (for edit modal)" -ForegroundColor Green
                    Write-Host "  - Task name: $($editData.task_name)" -ForegroundColor Cyan
                    Write-Host "  - Client ID: $($editData.client_id)" -ForegroundColor Cyan
                    Write-Host "  - Due date formatted: $($editData.due_date)" -ForegroundColor Cyan
                } else {
                    Write-Host "✗ Edit endpoint failed. Status: $($editResponse.StatusCode)" -ForegroundColor Red
                }
            } catch {
                Write-Host "✗ Error testing edit endpoint: $($_.Exception.Message)" -ForegroundColor Red
            }
        } else {
            Write-Host "✗ Could not extract task ID from action buttons" -ForegroundColor Red
        }
    } else {
        Write-Host "! No tasks found to test individual endpoints" -ForegroundColor Yellow
    }
} catch {
    Write-Host "✗ Error testing individual endpoints: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host "`n============================================================" -ForegroundColor Green
Write-Host "Test completed. Check the results above." -ForegroundColor Green
Write-Host "If all tests pass, the view button and edit modal should work correctly." -ForegroundColor Green