# AWS EC2 Deployment Guide with Docker & CI/CD

Complete guide to deploy the Attendance Management System on AWS EC2 with Docker and GitHub Actions CI/CD.

---

## Prerequisites

- AWS Account
- GitHub Account
- Domain name (optional, for custom domain)
- Basic knowledge of AWS EC2 and SSH

---

## Step 1: Launch EC2 Instance

### 1.1 Create EC2 Instance
1. Go to AWS Console → EC2 → Launch Instance
2. **Name:** `attendance-system-server`
3. **AMI:** Ubuntu Server 22.04 LTS (Free tier eligible)
4. **Instance Type:** t2.micro (Free tier) or t2.small (recommended)
5. **Key Pair:** Create new key pair or use existing
   - Download the `.pem` file and keep it safe
6. **Network Settings:**
   - Create security group with:
     - SSH (22) - Your IP
     - HTTP (80) - Anywhere (0.0.0.0/0)
     - HTTPS (443) - Anywhere (0.0.0.0/0) 
     - Custom TCP (8080) - Anywhere (for phpMyAdmin)
7. **Storage:** 20 GB gp3
8. Click **Launch Instance**

### 1.2 Connect to EC2
```bash
# Change permission of your key file
chmod 400 your-key-pair.pem

# Connect to EC2
ssh -i your-key-pair.pem ubuntu@your-ec2-public-ip
```

---

## Step 2: Install Docker on EC2

### 2.1 Update System
```bash
sudo apt update && sudo apt upgrade -y
```

### 2.2 Install Docker
```bash
# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Add user to docker group
sudo usermod -aG docker $USER

# Install Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Verify installation
docker --version
docker-compose --version

# Logout and login again for group changes to take effect
exit
```

### 2.3 Reconnect to EC2
```bash
ssh -i your-key-pair.pem ubuntu@your-ec2-public-ip
```

---

## Step 3: Setup Application on EC2

### 3.1 Clone Repository
```bash
# Install Git if not present
sudo apt install git -y

# Clone your repository
cd ~
git clone https://github.com/YOUR_USERNAME/YOUR_REPO_NAME.git attendance-app
cd attendance-app
```

### 3.2 Configure Environment
```bash
# Copy environment file
cp .env.example .env

# Edit .env file with secure passwords
nano .env
```

Update the following in `.env`:
```env
DB_PASS=YourSecurePassword123!
MYSQL_ROOT_PASSWORD=YourSecureRootPassword123!
MYSQL_PASSWORD=YourSecureUserPassword123!
APP_URL=http://your-ec2-public-ip
```

### 3.3 Make Deploy Script Executable
```bash
chmod +x deploy.sh
```

### 3.4 Deploy Application
```bash
docker-compose -f docker-compose.prod.yml up -d --build
```

### 3.5 Verify Deployment
```bash
# Check running containers
docker ps

# Check logs
docker-compose -f docker-compose.prod.yml logs -f
```

---

## Step 4: Setup GitHub Actions CI/CD

### 4.1 Add GitHub Secrets
Go to your GitHub repository → Settings → Secrets and variables → Actions → New repository secret

Add these secrets:

1. **EC2_SSH_KEY**
   ```bash
   # Copy your private key content
   cat your-key-pair.pem
   ```
   Paste entire content including `-----BEGIN RSA PRIVATE KEY-----` and `-----END RSA PRIVATE KEY-----`

2. **EC2_HOST**
   ```
   your-ec2-public-ip
   ```

3. **EC2_USER**
   ```
   ubuntu
   ```

### 4.2 Configure GitHub Repository

1. Push your code to GitHub:
```bash
# On your local machine
cd "C:\Users\Miyuru\Attendance-management-system-HTML-CSS-PHP-MYSQL"

# Initialize git if not already done
git init

# Add remote repository
git remote add origin https://github.com/YOUR_USERNAME/YOUR_REPO_NAME.git

# Add all files
git add .

# Commit
git commit -m "Initial commit with CI/CD setup"

# Push to GitHub
git push -u origin main
```

### 4.3 Test CI/CD Pipeline
- Every push to `main` branch will automatically trigger deployment
- Check Actions tab in GitHub to monitor deployment

---

## Step 5: Access Your Application

### Application URLs:
- **Main App:** http://your-ec2-public-ip
- **phpMyAdmin:** http://your-ec2-public-ip:8080

### Default Login:
- Username: `admin`
- Password: `admin123`

**⚠️ IMPORTANT:** Change default password immediately after first login!

---

## Step 6: Optional - Setup Domain & SSL

### 6.1 Point Domain to EC2
1. Go to your domain registrar (GoDaddy, Namecheap, etc.)
2. Add A Record: `@` → `your-ec2-public-ip`
3. Add A Record: `www` → `your-ec2-public-ip`

### 6.2 Install SSL with Let's Encrypt
```bash
# Install Nginx for reverse proxy
sudo apt install nginx certbot python3-certbot-nginx -y

# Get SSL certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Configure Nginx
sudo nano /etc/nginx/sites-available/attendance
```

Add this configuration:
```nginx
server {
    listen 443 ssl;
    server_name yourdomain.com www.yourdomain.com;

    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;

    location / {
        proxy_pass http://localhost:80;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}

server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}
```

```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/attendance /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

---

## Maintenance Commands

### View Logs
```bash
docker-compose -f docker-compose.prod.yml logs -f
docker-compose -f docker-compose.prod.yml logs web
docker-compose -f docker-compose.prod.yml logs db
```

### Restart Services
```bash
docker-compose -f docker-compose.prod.yml restart
```

### Stop Services
```bash
docker-compose -f docker-compose.prod.yml down
```

### Backup Database
```bash
docker exec attendance_db mysqldump -u root -p attendance_system > backup_$(date +%Y%m%d).sql
```

### Update Application
```bash
cd ~/attendance-app
git pull origin main
docker-compose -f docker-compose.prod.yml up -d --build
```

### Clean Docker
```bash
docker system prune -af
docker volume prune -f
```

---

## Troubleshooting

### Check Container Status
```bash
docker ps -a
docker-compose -f docker-compose.prod.yml ps
```

### Access Container Shell
```bash
docker exec -it attendance_web bash
docker exec -it attendance_db bash
```

### Database Connection Issues
```bash
# Check database logs
docker logs attendance_db

# Restart database
docker-compose -f docker-compose.prod.yml restart db
```

### Port Already in Use
```bash
# Check what's using the port
sudo lsof -i :80
sudo lsof -i :3306

# Kill process if needed
sudo kill -9 PID
```

---

## Security Best Practices

1. **Change Default Credentials**
   - Update database passwords in `.env`
   - Change default admin login

2. **Restrict Security Group**
   - Limit SSH access to your IP only
   - Consider using a VPN or bastion host

3. **Regular Updates**
   ```bash
   sudo apt update && sudo apt upgrade -y
   docker-compose -f docker-compose.prod.yml pull
   ```

4. **Enable Firewall**
   ```bash
   sudo ufw allow 22/tcp
   sudo ufw allow 80/tcp
   sudo ufw allow 443/tcp
   sudo ufw allow 8080/tcp
   sudo ufw enable
   ```

5. **Backup Regularly**
   - Schedule automated database backups
   - Store backups in S3 or external storage

---

## Cost Estimation (AWS)

- **t2.micro (Free Tier):** $0/month (first 12 months)
- **t2.small:** ~$17/month
- **Storage (20GB):** ~$2/month
- **Data Transfer:** First 100GB free

**Total:** ~$0-$20/month depending on instance type

---

## Support

For issues or questions:
- Check GitHub Actions logs
- Review EC2 instance logs: `/var/log/`
- Check Docker logs: `docker-compose logs`

---

## Quick Deployment Checklist

- [ ] EC2 instance created with proper security group
- [ ] Docker and Docker Compose installed
- [ ] Repository cloned on EC2
- [ ] `.env` file configured with secure passwords
- [ ] Application deployed and running
- [ ] GitHub secrets added (EC2_SSH_KEY, EC2_HOST, EC2_USER)
- [ ] CI/CD pipeline tested
- [ ] Default credentials changed
- [ ] Domain configured (optional)
- [ ] SSL certificate installed (optional)
- [ ] Backup strategy in place

---

**Congratulations! Your application is now deployed on AWS EC2 with automated CI/CD! 🎉**
