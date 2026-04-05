# ── PGFE Docker Health Check Script (PowerShell) ──
# Usage: .\docker-health-check.ps1

param(
    [string]$Service = "all",
    [switch]$Verbose = $false
)

$ErrorActionPreference = "Continue"

# ── Functions ──
function Log-Info {
    param([string]$Message)
    Write-Host "[INFO] $Message" -ForegroundColor Cyan
}

function Log-Success {
    param([string]$Message)
    Write-Host "[OK]   $Message" -ForegroundColor Green
}

function Log-Warning {
    param([string]$Message)
    Write-Host "[WARN] $Message" -ForegroundColor Yellow
}

function Log-Error {
    param([string]$Message)
    Write-Host "[ERR]  $Message" -ForegroundColor Red
}

function Check-Container {
    param([string]$Name)
    
    Log-Info "Checking container: $Name"
    
    $container = docker ps -a --filter "name=$Name" --format "{{.Names}}\t{{.Status}}\t{{.Ports}}"
    
    if (-not $container) {
        Log-Error "Container $Name not found"
        return $false
    }
    
    Write-Host "$container" -ForegroundColor Gray
    
    # Vérifier si healthy
    $status = docker inspect "$Name" --format "{{.State.Health.Status}}" 2>$null
    if ($null -eq $status -or $status -eq "") {
        Log-Warning "No healthcheck for $Name"
        return $true
    }
    
    if ($status -eq "healthy") {
        Log-Success "$Name is healthy"
        return $true
    } elseif ($status -eq "starting") {
        Log-Warning "$Name is starting..."
        return $false
    } else {
        Log-Error "$Name is unhealthy!"
        if ($Verbose) {
            docker inspect "$Name" --format "{{json .State.Health}}" | ConvertFrom-Json | Format-List
        }
        return $false
    }
}

function Check-Port {
    param([int]$Port, [string]$Service)
    
    # Windows netstat
    $connection = netstat -ano 2>$null | Select-String ":$Port "
    
    if ($connection) {
        Log-Success "Port $Port is in use (for $Service)"
        return $true
    } else {
        Log-Warning "Port $Port is NOT in use"
        return $false
    }
}

function Show-Logs {
    param([string]$ServiceName)
    
    Log-Info "Last 20 lines of $ServiceName logs:"
    docker compose logs --tail=20 $ServiceName 2>$null
}

function Check-Connectivity {
    param([string]$Url, [string]$Description)
    
    Log-Info "Checking connectivity to: $Description ($Url)"
    
    try {
        $response = Invoke-WebRequest -Uri $Url -UseBasicParsing -TimeoutSec 5 -ErrorAction Stop
        if ($response.StatusCode -eq 200 -or $response.StatusCode -eq 404) {
            Log-Success "✓ $Description is reachable"
            return $true
        }
    } catch {
        Log-Error "✗ $Description is not reachable: $_"
        return $false
    }
}

# ── Main ──
Write-Host ""
Write-Host "╔════════════════════════════════════════════════════════════════╗" -ForegroundColor Blue
Write-Host "║          PGFE Docker Health Check                             ║" -ForegroundColor Blue
Write-Host "╚════════════════════════════════════════════════════════════════╝" -ForegroundColor Blue
Write-Host ""

# Check Docker
Log-Info "Checking Docker installation..."
try {
    docker --version | Out-Null
    Log-Success "Docker is installed"
} catch {
    Log-Error "Docker is not installed or not in PATH"
    exit 1
}

Write-Host ""

# Check containers
if ($Service -eq "all" -or $Service -eq "database") {
    Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ DATABASE ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Magenta
    $ok1 = Check-Container "pgfe-database"
    Check-Port 3306 "MySQL"
    Write-Host ""
}

if ($Service -eq "all" -or $Service -eq "backend") {
    Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ BACKEND ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Magenta
    $ok2 = Check-Container "pgfe-backend"
    Write-Host ""
}

if ($Service -eq "all" -or $Service -eq "nginx-api") {
    Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ NGINX API GATEWAY ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Magenta
    $ok3 = Check-Container "pgfe-nginx-api"
    Check-Port 8400 "API"
    Check-Connectivity "http://localhost:8400/health" "Backend API"
    Write-Host ""
}

if ($Service -eq "all" -or $Service -eq "frontend") {
    Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ FRONTEND ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Magenta
    $ok4 = Check-Container "pgfe-frontend"
    Check-Port 80 "Frontend"
    Check-Connectivity "http://localhost/health" "Frontend"
    Write-Host ""
}

# Summary
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ SUMMARY ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Magenta
Write-Host ""
Log-Info "For more information, run:"
Write-Host "  docker compose ps                    # Container status"
Write-Host "  docker compose logs -f               # Continuous logs"
Write-Host "  docker compose logs backend          # Backend logs only"
Write-Host "  .\docker-health-check.ps1 -Verbose  # Detailed health info"
Write-Host ""
