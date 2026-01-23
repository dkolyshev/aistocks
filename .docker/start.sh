#!/bin/bash

# Stock Report Generation System - Docker Quick Start Script

echo "================================================"
echo "Stock Report Generation System - Docker Setup"
echo "================================================"
echo ""

# Check if docker is installed
if ! command -v docker &> /dev/null; then
    echo "❌ Error: Docker is not installed"
    echo "Please install Docker first: https://docs.docker.com/get-docker/"
    exit 1
fi

# Check if docker-compose is installed
if ! command -v docker-compose &> /dev/null; then
    echo "❌ Error: Docker Compose is not installed"
    echo "Please install Docker Compose first: https://docs.docker.com/compose/install/"
    exit 1
fi

echo "✅ Docker and Docker Compose are installed"
echo ""

# Navigate to the .docker directory
cd "$(dirname "$0")"

# Check if .env exists, if not create from example
if [ ! -f .env ]; then
    echo "📝 Creating .env file from .env.example..."
    cp .env.example .env
    echo "✅ .env file created"
else
    echo "✅ .env file already exists"
fi

echo ""
echo "🐳 Starting Docker containers..."
echo ""

# Build and start containers
docker-compose up -d --build

# Check if successful
if [ $? -eq 0 ]; then
    echo ""
    echo "================================================"
    echo "✅ SUCCESS! Application is running"
    echo "================================================"
    echo ""
    echo "📍 Access the application at:"
    echo "   http://localhost:8080"
    echo ""
    echo "📊 Report Manager:"
    echo "   http://localhost:8080/reportManager.html"
    echo ""
    echo "🛠️  Useful commands:"
    echo "   View logs:        docker-compose logs -f"
    echo "   Stop containers:  docker-compose down"
    echo "   Restart:          docker-compose restart"
    echo "   Access shell:     docker-compose exec php-app bash"
    echo ""
    echo "📚 Full documentation: .docker/README.md"
    echo "================================================"
else
    echo ""
    echo "❌ Error: Failed to start containers"
    echo "Check the error messages above for details"
    exit 1
fi
