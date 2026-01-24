# 🤖 CI/CD Setup Guide - GitHub Actions + Docker + EC2

Automated deployment pipeline that deploys your application to AWS EC2 whenever you push code.

---

## 📋 Overview

**Workflow**: Push to GitHub → GitHub Actions → Build Docker Images → Deploy to EC2

**Trigger**: Automatic on push to `main` or `master` branch (or manual trigger)

---

## 🚀 Quick Setup (5 Steps)

### Step 1: Prepare EC2 Instance

1. **Launch EC2 Instance** on AWS Console
   - Ubuntu 22.04 LTS
   - t2.small or larger
   - Security Group: Ports 22, 80, 443, 8080 open

2. **Connect to EC2 and install Docker**:
```bash
ssh -i your-key.pem ubuntu@YOUR-EC2-IP

# Clone repository
git clone https://github.com/YOUR-USERNAME/YOUR-REPO.git
cd YOUR-REPO

# Run setup script
chmod +x ec2-setup.sh
./ec2-setup.sh

# Log out and back in
exit
ssh -i your-key.pem ubuntu@YOUR-EC2-IP
```

3. **Create app directory and .env**:
```bash
mkdir -p ~/attendance-app
cd ~/attendance-app
cp .env.example .env
nano .env  # Add your secure passwords
```

---

### Step 2: Configure GitHub Secrets

Go to your GitHub repository: **Settings → Secrets and variables → Actions**

Add these secrets:

| Secret Name | Value | Where to Get It |
|-------------|-------|-----------------|
| `EC2_SSH_KEY` | Your entire .pem file content | Open your key file in text editor, copy all |
| `EC2_HOST` | Your EC2 public IP | AWS Console → EC2 → Your Instance |
| `EC2_USER` | `ubuntu` | Default for Ubuntu AMI |

**How to add EC2_SSH_KEY**:
```bash
# On Windows (PowerShell)
Get-Content your-key.pem | clip

# On Mac/Linux
cat your-key.pem | pbcopy  # Mac
cat your-key.pem           # Linux (copy output)
```

Then in GitHub:
1. Click "New repository secret"
2. Name: `EC2_SSH_KEY`
3. Value: Paste entire key (including `-----BEGIN RSA PRIVATE KEY-----` and `-----END RSA PRIVATE KEY-----`)
4. Click "Add secret"

Repeat for `EC2_HOST` and `EC2_USER`.

---

### Step 3: Update .env on EC2

Make sure your EC2 has the .env file configured:

```bash
ssh -i your-key.pem ubuntu@YOUR-EC2-IP
cd ~/attendance-app

# Create/edit .env
nano .env
```

Add:
```env
MYSQL_ROOT_PASSWORD=your_secure_password_123
MYSQL_USER=attendance_user
MYSQL_PASSWORD=your_secure_password_456
DB_PASS=your_secure_password_123
DB_HOST=db
DB_USER=root
DB_NAME=attendance_system
```

---

### Step 4: Test Deployment

Make a small change and push:

```bash
git add .
git commit -m "Test CI/CD deployment"
git push origin main
```

Watch the deployment:
1. Go to your GitHub repository
2. Click **Actions** tab
3. Click on the running workflow
4. Watch real-time logs

---

### Step 5: Verify

After GitHub Actions completes:

1. ✅ Check Actions tab shows green checkmark
2. ✅ Visit `http://YOUR-EC2-IP`
3. ✅ Application should be running
4. ✅ Login with `admin` / `admin123`

---

## 🎯 How It Works

### Automatic Deployment Flow

```
1. You push code to GitHub
   ↓
2. GitHub Actions triggers
   ↓
3. Code is synced to EC2 via rsync
   ↓
4. Docker containers are rebuilt
   ↓
5. New containers start
   ↓
6. Old containers are removed
   ↓
7. Deployment complete! 🎉
```

### Manual Deployment Trigger

You can also trigger deployment manually:

1. Go to **Actions** tab
2. Click "Deploy to EC2 with Docker"
3. Click "Run workflow"
4. Select branch
5. Click "Run workflow"

---

## 🔧 Troubleshooting

### Issue: "Permission denied (publickey)"

**Solution**: Check EC2_SSH_KEY secret
- Must include `-----BEGIN RSA PRIVATE KEY-----`
- Must include `-----END RSA PRIVATE KEY-----`
- No extra spaces or line breaks

### Issue: "Host key verification failed"

**Already handled** with `StrictHostKeyChecking=no` in workflow

### Issue: Containers won't start

**Check logs**:
1. Go to Actions → Failed workflow
2. Expand "Deploy to EC2" step
3. Look for error messages

Or SSH to EC2:
```bash
ssh -i your-key.pem ubuntu@YOUR-EC2-IP
cd ~/attendance-app
docker-compose -f docker-compose.prod.yml logs
```

### Issue: "rsync: command not found"

Rsync is pre-installed on GitHub Actions runners. If issues persist, the workflow will fall back gracefully.

---

## 📊 Monitoring Deployments

### View Deployment History

GitHub → Repository → Actions
- ✅ Green: Successful
- ❌ Red: Failed  
- 🟡 Yellow: In progress

### Check Application Status on EC2

```bash
ssh -i your-key.pem ubuntu@YOUR-EC2-IP
cd ~/attendance-app

# Container status
docker-compose -f docker-compose.prod.yml ps

# View logs
docker-compose -f docker-compose.prod.yml logs -f

# Resource usage
docker stats
```

---

## 🔐 Security Best Practices

### 1. Protect Your Secrets
- ✅ Never commit `.env` file
- ✅ Never expose SSH keys
- ✅ Use strong passwords
- ✅ Rotate secrets regularly

### 2. Secure EC2 Access
```bash
# In AWS Console, edit Security Group:
# Change SSH (port 22) from 0.0.0.0/0 to "My IP"
```

### 3. Use Environment-Specific Branches
- `main` → Production
- `staging` → Staging environment
- `develop` → Development

### 4. Add Branch Protection
Repository Settings → Branches → Add rule:
- ✅ Require pull request reviews
- ✅ Require status checks to pass

---

## 🎨 Customization

### Deploy to Multiple Environments

Create separate workflow files:

**.github/workflows/deploy-staging.yml**:
```yaml
name: Deploy to Staging
on:
  push:
    branches: [staging]
# Use different secrets: STAGING_EC2_HOST, etc.
```

**.github/workflows/deploy-production.yml**:
```yaml
name: Deploy to Production
on:
  push:
    branches: [main]
# Use production secrets
```

### Add Slack Notifications

Add this step to workflow:
```yaml
- name: Slack Notification
  uses: 8398a7/action-slack@v3
  with:
    status: ${{ job.status }}
    webhook_url: ${{ secrets.SLACK_WEBHOOK }}
  if: always()
```

### Add Health Check

Add after deployment:
```yaml
- name: Health Check
  run: |
    sleep 10
    response=$(curl -s -o /dev/null -w "%{http_code}" http://${{ secrets.EC2_HOST }})
    if [ $response -eq 200 ]; then
      echo "✅ Health check passed"
    else
      echo "❌ Health check failed"
      exit 1
    fi
```

---

## 📈 Advanced Features

### Rollback on Failure

The workflow automatically stops if containers fail to start. For manual rollback:

```bash
ssh -i your-key.pem ubuntu@YOUR-EC2-IP
cd ~/attendance-app
git checkout HEAD~1  # Go back one commit
./ec2-deploy.sh
```

### Database Backups Before Deployment

Add to workflow before deployment:
```yaml
- name: Backup Database
  run: |
    ssh -i private_key.pem ${USER}@${HOST} \
    'docker exec attendance_db mysqldump -u root -p${MYSQL_ROOT_PASSWORD} attendance_system > ~/backup_$(date +%Y%m%d_%H%M%S).sql'
```

### Deploy Only on Tag/Release

Change trigger in workflow:
```yaml
on:
  push:
    tags:
      - 'v*'
```

Then deploy with:
```bash
git tag v1.0.0
git push origin v1.0.0
```

---

## 🎓 Best Practices

1. ✅ **Test locally first**: `docker-compose up -d`
2. ✅ **Small commits**: Easier to debug if deployment fails
3. ✅ **Meaningful commit messages**: Shows in deployment logs
4. ✅ **Monitor logs**: Check Actions tab after each push
5. ✅ **Backup database**: Before major changes
6. ✅ **Use feature branches**: Merge to main when ready
7. ✅ **Tag releases**: Use semantic versioning (v1.0.0)

---

## 🆘 Quick Commands

### View Workflow Status
```bash
# List recent workflow runs
gh run list

# View specific run
gh run view <run-id>

# Watch live logs
gh run watch
```

### Manual Deployment (Bypass CI/CD)
```bash
ssh -i your-key.pem ubuntu@YOUR-EC2-IP
cd ~/attendance-app
git pull origin main
./ec2-deploy.sh
```

### Stop All Containers
```bash
ssh -i your-key.pem ubuntu@YOUR-EC2-IP
cd ~/attendance-app
docker-compose -f docker-compose.prod.yml down
```

---

## ✅ Verification Checklist

- [ ] EC2 instance running
- [ ] Docker installed on EC2
- [ ] `~/attendance-app` directory exists
- [ ] `.env` file configured on EC2
- [ ] GitHub secrets added (3 secrets)
- [ ] First push triggered deployment
- [ ] Actions tab shows successful run
- [ ] Application accessible at EC2 IP
- [ ] Can login to application
- [ ] Containers auto-restart on failure

---

## 🎉 Success!

Your CI/CD pipeline is now active! Every push to `main` will automatically deploy to EC2.

**What happens now**:
1. You write code locally
2. Commit and push to GitHub
3. GitHub Actions automatically deploys
4. Your changes are live in minutes!

**Next Steps**:
- Setup custom domain
- Add SSL certificate (HTTPS)
- Configure monitoring
- Setup staging environment
- Add automated tests

---

**Questions?** Check [DOCKER-QUICK-REFERENCE.md](DOCKER-QUICK-REFERENCE.md) or [EC2-DEPLOYMENT-GUIDE.md](EC2-DEPLOYMENT-GUIDE.md)
