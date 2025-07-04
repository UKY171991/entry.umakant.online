#!/usr/bin/env pwsh

Write-Host "=== Testing All CRUD Page Modals ===" -ForegroundColor Green

# List of main CRUD pages to test
$pages = @(
    @{name="Clients"; path="clients"},
    @{name="Incomes"; path="incomes"},
    @{name="Expenses"; path="expenses"},
    @{name="Emails"; path="emails"},
    @{name="Websites"; path="websites"},
    @{name="Pending Tasks"; path="pending-tasks"}
)

foreach ($page in $pages) {
    Write-Host "`n--- Testing $($page.name) ---" -ForegroundColor Yellow
    
    $viewPath = "c:\git\entry\resources\views\$($page.path)\index.blade.php"
    
    if (Test-Path $viewPath) {
        $content = Get-Content $viewPath -Raw
        
        # Check for modal presence
        $hasModal = $content -match 'class="modal'
        Write-Host "✓ Has Modal: $hasModal" -ForegroundColor $(if($hasModal) {"Green"} else {"Red"})
        
        # Check for Add button
        $hasAddButton = $content -match '(Add|Create|New).*(?:btn|button)'
        Write-Host "✓ Has Add Button: $hasAddButton" -ForegroundColor $(if($hasAddButton) {"Green"} else {"Red"})
        
        # Check for CSRF token
        $hasCsrf = $content -match '@csrf'
        Write-Host "✓ Has CSRF Token: $hasCsrf" -ForegroundColor $(if($hasCsrf) {"Green"} else {"Red"})
        
        # Check for form ID
        $hasFormId = $content -match 'id="[a-zA-Z]*Form"'
        Write-Host "✓ Has Form ID: $hasFormId" -ForegroundColor $(if($hasFormId) {"Green"} else {"Red"})
        
        # Check for DataTables
        $hasDataTables = $content -match 'DataTable|data-table'
        Write-Host "✓ Has DataTables: $hasDataTables" -ForegroundColor $(if($hasDataTables) {"Green"} else {"Red"})
        
        # Check for Bootstrap 4 modal syntax
        $isBootstrap4 = $content -match 'data-dismiss="modal"'
        Write-Host "✓ Bootstrap 4 Modal: $isBootstrap4" -ForegroundColor $(if($isBootstrap4) {"Green"} else {"Red"})
        
        # Check for Toastr
        $hasToastr = $content -match 'toastr'
        Write-Host "✓ Has Toastr: $hasToastr" -ForegroundColor $(if($hasToastr) {"Green"} else {"Red"})
        
    } else {
        Write-Host "❌ View file not found: $viewPath" -ForegroundColor Red
    }
}

Write-Host "" 
Write-Host "=== Modal Test Complete ===" -ForegroundColor Green
