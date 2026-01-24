#!/bin/bash

# EC2 Initial Setup Script
# Run this script on your fresh EC2 instance to install Docker and dependencies

set -e  # Exit on error

echo "========================================="
echo "AWS EC2 Initial Setup"
echo "Installing Docker and Dependencies"
echo "========================================="
echo ""

# Update system
echo "Updating system packages..."
sudo apt update && sudo apt upgrade -y

# Install dependencies
echo ""
echo "Installing required dependencies..."
sudo apt install -y \
    apt-transport-https \
    ca-certificates \
    curl \
    software-properties-common \
    git \
    ufw

# Install Docker
echo ""
echo "Installing Docker..."
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
rm get-docker.sh

# Add current user to docker group
echo ""
echo "Adding user to docker group..."
sudo usermod -aG docker ${USER}

# Install Docker Compose
echo ""
echo "Installing Docker Compose..."
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Configure UFW Firewall
echo ""
echo "Configuring firewall..."
sudo ufw allow 22/tcp    # SSH
sudo ufw allow 80/tcp    # HTTP
sudo ufw allow 443/tcp   # HTTPS
sudo ufw allow 8080/tcp  # PhpMyAdmin
echo "y" | sudo ufw enable

# Enable Docker service
echo ""
echo "Enabling Docker service..."
sudo systemctl enable docker
sudo systemctl start docker

# Verify installations
echo ""
echo "========================================="
echo "Installation Complete!"
echo "========================================="
echo ""
echo "Installed versions:"
docker --version
docker-compose --version
echo ""
echo "========================================="
echo "IMPORTANT: You need to log out and log back in"
echo "for the Docker group changes to take effect!"
echo "========================================="
echo ""
echo "After logging back in, clone your repository:"
echo "  git clone <your-repo-url>"
echo "  cd Attendance-management-system-HTML-CSS-PHP-MYSQL"
echo "  cp .env.example .env"
echo "  nano .env  # Edit with secure passwords"
echo "  ./ec2-deploy.sh"
echo ""
