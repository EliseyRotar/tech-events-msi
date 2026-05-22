#!/bin/bash

# Tech Dragons Events - Arch Linux Startup Script

set -e

echo "🚀 Starting Tech Dragons Events Setup for Arch Linux..."

# Check for Docker
if ! command -v docker &> /dev/null; then
    echo "❌ Docker is not installed."
    echo "💡 Install it with: sudo pacman -S docker docker-compose"
    exit 1
fi

# Check for Docker Compose
if ! docker compose version &> /dev/null; then
    echo "❌ Docker Compose is not installed."
    echo "💡 Install it with: sudo pacman -S docker-compose"
    exit 1
fi

# Ensure .env exists
if [ ! -f .env ]; then
    echo "📄 Creating .env from .env.example..."
    cp .env.example .env
fi

# Start Containers
echo "🐳 Spinning up Docker containers..."
docker compose up -d

echo ""
echo "✅ Tech Dragons Events is now running!"
echo "🌐 Access the application at: http://localhost:8080"
echo "🛠️ To stop the app, run: docker compose down"
echo ""
