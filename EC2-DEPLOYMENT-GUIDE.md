# AWS EC2 Docker Deployment Guide

Complete step-by-step guide to deploy the Attendance Management System on AWS EC2 using Docker.

---

## 📋 Prerequisites

- AWS Account
- GitHub/Git repository with your code
- SSH client (PuTTY for Windows, Terminal for Mac/Linux)
- Basic command line knowledge

---

## 🚀 Part 1: Launch EC2 Instance

### Step 1: Create EC2 Instance

1. **Login to AWS Console**
   - Go to [AWS Console](https://console.aws.amazon.com/)
   - Navigate to EC2 Dashboard

2. **Launch Instance**
   - Click "Launch Instance"
   - **Name**: `attendance-system-server`
   - **AMI**: Ubuntu Server 22.04 LTS (Free tier eligible)
   - **Instance Type**: 
     - `t2.micro` (Free tier - 1 GB RAM)
     - `t2.small` (Recommended - 2 GB RAM) - $0.023/hour

3. **Key Pair**
   - Create new key pair or use existing
   - Type: RSA
   - Format: `.pem` (for Mac/Linux) or `.ppk` (for PuTTY on Windows)
   - **Download and save the key file safely!**

4. **Network Settings - Security Group**
   Create a security group with these rules:
   
   | Type        | Protocol | Port  | Source    | Description          |
   |-------------|----------|-------|-----------|----------------------|
   | SSH         | TCP      | 22    | My IP     | SSH access           |
   | HTTP        | TCP      | 80    | 0.0.0.0/0 | Web application      |
   | HTTPS       | TCP      | 443   | 0.0.0.0/0 | Secure web (future)  |
   | Custom TCP  | TCP      | 8080  | 0.0.0.0/0 | PhpMyAdmin           |

5. **Storage**
   - Size: 20 GB gp3 (or 30 GB for more space)

6. **Launch Instance**
   - Click "Launch Instance"
   - Wait for instance to be in "Running" state

### Step 2: Connect to EC2

#### For Mac/Linux:
```bash
# Navigate to directory with your key file
cd ~/Downloads

# Set proper permissions
chmod 400 your-key-pair.pem

# Connect to EC2
ssh -i your-key-pair.pem ubuntu@YOUR-EC2-PUBLIC-IP
```

#### For Windows (using PuTTY):
1. Convert `.pem` to `.ppk` using PuTTYgen
2. Open PuTTY
3. Host: `ubuntu@YOUR-EC2-PUBLIC-IP`
4. Connection → SSH → Auth → Browse for `.ppk` file
5. Click "Open"

---

## 🐳 Part 2: Install Docker on EC2

### Option A: Automated Installation (Recommended)

1. **Upload setup script to EC2:**
```bash
# From your local machine
scp -i your-key-pair.pem ec2-setup.sh ubuntu@YOUR-EC2-PUBLIC-IP:~
```

2. **Run the setup script on EC2:**
```bash
# On EC2 instance
chmod +x ec2-setup.sh
./ec2-setup.sh
```

3. **Log out and log back in:**
```bash
exit
ssh -i your-key-pair.pem ubuntu@YOUR-EC2-PUBLIC-IP
```

### Option B: Manual Installation

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install dependencies
sudo apt install -y apt-transport-https ca-certificates curl software-properties-common git

# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Add user to docker group
sudo usermod -aG docker ${USER}

# Install Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Configure firewall
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 8080/tcp
sudo ufw --force enable

# Enable Docker
sudo systemctl enable docker
sudo systemctl start docker

# Log out and log back in for group changes
exit
```

---

## 📦 Part 3: Deploy Application

### Step 1: Clone Repository

```bash
# On EC2 instance
cd ~
git clone https://github.com/YOUR-USERNAME/Attendance-management-system-HTML-CSS-PHP-MYSQL.git
cd Attendance-management-system-HTML-CSS-PHP-MYSQL
```

### Step 2: Configure Environment

```bash
# Create .env file from example
cp .env.example .env

# Edit .env file with secure passwords
nano .env
```

**Important: Set secure passwords in .env:**
```env
MYSQL_ROOT_PASSWORD=your_strong_password_here_123!
MYSQL_USER=attendance_user
MYSQL_PASSWORD=another_strong_password_456!
DB_PASS=your_strong_password_here_123!
```

Press `Ctrl+X`, then `Y`, then `Enter` to save.

### Step 3: Deploy with Docker

```bash
# Make deploy script executable
chmod +x ec2-deploy.sh

# Run deployment
./ec2-deploy.sh
```

### Step 4: Verify Deployment

```bash
# Check container status
docker-compose -f docker-compose.prod.yml ps

# Check logs
docker-compose -f docker-compose.prod.yml logs -f
```

Press `Ctrl+C` to exit logs.

---

## 🌐 Part 4: Access Your Application

### Get Your EC2 Public IP

1. Go to AWS Console → EC2 → Instances
2. Click on your instance
3. Copy the "Public IPv4 address"

### Access URLs

- **Web Application**: `http://YOUR-EC2-PUBLIC-IP`
- **PhpMyAdmin**: `http://YOUR-EC2-PUBLIC-IP:8080`

### Default Login Credentials

- **Username**: `admin`
- **Password**: `admin123`

**⚠️ Change the default password immediately after first login!**

---

## 🔧 Common Operations

### View Logs
```bash
# All services
docker-compose -f docker-compose.prod.yml logs -f

# Specific service
docker-compose -f docker-compose.prod.yml logs -f web
docker-compose -f docker-compose.prod.yml logs -f db
```

### Restart Services
```bash
docker-compose -f docker-compose.prod.yml restart
```

### Stop Services
```bash
docker-compose -f docker-compose.prod.yml down
```

### Start Services
```bash
docker-compose -f docker-compose.prod.yml up -d
```

### Update Application
```bash
# Pull latest code
git pull origin main

# Redeploy
./ec2-deploy.sh
```

### Backup Database
```bash
# Create backup
docker-compose -f docker-compose.prod.yml exec db mysqldump -u root -p attendance_system > backup_$(date +%Y%m%d).sql

# Download backup to local machine
scp -i your-key-pair.pem ubuntu@YOUR-EC2-PUBLIC-IP:~/Attendance-management-system-HTML-CSS-PHP-MYSQL/backup_*.sql .
```

### Restore Database
```bash
# Upload backup to EC2
scp -i your-key-pair.pem backup.sql ubuntu@YOUR-EC2-PUBLIC-IP:~/

# Restore on EC2
cd ~/Attendance-management-system-HTML-CSS-PHP-MYSQL
docker-compose -f docker-compose.prod.yml exec -T db mysql -u root -p attendance_system < ~/backup.sql
```

---

## 🔒 Security Best Practices

### 1. Change Default Passwords
- Change default admin password in the application
- Use strong passwords in `.env` file

### 2. Restrict SSH Access
```bash
# Edit security group in AWS Console
# Allow SSH (port 22) only from your IP address
```

### 3. Disable PhpMyAdmin in Production
Edit `docker-compose.prod.yml` and comment out the phpmyadmin service:
```yaml
# phpmyadmin:
#   image: phpmyadmin/phpmyadmin
#   ...
```

### 4. Setup HTTPS (SSL)
Use Certbot and Let's Encrypt for free SSL certificates:
```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Get certificate (after setting up domain)
sudo certbot --nginx -d yourdomain.com
```

### 5. Regular Updates
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Update Docker images
docker-compose -f docker-compose.prod.yml pull
docker-compose -f docker-compose.prod.yml up -d
```

---

## 🐛 Troubleshooting

### Issue: Cannot connect to EC2
**Solution**: Check security group allows your IP on port 22

### Issue: Docker permission denied
**Solution**: 
```bash
sudo usermod -aG docker ${USER}
# Log out and log back in
```

### Issue: Containers not starting
**Solution**:
```bash
# Check logs
docker-compose -f docker-compose.prod.yml logs

# Check disk space
df -h

# Restart Docker
sudo systemctl restart docker
```

### Issue: Database connection failed
**Solution**:
```bash
# Check if database is healthy
docker-compose -f docker-compose.prod.yml ps

# Check database logs
docker-compose -f docker-compose.prod.yml logs db

# Restart database
docker-compose -f docker-compose.prod.yml restart db
```

### Issue: Port already in use
**Solution**:
```bash
# Find process using port 80
sudo lsof -i :80

# Kill process
sudo kill -9 <PID>

# Or change port in .env
echo "WEB_PORT=8000" >> .env
```

---

## 💰 Cost Estimation

### AWS EC2 Costs (US East Region)

| Instance Type | vCPU | RAM   | Monthly Cost* | Recommended For |
|---------------|------|-------|---------------|-----------------|
| t2.micro      | 1    | 1 GB  | Free / $8.50  | Testing only    |
| t2.small      | 1    | 2 GB  | $16.79        | Production      |
| t2.medium     | 2    | 4 GB  | $33.58        | High traffic    |

*Approximate costs. Check [AWS Pricing](https://aws.amazon.com/ec2/pricing/) for current rates.

### Free Tier Includes:
- 750 hours/month of t2.micro for 12 months
- 30 GB EBS storage
- 100 GB data transfer out per month

---

## 🎯 Next Steps

1. ✅ Setup custom domain name
2. ✅ Configure SSL/HTTPS
3. ✅ Setup automated backups
4. ✅ Configure monitoring (CloudWatch)
5. ✅ Setup CI/CD with GitHub Actions
6. ✅ Scale with Load Balancer (for high traffic)

---

## 📚 Additional Resources

- [Docker Documentation](https://docs.docker.com/)
- [AWS EC2 Documentation](https://docs.aws.amazon.com/ec2/)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [Let's Encrypt SSL](https://letsencrypt.org/)

---

## 🆘 Need Help?

If you encounter issues:
1. Check logs: `docker-compose -f docker-compose.prod.yml logs`
2. Review [TROUBLESHOOTING.md](TROUBLESHOOTING.md)
3. Check AWS EC2 instance status
4. Verify security group rules
5. Ensure .env file has correct values

---

**Congratulations! Your attendance management system is now running on AWS EC2! 🎉**
