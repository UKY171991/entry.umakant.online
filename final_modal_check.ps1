Write-Host "=== Final Modal Verification ===" -ForegroundColor Cyan

$pages = @("clients", "incomes", "expenses", "emails", "websites", "pending-tasks")

foreach($page in $pages) {
    Write-Host "`n--- $page ---" -ForegroundColor Yellow
    $path = "c:\git\entry\resources\views\$page\index.blade.php"
    
    if(Test-Path $path) {
        $content = Get-Content $path -Raw
        
        # Essential modal components
        $hasModalDiv = $content -match 'class="modal'
        $hasForm = $content -match '<form.*id=".*Form"'
        $hasCSRF = $content -match '@csrf'
        $hasSaveBtn = $content -match 'id="saveBtn"'
        $hasAddBtn = $content -match '(Add|Create|New).*(?:btn|button)'
        $hasBootstrap4 = $content -match 'data-dismiss="modal"'
        
        Write-Host "✓ Modal Div: $hasModalDiv" -ForegroundColor $(if($hasModalDiv) {"Green"} else {"Red"})
        Write-Host "✓ Form with ID: $hasForm" -ForegroundColor $(if($hasForm) {"Green"} else {"Red"})
        Write-Host "✓ CSRF Token: $hasCSRF" -ForegroundColor $(if($hasCSRF) {"Green"} else {"Red"})
        Write-Host "✓ Save Button: $hasSaveBtn" -ForegroundColor $(if($hasSaveBtn) {"Green"} else {"Red"})
        Write-Host "✓ Add Button: $hasAddBtn" -ForegroundColor $(if($hasAddBtn) {"Green"} else {"Red"})
        Write-Host "✓ Bootstrap 4: $hasBootstrap4" -ForegroundColor $(if($hasBootstrap4) {"Green"} else {"Red"})
        
        $allGood = $hasModalDiv -and $hasForm -and $hasCSRF -and $hasSaveBtn -and $hasAddBtn -and $hasBootstrap4
        Write-Host "✓ Overall Status: $allGood" -ForegroundColor $(if($allGood) {"Green"} else {"Red"})
        
    } else {
        Write-Host "❌ File not found!" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "=== Verification Complete ===" -ForegroundColor Cyan
