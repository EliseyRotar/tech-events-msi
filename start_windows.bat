@echo off
setlocal

echo 🚀 Starting Tech Dragons Events Setup for Windows...

:: Check for Docker
docker --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Docker is not installed or not in PATH.
    echo 💡 Please install Docker Desktop for Windows: https://www.docker.com/products/docker-desktop
    pause
    exit /b 1
)

:: Check for .env
if not exist .env (
    echo 📄 Creating .env from .env.example...
    copy .env.example .env
)

:: Start Containers
echo 🐳 Spinning up Docker containers...
docker compose up -d

echo.
echo ✅ Tech Dragons Events is now running!
echo 🌐 Access the application at: http://localhost:8080
echo 🛠️ To stop the app, run: docker compose down
echo.
pause
