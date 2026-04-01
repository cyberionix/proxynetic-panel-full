# Yerel: config:clear, migrate, ardından queue:work'i yeni PowerShell penceresinde başlatır.
# Çalıştırma: PowerShell'de proje klasöründe: .\start-local-queue.ps1
# PHP yolu: PATH, veya ortam degiskeni PHP_EXE, veya Laragon/XAMPP varsayilan yollari

$ErrorActionPreference = 'Stop'
$ProjectRoot = $PSScriptRoot
Set-Location $ProjectRoot

function Find-Php {
    if ($env:PHP_EXE -and (Test-Path $env:PHP_EXE)) {
        return (Resolve-Path $env:PHP_EXE).Path
    }
    $cmd = Get-Command php -ErrorAction SilentlyContinue
    if ($cmd) {
        return $cmd.Source
    }
    $laragonBase = 'C:\laragon\bin\php'
    if (Test-Path $laragonBase) {
        $dir = Get-ChildItem $laragonBase -Directory -ErrorAction SilentlyContinue |
            Sort-Object { $_.Name } -Descending |
            Select-Object -First 1
        if ($dir) {
            $exe = Join-Path $dir.FullName 'php.exe'
            if (Test-Path $exe) { return $exe }
        }
    }
    foreach ($p in @('C:\xampp\php\php.exe', 'C:\php\php.exe')) {
        if (Test-Path $p) { return $p }
    }
    return $null
}

$php = Find-Php
if (-not $php) {
    Write-Host "php.exe bulunamadi." -ForegroundColor Red
    Write-Host "Cozum: PHP'yi PATH'e ekleyin veya kullanici ortam degiskeni PHP_EXE ile tam yolu verin."
    Write-Host "Ornek Laragon: C:\laragon\bin\php\php-8.3.x-Win32-vs16-x64\php.exe"
    exit 1
}

Write-Host "Proje: $ProjectRoot" -ForegroundColor Cyan
Write-Host "PHP:   $php" -ForegroundColor Cyan
Write-Host ""

& $php artisan config:clear
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

& $php artisan migrate --force --no-interaction
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

Write-Host ""
Write-Host "Kuyruk isçisi yeni pencerede aciliyor; o pencereyi kapatarak durdurursunuz." -ForegroundColor Green
$phpEsc = $php -replace "'", "''"
$rootEsc = $ProjectRoot -replace "'", "''"
Start-Process powershell -ArgumentList @(
    '-NoExit',
    '-Command',
    "Set-Location '$rootEsc'; & '$phpEsc' artisan queue:work database --tries=3"
)

Write-Host "Tamam." -ForegroundColor Green
