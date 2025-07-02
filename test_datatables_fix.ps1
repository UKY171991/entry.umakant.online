#!/usr/bin/env powershell
# Test script to verify the DataTables fix
# Simple test to check if the income page loads

Write-Host "Testing Income DataTables Fix" -ForegroundColor Green
Write-Host "=============================" -ForegroundColor Green

# Test basic income page
Write-Host "Testing basic income page..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/incomes" -UseBasicParsing
    if ($response.StatusCode -eq 200) {
        Write-Host "Income page loads successfully" -ForegroundColor Green
    } else {
        Write-Host "Income page failed to load: $($response.StatusCode)" -ForegroundColor Red
    }
} catch {
    Write-Host "Error accessing income page: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host "Test completed!" -ForegroundColor Green
