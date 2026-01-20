#!/bin/bash

echo "========================================="
echo "Deploying Attendance Management System"
echo "========================================="

# Pull latest code
echo "Pulling latest code from repository..."
git pull origin main

# Stop existing containers
echo "Stopping existing containers..."
docker-compose -f docker-compose.prod.yml down

# Build and start containers
echo "Building and starting containers..."
docker-compose -f docker-compose.prod.yml up -d --build

# Wait for services to be ready
echo "Waiting for services to start..."
sleep 10

# Check container status
echo "Checking container status..."
docker-compose -f docker-compose.prod.yml ps

# Clean up unused images
echo "Cleaning up unused Docker images..."
docker image prune -f

echo "========================================="
echo "Deployment completed!"
echo "========================================="
echo "Application URL: http://$(curl -s http://169.254.169.254/latest/meta-data/public-ipv4)"
echo "PhpMyAdmin URL: http://$(curl -s http://169.254.169.254/latest/meta-data/public-ipv4):8080"
