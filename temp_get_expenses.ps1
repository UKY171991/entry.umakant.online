$headers = @{"X-Requested-With"="XMLHttpRequest"}
Invoke-WebRequest -Uri http://localhost:8000/expenses -Headers $headers -OutFile curl_output.txt -ErrorVariable err -ErrorAction SilentlyContinue
if ($err) {
    Write-Error "Invoke-WebRequest failed: $($err[0].Exception.Message)"
    exit 1
}