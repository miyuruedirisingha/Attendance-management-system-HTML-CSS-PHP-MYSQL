# 🐳 Docker Deployment Quick Reference

## 📦 Essential Commands

### Local Development
```bash
# Start all services
docker-compose up -d

# View logs
docker-compose logs -f

# Stop services
docker-compose down

# Restart services
docker-compose restart

# Rebuild after code changes
docker-compose up -d --build
```

### EC2 Production
```bash
# Initial setup (one time)
./ec2-setup.sh

# Deploy application
./ec2-deploy.sh

# Production commands
docker-compose -f docker-compose.prod.yml up -d
docker-compose -f docker-compose.prod.yml logs -f
docker-compose -f docker-compose.prod.yml down
docker-compose -f docker-compose.prod.yml restart
```

## 🔧 Troubleshooting

### View Container Logs
```bash
# All containers
docker-compose logs -f

# Specific container
docker logs attendance_web -f
docker logs attendance_db -f
docker logs attendance_phpmyadmin -f
```

### Container Management
```bash
# List running containers
docker ps

# List all containers
docker ps -a

# Stop specific container
docker stop attendance_web

# Start specific container
docker start attendance_web

# Restart specific container
docker restart attendance_web

# Remove container
docker rm attendance_web
```

### Database Operations
```bash
# Access MySQL shell
docker exec -it attendance_db mysql -u root -p

# Backup database
docker exec attendance_db mysqldump -u root -p attendance_system > backup.sql

# Restore database
docker exec -i attendance_db mysql -u root -p attendance_system < backup.sql

# View database logs
docker logs attendance_db
```

### Image Management
```bash
# List images
docker images

# Remove image
docker rmi attendance_web

# Remove unused images
docker image prune -f

# Remove all unused data
docker system prune -a
```

### System Information
```bash
# Check Docker version
docker --version
docker-compose --version

# View system-wide information
docker info

# View resource usage
docker stats

# Disk usage
docker system df
```

## 🌐 Access URLs

### Local Development
- **Web App**: http://localhost
- **PhpMyAdmin**: http://localhost:8080
- **MySQL**: localhost:3306

### EC2 Production
- **Web App**: http://YOUR-EC2-IP
- **PhpMyAdmin**: http://YOUR-EC2-IP:8080
- **MySQL**: YOUR-EC2-IP:3306 (not recommended to expose)

### Default Credentials
- **Username**: admin
- **Password**: admin123
- **⚠️ Change immediately after first login!**

## 📁 Important Files

```
.env                      # Environment variables (DO NOT COMMIT)
.env.example              # Template for environment variables
docker-compose.yml        # Development configuration
docker-compose.prod.yml   # Production configuration
Dockerfile                # Container image definition
.dockerignore             # Files to exclude from build
ec2-setup.sh              # EC2 initial setup script
ec2-deploy.sh             # EC2 deployment script
```

## 🔐 Environment Variables

### Required in .env
```env
MYSQL_ROOT_PASSWORD=your_secure_password
MYSQL_USER=attendance_user
MYSQL_PASSWORD=your_secure_password
DB_PASS=your_secure_password
DB_HOST=db
DB_USER=root
DB_NAME=attendance_system
```

### Optional (for custom ports)
```env
WEB_PORT=80
DB_PORT=3306
PHPMYADMIN_PORT=8080
```

## 🚨 Common Issues & Solutions

### Issue: Port already in use
```bash
# Find process using port
sudo lsof -i :80

# Stop the process or change port in .env
echo "WEB_PORT=8000" >> .env
docker-compose up -d
```

### Issue: Database connection failed
```bash
# Check if database is running
docker ps | grep attendance_db

# Check database logs
docker logs attendance_db

# Restart database
docker-compose restart db

# Wait for database to be ready
sleep 10
```

### Issue: Permission denied
```bash
# On Linux/Mac
sudo chmod +x ec2-setup.sh ec2-deploy.sh

# Docker permission
sudo usermod -aG docker $USER
# Log out and log back in
```

### Issue: Containers won't start
```bash
# Check logs
docker-compose logs

# Remove and recreate
docker-compose down -v
docker-compose up -d

# Check disk space
df -h
docker system df
```

### Issue: Changes not reflecting
```bash
# Rebuild containers
docker-compose down
docker-compose up -d --build

# For production
docker-compose -f docker-compose.prod.yml down
docker-compose -f docker-compose.prod.yml up -d --build
```

## 📊 Monitoring

### Check Container Status
```bash
# Quick status
docker-compose ps

# Detailed status
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"

# Resource usage
docker stats --no-stream
```

### Health Checks
```bash
# Check if web is responding
curl http://localhost

# Check database
docker exec attendance_db mysqladmin ping -h localhost -u root -p

# Check all services
docker-compose -f docker-compose.prod.yml ps
```

## 🔄 Update & Redeploy

### Local Update
```bash
git pull origin main
docker-compose down
docker-compose up -d --build
```

### EC2 Update
```bash
# SSH to EC2
ssh -i your-key.pem ubuntu@YOUR-EC2-IP

# Navigate to project
cd ~/Attendance-management-system-HTML-CSS-PHP-MYSQL

# Pull changes
git pull origin main

# Redeploy
./ec2-deploy.sh
```

### Or use GitHub Actions
Just push to main branch - automatic deployment!

## 🆘 Emergency Commands

### Complete Reset
```bash
# Stop everything
docker-compose down -v --remove-orphans

# Remove all containers
docker rm -f $(docker ps -aq)

# Remove all images
docker rmi -f $(docker images -q)

# Remove all volumes
docker volume rm $(docker volume ls -q)

# Start fresh
docker-compose up -d
```

### View All Logs
```bash
# Save logs to file
docker-compose logs > deployment-logs.txt

# View last 100 lines
docker-compose logs --tail=100

# Follow specific service
docker-compose logs -f web
```

## 📞 Need Help?

1. Check container logs: `docker-compose logs -f`
2. Verify .env configuration
3. Check [EC2-DEPLOYMENT-GUIDE.md](EC2-DEPLOYMENT-GUIDE.md)
4. Review [TROUBLESHOOTING.md](TROUBLESHOOTING.md)
5. Check GitHub Issues

## 🎯 Best Practices

1. ✅ Always use `.env` for sensitive data
2. ✅ Never commit `.env` to git
3. ✅ Use `docker-compose.prod.yml` for production
4. ✅ Regular backups of database
5. ✅ Monitor logs regularly
6. ✅ Keep Docker images updated
7. ✅ Use strong passwords
8. ✅ Disable PhpMyAdmin in production

---

**Quick Start**: `docker-compose up -d` → http://localhost
**EC2 Deploy**: `./ec2-deploy.sh` → http://YOUR-EC2-IP
