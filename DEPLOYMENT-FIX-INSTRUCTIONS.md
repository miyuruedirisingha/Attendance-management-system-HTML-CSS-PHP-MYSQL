# 🚀 DEPLOYMENT FIX - READ THIS FIRST

## ⚠️ Your deployment wasn't working because:

1. **Missing `docker-compose.prod.yml`** ❌ - Now fixed ✅
2. **Missing `.env` file** ❌ - Now fixed ✅  
3. **Incorrect port configuration** ❌ - Now fixed ✅

---

## 📋 What Has Been Fixed

### New Files Created:
- ✅ `docker-compose.prod.yml` - Production Docker configuration
- ✅ `.env` - Environment variables (⚠️ **UPDATE PASSWORDS!**)
- ✅ `TROUBLESHOOTING.md` - Complete troubleshooting guide
- ✅ `fix-deployment.sh` - Automated fix script
- ✅ `DEPLOYMENT-FIX-INSTRUCTIONS.md` - This file

---

## 🎯 Quick Start - Deploy NOW

### Option 1: Using GitHub Actions (Recommended)

1. **Update .env file locally:**
   ```bash
   # Edit .env and change these passwords:
   DB_PASS=YourSecurePassword123!
   MYSQL_ROOT_PASSWORD=YourSecurePassword123!
   MYSQL_PASSWORD=YourSecurePassword123!
   APP_URL=http://YOUR-EC2-PUBLIC-IP
   ```

2. **Commit and push:**
   ```bash
   git add .
   git commit -m "Fix deployment configuration"
   git push origin main
   ```

3. **Monitor deployment:**
   - Go to GitHub → Your Repository → Actions tab
   - Watch the deployment progress
   - Should complete in ~3-5 minutes

### Option 2: Manual EC2 Deployment

1. **SSH to your EC2:**
   ```bash
   ssh -i your-key.pem ubuntu@your-ec2-ip
   ```

2. **Navigate to app directory:**
   ```bash
   cd ~/attendance-app
   ```

3. **Pull latest code:**
   ```bash
   git pull origin main
   ```

4. **Update .env file on EC2:**
   ```bash
   nano .env
   # Update passwords and save (Ctrl+X, Y, Enter)
   ```

5. **Run the fix script:**
   ```bash
   chmod +x fix-deployment.sh
   ./fix-deployment.sh
   ```

6. **Done!** Access your app at `http://YOUR-EC2-IP`

---

## 🔐 Security Group Configuration

**CRITICAL:** Make sure these ports are open in your EC2 Security Group:

| Port | Type | Source | Purpose |
|------|------|--------|---------|
| 22 | SSH | Your IP | SSH Access |
| **80** | HTTP | 0.0.0.0/0 | **Main App** ⚠️ |
| 443 | HTTPS | 0.0.0.0/0 | SSL (if configured) |
| 8080 | TCP | Your IP | phpMyAdmin |

### How to Update Security Group:

1. Go to AWS Console → EC2
2. Select your instance
3. Click **Security** tab
4. Click on the Security Group link
5. Click **Edit inbound rules**
6. Ensure port 80 is open to 0.0.0.0/0
7. Click **Save rules**

---

## ✅ Verification Steps

After deployment, verify:

### 1. Check Containers Running
```bash
docker ps
```
Should show 3 containers: attendance_web, attendance_db, attendance_phpmyadmin

### 2. Access Application
Open browser: `http://YOUR-EC2-PUBLIC-IP`

### 3. Test Login
- Username: `admin`
- Password: `admin123`

### 4. Access phpMyAdmin
Open browser: `http://YOUR-EC2-PUBLIC-IP:8080`

---

## 🐛 Still Having Issues?

### Common Problems:

#### Problem 1: Cannot access website
**Solution:**
```bash
# Check if port 80 is in use
sudo lsof -i :80

# Stop Apache if running
sudo systemctl stop apache2
sudo systemctl disable apache2

# Restart containers
docker-compose -f docker-compose.prod.yml restart
```

#### Problem 2: Database connection failed
**Solution:**
```bash
# Check database logs
docker logs attendance_db

# Restart database
docker-compose -f docker-compose.prod.yml restart db
```

#### Problem 3: GitHub Actions failing
**Solution:**
Check these GitHub Secrets are set correctly:
- `EC2_SSH_KEY` - Full .pem file content (including BEGIN/END lines)
- `EC2_HOST` - Your EC2 public IP (no http://)
- `EC2_USER` - `ubuntu`

#### Problem 4: White screen / PHP errors
**Solution:**
```bash
# View logs
docker logs attendance_web --tail 50

# Check database connection
docker exec attendance_web php -r "var_dump(mysqli_connect('db', 'root', getenv('DB_PASS'), 'attendance_system'));"
```

---

## 📚 Additional Resources

- **Full Troubleshooting Guide:** See `TROUBLESHOOTING.md`
- **EC2 Setup Guide:** See `EC2-SETUP.md`
- **Deployment Guide:** See `DEPLOYMENT-GUIDE.md`

---

## 🔄 Maintenance Commands

```bash
# View logs
docker-compose -f docker-compose.prod.yml logs -f

# Restart services
docker-compose -f docker-compose.prod.yml restart

# Stop services
docker-compose -f docker-compose.prod.yml down

# Update application
cd ~/attendance-app
git pull origin main
docker-compose -f docker-compose.prod.yml up -d --build

# Backup database
docker exec attendance_db mysqldump -u root -pYourPassword attendance_system > backup.sql
```

---

## 🎉 Success Checklist

- [ ] Files created: docker-compose.prod.yml, .env
- [ ] .env passwords updated
- [ ] Code pushed to GitHub
- [ ] EC2 Security Group allows port 80
- [ ] Deployment script executed successfully
- [ ] Website accessible at http://YOUR-EC2-IP
- [ ] Login works with admin/admin123
- [ ] Default passwords changed

---

## 💡 Pro Tips

1. **Always backup before updates:**
   ```bash
   docker exec attendance_db mysqldump -u root -p attendance_system > backup_$(date +%Y%m%d).sql
   ```

2. **Monitor logs in real-time:**
   ```bash
   docker-compose -f docker-compose.prod.yml logs -f
   ```

3. **Quick restart after code changes:**
   ```bash
   git pull && docker-compose -f docker-compose.prod.yml up -d --build
   ```

4. **Check system resources:**
   ```bash
   docker stats
   ```

---

## 📞 Need Help?

If you're still stuck:

1. Run the health check:
   ```bash
   # Create health check script from TROUBLESHOOTING.md
   ```

2. Collect logs:
   ```bash
   docker-compose -f docker-compose.prod.yml logs > deployment-logs.txt
   ```

3. Check GitHub Actions logs in your repository

---

**Your deployment should now be working! 🚀**

Remember to:
- ⚠️ Change database passwords
- ⚠️ Change default admin login
- ⚠️ Setup SSL certificate for HTTPS
- ⚠️ Enable regular backups
