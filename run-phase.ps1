Write-Host "Running automation..."

php artisan migrate:fresh
if ($LASTEXITCODE -ne 0) { exit 1 }

php artisan db:seed
if ($LASTEXITCODE -ne 0) { exit 1 }

php artisan test
if ($LASTEXITCODE -ne 0) { exit 1 }

Write-Host "All steps completed successfully."