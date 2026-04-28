# GitHub Upload Script for Course Registration Portal
# Run this in PowerShell: .\upload_to_github.ps1

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "GitHub Upload Script" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""

# Ask for GitHub username
$username = Read-Host "Enter your GitHub username"
if ([string]::IsNullOrWhiteSpace($username)) {
    Write-Host "Username cannot be empty!" -ForegroundColor Red
    exit
}

$repoName = "course-reg-portal"

Write-Host ""
Write-Host "Repository: https://github.com/$username/$repoName" -ForegroundColor Yellow
Write-Host ""

# Check if git is installed
try {
    $gitVersion = git --version
    Write-Host "✓ Git installed: $gitVersion" -ForegroundColor Green
} catch {
    Write-Host "✗ Git is not installed!" -ForegroundColor Red
    Write-Host "Download from: https://git-scm.com/" -ForegroundColor Yellow
    exit
}

# Initialize git
if (!(Test-Path ".git")) {
    Write-Host "Initializing Git repository..." -ForegroundColor Yellow
    git init
    git branch -M main
    Write-Host "✓ Git initialized" -ForegroundColor Green
} else {
    Write-Host "✓ Git already initialized" -ForegroundColor Green
}

# Add files
Write-Host "Adding files to Git..." -ForegroundColor Yellow
git add .
Write-Host "✓ Files added" -ForegroundColor Green

# Commit
Write-Host "Creating initial commit..." -ForegroundColor Yellow
git commit -m "Initial commit: Course Registration Portal with biometric authentication"
Write-Host "✓ Commit created" -ForegroundColor Green

# Add remote
$remoteUrl = "https://github.com/$username/$repoName.git"
$existingRemote = git remote get-url origin 2>$null

if ($existingRemote -match "fatal") {
    Write-Host "Adding remote origin..." -ForegroundColor Yellow
    git remote add origin $remoteUrl
    Write-Host "✓ Remote added: $remoteUrl" -ForegroundColor Green
} else {
    Write-Host "✓ Remote already exists: $existingRemote" -ForegroundColor Green
    Write-Host "Updating remote to: $remoteUrl" -ForegroundColor Yellow
    git remote set-url origin $remoteUrl
}

# Push to GitHub
Write-Host ""
Write-Host "Pushing to GitHub..." -ForegroundColor Yellow
Write-Host "If prompted, use your GitHub username and Personal Access Token" -ForegroundColor Yellow
Write-Host "Get token from: https://github.com/settings/tokens" -ForegroundColor Yellow
Write-Host ""

git push -u origin main

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "==========================================" -ForegroundColor Green
    Write-Host "SUCCESS! Repository uploaded to:" -ForegroundColor Green
    Write-Host "https://github.com/$username/$repoName" -ForegroundColor Cyan
    Write-Host "==========================================" -ForegroundColor Green
} else {
    Write-Host ""
    Write-Host "Push failed. Common issues:" -ForegroundColor Red
    Write-Host "1. Repository doesn't exist - create it at https://github.com/new" -ForegroundColor Yellow
    Write-Host "2. Authentication failed - use Personal Access Token" -ForegroundColor Yellow
    Write-Host "3. Repository name wrong - check at https://github.com/$username" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "Press any key to exit..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
