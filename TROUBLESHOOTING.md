# Deployment Troubleshooting & Fix Guide

## ⚠️ Issues Fixed

This guide addresses the common issues that prevented your deployment from working.

## ✅ What Was Fixed

### 1. **Missing Production Docker Compose File**
- **Problem**: `docker-compose.prod.yml` was referenced but didn't exist
- **Fix**: Created `docker-compose.prod.yml` with production-ready configuration
- **Changes**:
  - Web service runs on port 80 (standard HTTP)
  - Uses environment variables from `.env` file
  - Proper health checks for database
  - Production restart policies

### 2. **Missing .env File**
- **Problem**: `.env` file didn't exist, only `.env.example`
- **Fix**: Created `.env` file with secure default passwords
- **Action Required**: Update the passwords in `.env` before deploying!

### 3. **Port Configuration**
- **Development** (docker-compose.yml): Port 8000:80
- **Production** (docker-compose.prod.yml): Port 80:80
- **Action Required**: Ensure EC2 security group allows port 80

---

## 🚀 Deployment Steps

### On Your EC2 Instance:

1. **Pull Latest Code**
   ```bash
   cd ~/attendance-app
   git pull origin main
   ```

2. **Update Environment Variables**
   ```bash
   nano .env
   ```
   Update these values:
   ```env
   DB_PASS=YourSecurePassword123!
   MYSQL_ROOT_PASSWORD=YourSecurePassword123!
   MYSQL_PASSWORD=YourSecureUserPassword123!
   APP_URL=http://YOUR-EC2-PUBLIC-IP
   ```

3. **Stop Old Containers**
   ```bash
   docker-compose down
   docker-compose -f docker-compose.prod.yml down
   ```

4. **Remove Old Images (Force Fresh Build)**
   ```bash
   docker images | grep attendance | awk '{print $3}' | xargs -r docker rmi -f
   ```

5. **Start Production Deployment**
   ```bash
   docker-compose -f docker-compose.prod.yml up -d --build
   ```

6. **Check Status**
   ```bash
   docker-compose -f docker-compose.prod.yml ps
   docker logs attendance_web
   docker logs attendance_db
   ```

---

## 🔍 Troubleshooting Commands

### Check if Containers are Running
```bash
docker ps
```
Expected output: 3 containers (attendance_web, attendance_db, attendance_phpmyadmin)

### Check Container Logs
```bash
# Web server logs
docker logs attendance_web --tail 50 -f

# Database logs
docker logs attendance_db --tail 50 -f

# All logs
docker-compose -f docker-compose.prod.yml logs -f
```

### Test Database Connection
```bash
# Enter database container
docker exec -it attendance_db mysql -u root -p

# Enter password when prompted
# Then run:
SHOW DATABASES;
USE attendance_system;
SHOW TABLES;
EXIT;
```

### Test Web Server
```bash
# Check if Apache is responding
curl -I http://localhost

# Test from outside EC2
curl -I http://YOUR-EC2-PUBLIC-IP
```

### Container Shell Access
```bash
# Access web container
docker exec -it attendance_web bash

# Check PHP version and extensions
php -v
php -m | grep mysqli

# Exit container
exit
```

---

## 🔐 EC2 Security Group Configuration

Ensure these ports are open:

| Port | Protocol | Source | Purpose |
|------|----------|--------|---------|
| 22 | TCP | Your IP | SSH Access |
| 80 | TCP | 0.0.0.0/0 | HTTP (Main App) |
| 443 | TCP | 0.0.0.0/0 | HTTPS (if using SSL) |
| 8080 | TCP | Your IP | phpMyAdmin (restrict!) |

### Update Security Group:
1. Go to EC2 Console
2. Select your instance
3. Click Security → Security Groups
4. Edit Inbound Rules
5. Add/Modify rules as needed

---

## 🐛 Common Issues & Solutions

### Issue 1: Port 80 Already in Use
```bash
# Check what's using port 80
sudo lsof -i :80

# If Apache2 or Nginx is running
sudo systemctl stop apache2
sudo systemctl stop nginx
sudo systemctl disable apache2
sudo systemctl disable nginx
```

### Issue 2: Database Won't Start
```bash
# Check if port 3306 is in use
sudo lsof -i :3306

# Remove old database volume
docker-compose -f docker-compose.prod.yml down -v
docker volume rm attendance-management-system-html-css-php-mysql_db_data

# Restart
docker-compose -f docker-compose.prod.yml up -d --build
```

### Issue 3: Permission Denied
```bash
# Fix file permissions
sudo chown -R $USER:$USER ~/attendance-app
chmod +x deploy.sh
```

### Issue 4: GitHub Actions Failing
1. Check GitHub Secrets are set:
   - `EC2_SSH_KEY` - Your .pem file content
   - `EC2_HOST` - Your EC2 public IP
   - `EC2_USER` - `ubuntu`

2. Verify SSH key works:
   ```bash
   ssh -i your-key.pem ubuntu@your-ec2-ip
   ```

3. Check GitHub Actions logs:
   - Go to repository → Actions tab
   - Click on failed workflow
   - Review error messages

### Issue 5: Cannot Connect to Database
```bash
# Check if database is healthy
docker inspect attendance_db | grep -A 10 Health

# Manually test connection
docker exec -it attendance_web bash
php -r "echo mysqli_connect('db', 'root', 'YOUR_PASSWORD', 'attendance_system') ? 'Connected' : 'Failed';"
```

### Issue 6: White Screen / 500 Error
```bash
# Check PHP errors
docker logs attendance_web --tail 100

# Enable PHP error display (temporary debugging)
docker exec -it attendance_web bash
echo "display_errors = On" >> /usr/local/etc/php/php.ini
echo "error_reporting = E_ALL" >> /usr/local/etc/php/php.ini
exit
docker-compose -f docker-compose.prod.yml restart web
```

---

## 🔄 CI/CD Pipeline Fix

### GitHub Actions Secrets Checklist
- [ ] `EC2_SSH_KEY` - Full content of .pem file
- [ ] `EC2_HOST` - EC2 public IP address
- [ ] `EC2_USER` - `ubuntu` (for Ubuntu AMI)

### Test Manual Deploy First
Before relying on CI/CD:
```bash
# SSH to EC2
ssh -i your-key.pem ubuntu@your-ec2-ip

# Run deployment manually
cd ~/attendance-app
./deploy.sh
```

---

## 📊 Health Check Script

Create this script on EC2 to quickly check system health:

```bash
nano ~/check-health.sh
```

Paste:
```bash
#!/bin/bash
echo "=== Docker Status ==="
docker ps --filter "name=attendance"

echo -e "\n=== Container Health ==="
docker inspect attendance_db | grep -A 5 Health

echo -e "\n=== Web Server Response ==="
curl -I http://localhost 2>&1 | head -5

echo -e "\n=== Database Connectivity ==="
docker exec attendance_web php -r "echo mysqli_connect('db', 'root', 'rootpassword', 'attendance_system') ? '✅ Connected' : '❌ Failed';"

echo -e "\n=== Disk Space ==="
df -h / | tail -1

echo -e "\n=== Memory Usage ==="
free -h | head -2
```

Make executable and run:
```bash
chmod +x ~/check-health.sh
./check-health.sh
```

---

## 🎯 Quick Fix Commands

### Complete Reset & Redeploy
```bash
cd ~/attendance-app
docker-compose -f docker-compose.prod.yml down -v
docker system prune -af
git pull origin main
docker-compose -f docker-compose.prod.yml up -d --build
docker-compose -f docker-compose.prod.yml logs -f
```

### Restart Everything
```bash
docker-compose -f docker-compose.prod.yml restart
```

### View Real-time Logs
```bash
docker-compose -f docker-compose.prod.yml logs -f
```

---

## ✅ Verification Steps

After deployment, verify everything works:

1. **Check Containers**
   ```bash
   docker ps
   ```
   Should show 3 running containers

2. **Access Application**
   - Open browser: `http://YOUR-EC2-IP`
   - Should see login page

3. **Access phpMyAdmin**
   - Open browser: `http://YOUR-EC2-IP:8080`
   - Login with root credentials

4. **Test Login**
   - Username: `admin`
   - Password: `admin123`

---

## 📞 Need More Help?

If issues persist:

1. Run the health check script
2. Collect all logs:
   ```bash
   docker-compose -f docker-compose.prod.yml logs > deployment-logs.txt
   ```
3. Check GitHub Actions workflow logs
4. Review EC2 system logs: `/var/log/syslog`

---

## 🔒 Security Reminders

- [ ] Change database passwords in `.env`
- [ ] Change default admin login after first access
- [ ] Restrict phpMyAdmin (port 8080) to your IP only
- [ ] Enable UFW firewall
- [ ] Setup SSL certificate with Let's Encrypt
- [ ] Regular backups of database

---

**Your deployment should now work! 🎉**
