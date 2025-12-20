# Docker Deployment Guide - Attendance Management System

## Quick Start - Deploy to Any Server

### Prerequisites
- A server with Docker and Docker Compose installed (Ubuntu, CentOS, AWS EC2, DigitalOcean, etc.)
- SSH access to your server
- Open ports: 80 (HTTP), 8080 (phpMyAdmin)

---

## Step 1: Install Docker on Server

### For Ubuntu/Debian:
```bash
# Connect to your server
ssh user@your-server-ip

# Update system
sudo apt update && sudo apt upgrade -y

# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Install Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Add user to docker group
sudo usermod -aG docker $USER

# Logout and login again
exit
```

### For Amazon Linux 2 / CentOS:
```bash
# Update system
sudo yum update -y

# Install Docker
sudo yum install docker -y
sudo service docker start
sudo systemctl enable docker

# Install Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Add user to docker group
sudo usermod -aG docker $USER

# Logout and login again
exit
```

---

## Step 2: Upload Project to Server

### Option A: Using Git (Recommended)
```bash
# On your server
cd ~
git clone <your-repository-url>
cd "Attendance Management System"
```

### Option B: Using SCP (from your local machine)
```bash
# From Windows (PowerShell)
scp -r "C:\xampp\htdocs\Attendance Management System" user@your-server-ip:~/

# Then on server
cd "Attendance Management System"
```

### Option C: Using FTP/SFTP
Use FileZilla or WinSCP to upload the entire folder to your server.

---

## Step 3: Configure for Production

```bash
# Replace config.php with Docker version
cp config.docker.php config.php

# (Optional) Change default passwords in docker-compose.yml
nano docker-compose.yml
# Update MYSQL_ROOT_PASSWORD and MYSQL_PASSWORD
```

---

## Step 4: Deploy with Docker

```bash
# Build and start all containers
docker-compose up -d

# Check if containers are running
docker-compose ps

# View logs (if needed)
docker-compose logs -f
```

### Expected Output:
```
Creating attendance_db         ... done
Creating attendance_web        ... done
Creating attendance_phpmyadmin ... done
```

---

## Step 5: Access Your Application

- **Main Application**: http://your-server-ip/
- **phpMyAdmin**: http://your-server-ip:8080/
- **Database**: MySQL on port 3306 (internal)

Default database credentials:
- Root password: `rootpassword` (change this in production!)
- Database: `attendance_system`

---

## Management Commands

### Stop the application:
```bash
docker-compose down
```

### Restart the application:
```bash
docker-compose restart
```

### View logs:
```bash
docker-compose logs -f web
docker-compose logs -f db
```

### Update application:
```bash
# Pull latest changes (if using git)
git pull

# Rebuild and restart
docker-compose down
docker-compose up -d --build
```

### Backup database:
```bash
docker exec attendance_db mysqldump -u root -prootpassword attendance_system > backup_$(date +%Y%m%d).sql
```

### Restore database:
```bash
docker exec -i attendance_db mysql -u root -prootpassword attendance_system < backup.sql
```

---

## Security Recommendations for Production

### 1. Change Default Passwords
Edit `docker-compose.yml`:
```yaml
environment:
  MYSQL_ROOT_PASSWORD: your_secure_password_here
  MYSQL_PASSWORD: another_secure_password
```

### 2. Use Environment File
Create `.env` file:
```env
MYSQL_ROOT_PASSWORD=your_secure_password
MYSQL_DATABASE=attendance_system
MYSQL_USER=attendance_user
MYSQL_PASSWORD=your_user_password
```

Then update `docker-compose.yml` to use `env_file: .env`

### 3. Setup Firewall (UFW on Ubuntu)
```bash
sudo ufw allow 22/tcp    # SSH
sudo ufw allow 80/tcp    # HTTP
sudo ufw allow 443/tcp   # HTTPS (if using SSL)
sudo ufw enable
```

### 4. Add SSL Certificate (Let's Encrypt)
```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache -y

# Get certificate (after domain is pointed to server)
sudo certbot --apache -d yourdomain.com
```

### 5. Disable phpMyAdmin in Production
Comment out the phpmyadmin service in `docker-compose.yml` or restrict access.

---

## Troubleshooting

### Container won't start:
```bash
docker-compose logs
```

### Port already in use:
```bash
# Check what's using port 80
sudo netstat -tlnp | grep :80

# Stop conflicting service
sudo systemctl stop apache2  # or nginx
```

### Database connection issues:
```bash
# Access MySQL directly
docker exec -it attendance_db mysql -u root -p

# Check database exists
SHOW DATABASES;
USE attendance_system;
SHOW TABLES;
```

### Permission issues:
```bash
docker-compose exec web chown -R www-data:www-data /var/www/html
docker-compose exec web chmod -R 755 /var/www/html
```

### Clear and rebuild:
```bash
docker-compose down -v
docker-compose up -d --build
```

---

## AWS EC2 Specific Setup

### 1. Security Group Settings:
- **SSH**: Port 22 (Your IP only)
- **HTTP**: Port 80 (0.0.0.0/0)
- **HTTPS**: Port 443 (0.0.0.0/0)
- **phpMyAdmin**: Port 8080 (Your IP only)

### 2. Connect to EC2:
```bash
ssh -i your-key.pem ec2-user@your-ec2-public-dns
```

### 3. Follow steps 1-4 above

---

## Testing Locally Before Upload

Test on your Windows machine with Docker Desktop:
```bash
# Open PowerShell in project directory
cd "C:\xampp\htdocs\Attendance Management System"

# Start containers
docker-compose up -d

# Access at http://localhost
```

---

## Support

If you encounter issues:
1. Check logs: `docker-compose logs -f`
2. Verify containers are running: `docker-compose ps`
3. Check database connection: `docker exec -it attendance_db mysql -u root -p`
4. Review server firewall settings
5. Ensure ports 80, 3306, 8080 are not blocked

---

## Performance Tips

- Use volumes for production data
- Regular database backups
- Monitor container resources: `docker stats`
- Use production-grade reverse proxy (Nginx) for better performance
- Enable caching and compression
