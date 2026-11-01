param(
    [int]$Port = 8001
)

$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path -Parent $PSScriptRoot
Set-Location $projectRoot

$php = (Get-Command php -ErrorAction Stop).Source
$phpRoot = Split-Path -Parent $php
$extensionsToLoad = @()
$temporaryIniDirectory = $null
$originalIniScanDirectory = $env:PHP_INI_SCAN_DIR
$originalAppTimezone = $env:APP_TIMEZONE
$phpModules = & $php -m
if ($LASTEXITCODE -ne 0) {
    throw 'No se pudo consultar la instalación de PHP.'
}

$requiredExtensions = @{
    'pdo_sqlite' = 'php_pdo_sqlite.dll'
    'sqlite3' = 'php_sqlite3.dll'
    'fileinfo' = 'php_fileinfo.dll'
}

foreach ($extension in $requiredExtensions.GetEnumerator()) {
    if ($phpModules -notcontains $extension.Key) {
        $extensionPath = Join-Path $phpRoot "ext\$($extension.Value)"
        if (-not (Test-Path $extensionPath)) {
            throw "PHP necesita la extensión $($extension.Key) para iniciar el modo de demostración."
        }

        $extensionsToLoad += $extension.Value
    }
}

$temporaryDirectoryName = 'carstumotor-demo-php-' + [guid]::NewGuid().ToString('N')
$temporaryIniDirectory = Join-Path $env:TEMP $temporaryDirectoryName
New-Item -ItemType Directory -Path $temporaryIniDirectory | Out-Null
$iniLines = @('opcache.enable=0', 'opcache.enable_cli=0')

if ($extensionsToLoad.Count -gt 0) {
    $escapedExtensionDirectory = (Join-Path $phpRoot 'ext').Replace('\', '\\')
    $iniLines += "extension_dir=`"$escapedExtensionDirectory`""
    foreach ($extension in $extensionsToLoad) {
        $iniLines += "extension=$extension"
    }
}

Set-Content -Path (Join-Path $temporaryIniDirectory 'demo.ini') -Value $iniLines -Encoding ASCII
if ([string]::IsNullOrWhiteSpace($originalIniScanDirectory)) {
    $env:PHP_INI_SCAN_DIR = $temporaryIniDirectory
} else {
    $env:PHP_INI_SCAN_DIR = "$originalIniScanDirectory;$temporaryIniDirectory"
}

$env:APP_ENV = 'local'
$env:APP_TIMEZONE = 'America/Bogota'
$env:DB_CONNECTION = 'sqlite'
$env:DB_DATABASE = Join-Path $projectRoot 'database\presentation.sqlite'
$env:DB_URL = ''
$passwordBytes = New-Object byte[] 20
$randomGenerator = [System.Security.Cryptography.RandomNumberGenerator]::Create()
$randomGenerator.GetBytes($passwordBytes)
$env:DEMO_LOGIN_PASSWORD = [BitConverter]::ToString($passwordBytes).Replace('-', '')
$randomGenerator.Dispose()

try {
    & $php artisan config:clear
    if ($LASTEXITCODE -ne 0) {
        throw 'No se pudo preparar la configuración local de demostración.'
    }

    & $php artisan migrate --force
    if ($LASTEXITCODE -ne 0) {
        throw 'Fallaron las migraciones de la base local de demostración.'
    }

    & $php artisan db:seed --class=Database\Seeders\DemoDataSeeder --force
    if ($LASTEXITCODE -ne 0) {
        throw 'No se pudieron cargar los datos locales de demostración.'
    }

    Write-Host ''
    Write-Host 'Base aislada: database\presentation.sqlite'
    Write-Host 'URL: http://127.0.0.1:'$Port
    Write-Host 'Cuentas locales de demostración (misma contraseña temporal para todas):'
    Write-Host '  Gerencia: demo.jefe@carstumotor.test'
    Write-Host '  Contabilidad: demo.contador@carstumotor.test'
    Write-Host '  Comercio internacional: demo.internacional@carstumotor.test'
    Write-Host '  Cliente: demo.cliente@carstumotor.test'
    Write-Host "  Contraseña temporal: $env:DEMO_LOGIN_PASSWORD"
    Write-Host 'La base configurada en .env no se modifica ni se utiliza para esta demostración.'
    Write-Host 'Presiona Ctrl+C para detener el servidor.'
    Write-Host ''

    & $php artisan serve --host=127.0.0.1 --port=$Port
    exit $LASTEXITCODE
} finally {
    $env:PHP_INI_SCAN_DIR = $originalIniScanDirectory
    $env:APP_TIMEZONE = $originalAppTimezone
    if ($temporaryIniDirectory -and (Test-Path $temporaryIniDirectory)) {
        Remove-Item -LiteralPath $temporaryIniDirectory -Recurse -Force
    }
}
