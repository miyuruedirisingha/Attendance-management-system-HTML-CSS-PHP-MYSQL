#!/bin/bash

# EC2 Docker Deployment Script
# This script deploys the Attendance Management System on AWS EC2 using Docker

set -e  # Exit on error

echo "========================================="
echo "AWS EC2 Docker Deployment"
echo "Attendance Management System"
echo "========================================="
echo ""

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if .env file exists
if [ ! -f .env ]; then
    echo -e "${RED}Error: .env file not found!${NC}"
    echo "Creating .env file from .env.example..."
    cp .env.example .env
    echo -e "${YELLOW}Please edit .env file with secure passwords before deploying!${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Environment file found${NC}"

# Pull latest code (if using git)
if [ -d .git ]; then
    echo ""
    echo "Pulling latest code from repository..."
    git pull origin main || git pull origin master || echo "Could not pull from git"
fi

# Stop existing containers
echo ""
echo "Stopping existing containers..."
docker-compose -f docker-compose.prod.yml down 2>/dev/null || true

# Remove old images (optional)
read -p "Remove old Docker images? (y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    docker-compose -f docker-compose.prod.yml down --rmi all --volumes --remove-orphans 2>/dev/null || true
fi

# Build and start containers
echo ""
echo "Building and starting containers..."
docker-compose -f docker-compose.prod.yml up -d --build

# Wait for services to be ready
echo ""
echo "Waiting for services to start..."
sleep 15

# Check container status
echo ""
echo "Container Status:"
docker-compose -f docker-compose.prod.yml ps

# Check if containers are running
WEB_STATUS=$(docker inspect -f '{{.State.Running}}' attendance_web 2>/dev/null || echo "false")
DB_STATUS=$(docker inspect -f '{{.State.Running}}' attendance_db 2>/dev/null || echo "false")

if [ "$WEB_STATUS" == "true" ] && [ "$DB_STATUS" == "true" ]; then
    echo -e "${GREEN}✓ All containers are running successfully!${NC}"
else
    echo -e "${RED}✗ Some containers failed to start. Check logs with: docker-compose logs${NC}"
    exit 1
fi

# Clean up unused images
echo ""
echo "Cleaning up unused Docker images..."
docker image prune -f

# Get EC2 public IP
echo ""
echo "========================================="
echo -e "${GREEN}Deployment Completed Successfully!${NC}"
echo "========================================="

# Try to get EC2 public IP
PUBLIC_IP=$(curl -s http://169.254.169.254/latest/meta-data/public-ipv4 2>/dev/null || echo "your-ec2-ip")

echo ""
echo "Access your application at:"
echo "  📱 Web Application: http://${PUBLIC_IP}"
echo "  🗄️  PhpMyAdmin:     http://${PUBLIC_IP}:8080"
echo ""
echo "Default login credentials:"
echo "  Username: admin"
echo "  Password: admin123"
echo ""
echo "Useful commands:"
echo "  View logs:        docker-compose -f docker-compose.prod.yml logs -f"
echo "  Stop services:    docker-compose -f docker-compose.prod.yml down"
echo "  Restart services: docker-compose -f docker-compose.prod.yml restart"
echo "  Check status:     docker-compose -f docker-compose.prod.yml ps"
echo ""
