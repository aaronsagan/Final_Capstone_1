# Run Database Migrations for Comment Likes Feature
Write-Host "Running database migrations..." -ForegroundColor Green

# Navigate to backend directory
Set-Location -Path "capstone_backend"

# Run migrations
php artisan migrate

Write-Host "`nMigrations completed!" -ForegroundColor Green
Write-Host "The following tables/columns were added:" -ForegroundColor Cyan
Write-Host "  - comment_likes table (new)" -ForegroundColor Yellow
Write-Host "  - update_comments.likes_count column (new)" -ForegroundColor Yellow

# Go back to root
Set-Location -Path ".."

Write-Host "`nYou can now refresh your browser to see the comment likes feature!" -ForegroundColor Green
