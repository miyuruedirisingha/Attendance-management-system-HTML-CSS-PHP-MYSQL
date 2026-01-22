#!/bin/bash

# Quick Fix Script for Deployment Issues
# Run this on your EC2 instance

set -e

echo "========================================="
echo "🔧 Attendance System - Quick Fix Script"
echo "========================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if .env exists
if [ ! -f .env ]; then
    echo -e "${RED}❌ .env file not found!${NC}"
    echo "Creating .env from .env.example..."
    cp .env.example .env
    echo -e "${YELLOW}⚠️  Please update .env with your secure passwords!${NC}"
    exit 1
fi

echo -e "${GREEN}✅ .env file found${NC}"

# Check if docker-compose.prod.yml exists
if [ ! -f docker-compose.prod.yml ]; then
    echo -e "${RED}❌ docker-compose.prod.yml not found!${NC}"
    echo "Please pull the latest code from repository"
    exit 1
fi

echo -e "${GREEN}✅ docker-compose.prod.yml found${NC}"

# Stop any running containers
echo ""
echo "📦 Stopping existing containers..."
docker-compose down 2>/dev/null || true
docker-compose -f docker-compose.prod.yml down 2>/dev/null || true

# Remove old images
echo ""
echo "🗑️  Removing old images..."
docker images | grep attendance | awk '{print $3}' | xargs -r docker rmi -f 2>/dev/null || true

# Clean up Docker system
echo ""
echo "🧹 Cleaning Docker system..."
docker system prune -f

# Build and start containers
echo ""
echo "🏗️  Building and starting containers..."
docker-compose -f docker-compose.prod.yml up -d --build

# Wait for containers to be ready
echo ""
echo "⏳ Waiting for services to start (20 seconds)..."
sleep 20

# Check container status
echo ""
echo "📊 Container Status:"
docker-compose -f docker-compose.prod.yml ps

# Check if containers are running
RUNNING=$(docker ps --filter "name=attendance" --format "{{.Names}}" | wc -l)

if [ $RUNNING -eq 3 ]; then
    echo -e "${GREEN}✅ All containers are running!${NC}"
else
    echo -e "${RED}❌ Some containers failed to start${NC}"
    echo "Checking logs..."
    docker-compose -f docker-compose.prod.yml logs --tail 50
    exit 1
fi

# Test web server
echo ""
echo "🌐 Testing web server..."
if curl -f -s http://localhost > /dev/null; then
    echo -e "${GREEN}✅ Web server is responding${NC}"
else
    echo -e "${RED}❌ Web server is not responding${NC}"
    echo "Checking web logs:"
    docker logs attendance_web --tail 20
fi

# Test database
echo ""
echo "🗄️  Testing database..."
docker exec attendance_web php -r "echo mysqli_connect('db', 'root', getenv('DB_PASS'), 'attendance_system') ? 'Connected' : 'Failed';" > /tmp/db_test.txt 2>&1
DB_TEST=$(cat /tmp/db_test.txt)

if [[ $DB_TEST == *"Connected"* ]]; then
    echo -e "${GREEN}✅ Database connection successful${NC}"
else
    echo -e "${RED}❌ Database connection failed${NC}"
    echo "Checking database logs:"
    docker logs attendance_db --tail 20
fi

# Get public IP
PUBLIC_IP=$(curl -s http://169.254.169.254/latest/meta-data/public-ipv4 2>/dev/null || echo "localhost")

# Final summary
echo ""
echo "========================================="
echo -e "${GREEN}🎉 Deployment Complete!${NC}"
echo "========================================="
echo ""
echo "📱 Access URLs:"
echo "   Main App:     http://${PUBLIC_IP}"
echo "   phpMyAdmin:   http://${PUBLIC_IP}:8080"
echo ""
echo "🔐 Default Login:"
echo "   Username: admin"
echo "   Password: admin123"
echo ""
echo "⚠️  IMPORTANT:"
echo "   - Change default admin password"
echo "   - Update database passwords in .env"
echo "   - Restrict phpMyAdmin access to your IP"
echo ""
echo "📊 View logs:"
echo "   docker-compose -f docker-compose.prod.yml logs -f"
echo ""
echo "🔄 Restart services:"
echo "   docker-compose -f docker-compose.prod.yml restart"
echo ""
echo "========================================="
