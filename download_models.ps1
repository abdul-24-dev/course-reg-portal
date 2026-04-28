# Download face-api.js models for local use
# Run this script in PowerShell: right-click -> Run with PowerShell

$baseUrl = "https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights"
$destDir = "C:\xampp\htdocs\course-reg-portal\face-models"

# Create directory
if (!(Test-Path $destDir)) {
    New-Item -ItemType Directory -Path $destDir -Force
    Write-Host "Created directory: $destDir" -ForegroundColor Green
}

$files = @(
    "tiny_face_detector_model-weights_manifest.json",
    "tiny_face_detector_model-shard1",
    "face_landmark_68_model-weights_manifest.json",
    "face_landmark_68_model-shard1",
    "face_recognition_model-weights_manifest.json",
    "face_recognition_model-shard1",
    "face_recognition_model-shard2"
)

Write-Host "`nDownloading face-api.js models..." -ForegroundColor Cyan
Write-Host "Destination: $destDir`n" -ForegroundColor Yellow

$success = 0
$total = $files.Count

foreach ($file in $files) {
    $url = "$baseUrl/$file"
    $dest = "$destDir\$file"
    
    Write-Host "Downloading $file..." -NoNewline
    
    try {
        # Use Invoke-WebRequest with timeout
        $progressPreference = 'SilentlyContinue'  # Hide progress bar for faster download
        Invoke-WebRequest -Uri $url -OutFile $dest -TimeoutSec 60 -ErrorAction Stop
        $size = (Get-Item $dest).Length / 1KB
        Write-Host " Done! ($($size.ToString('0.0')) KB)" -ForegroundColor Green
        $success++
    }
    catch {
        Write-Host " FAILED!" -ForegroundColor Red
        Write-Host "  Error: $_" -ForegroundColor Red
    }
}

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "Download complete: $success/$total files" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

if ($success -eq $total) {
    Write-Host "All models downloaded successfully!" -ForegroundColor Green
    Write-Host "Face recognition should now work." -ForegroundColor Green
} else {
    Write-Host "Some files failed to download. Please check your internet and try again." -ForegroundColor Red
}

Write-Host "`nPress any key to exit..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
