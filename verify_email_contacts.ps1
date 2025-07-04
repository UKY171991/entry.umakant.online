# PowerShell script to verify email template contact information
Write-Host "=== Email Template Contact Information Verification ===" -ForegroundColor Green
Write-Host ""

$expectedMobile = "+91-9453619260"
$expectedEmail = "uky171991@gmail.com"
$expectedWebsite = "https://codeapka.com"

$templateFiles = @(
    "resources\views\emails\templates\website_proposal.blade.php",
    "resources\views\emails\templates\project_status_update.blade.php", 
    "resources\views\emails\templates\project_completion.blade.php",
    "resources\views\emails\templates\general_inquiry.blade.php",
    "resources\views\emails\website_development_update.blade.php"
)

foreach ($file in $templateFiles) {
    Write-Host "Checking: $file" -ForegroundColor Yellow
    
    if (Test-Path $file) {
        $content = Get-Content $file -Raw
        
        $hasMobile = $content -match [regex]::Escape($expectedMobile)
        $hasEmail = $content -match [regex]::Escape($expectedEmail)
        $hasWebsite = $content -match [regex]::Escape($expectedWebsite)
        
        Write-Host "  Mobile ($expectedMobile): " -NoNewline
        if ($hasMobile) { 
            Write-Host "Found" -ForegroundColor Green 
        } else { 
            Write-Host "Missing" -ForegroundColor Red 
        }
        
        Write-Host "  Email ($expectedEmail): " -NoNewline
        if ($hasEmail) { 
            Write-Host "Found" -ForegroundColor Green 
        } else { 
            Write-Host "Missing" -ForegroundColor Red 
        }
        
        Write-Host "  Website ($expectedWebsite): " -NoNewline
        if ($hasWebsite) { 
            Write-Host "Found" -ForegroundColor Green 
        } else { 
            Write-Host "Missing" -ForegroundColor Red 
        }
        
        Write-Host ""
    } else {
        Write-Host "  File not found!" -ForegroundColor Red
        Write-Host ""
    }
}

Write-Host "=== Verification Complete ===" -ForegroundColor Green
