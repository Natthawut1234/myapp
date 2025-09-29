# Jenkins CI/CD Setup Guide สำหรับ MyApp2

## 📋 ข้อกำหนดเบื้องต้น

### 1. ติดตั้ง Jenkins
```powershell
# ดาวน์โหลด Jenkins WAR file
wget https://get.jenkins.io/war-stable/latest/jenkins.war

# หรือติดตั้งผ่าน Chocolatey
choco install jenkins

# รัน Jenkins
java -jar jenkins.war --httpPort=8081
```

### 2. ติดตั้ง Plugins ที่จำเป็น
เข้าไปที่ Jenkins Dashboard → Manage Jenkins → Manage Plugins
ติดตั้ง plugins ต่อไปนี้:
- Git plugin
- Docker Pipeline plugin
- Pipeline plugin
- Blue Ocean (optional - สำหรับ UI ที่สวยงาม)

### 3. ติดตั้ง Docker บน Jenkins Server
```powershell
# ติดตั้ง Docker Desktop สำหรับ Windows
# ดาวน์โหลดจาก https://docker.com/products/docker-desktop

# ตรวจสอบการติดตั้ง
docker --version
docker-compose --version
```

## 🚀 วิธีการตั้งค่า Jenkins Pipeline

### Step 1: สร้าง New Job
1. เข้าไปที่ Jenkins Dashboard
2. คลิก "New Item"
3. ใส่ชื่อ job: `myapp2-pipeline`
4. เลือก "Pipeline"
5. คลิก "OK"

### Step 2: กำหนดค่า Pipeline
1. ในหน้า Configuration ของ job
2. เลื่อนลงไปที่ส่วน "Pipeline"
3. เลือก "Pipeline script from SCM"
4. เลือก SCM: "Git"
5. ใส่ Repository URL: `https://github.com/Natthawut1234/myapp.git`
6. Branch: `main`
7. Script Path: `Jenkinsfile`

### Step 3: สร้าง Webhook (Optional)
สำหรับ Auto-trigger เมื่อมี push ใหม่:

1. ไปที่ GitHub repository settings
2. คลิก "Webhooks"
3. คลิก "Add webhook"
4. Payload URL: `http://your-jenkins-server:8081/github-webhook/`
5. Content type: `application/json`
6. เลือก "Just the push event"

## ⚙️ การปรับแต่งสำหรับ Environment ต่างๆ

### สำหรับ Development Environment
```groovy
// เพิ่มใน Jenkinsfile
when {
    branch 'develop'
}
environment {
    PROJECT_NAME = "myapp2-dev"
    HTTP_PORT = "8000"
    MYSQL_PORT = "3307"
}
```

### สำหรับ Production Environment
```groovy
when {
    branch 'main'
}
environment {
    PROJECT_NAME = "myapp2-prod"
    HTTP_PORT = "80"
    MYSQL_PORT = "3306"
}
```

## 🔧 Jenkins Agent Configuration

### สำหรับ Windows Agent
1. Manage Jenkins → Manage Nodes and Clouds
2. New Node → ใส่ชื่อ node
3. เลือก "Permanent Agent"
4. กำหนด:
   - Remote root directory: `C:\jenkins-agent`
   - Labels: `windows docker`
   - Launch method: "Launch agent by connecting it to the master"

### การติดตั้ง Agent บน Windows
```powershell
# ดาวน์โหลด agent.jar จาก Jenkins
# รันคำสั่งที่ Jenkins แสดงให้
java -jar agent.jar -jnlpUrl http://jenkins-server:8081/computer/agent-name/slave-agent.jnlp -secret your-secret -workDir "C:\jenkins-agent"
```

## 📊 Monitoring และ Logging

### 1. ดู Pipeline Status
- เข้าไปที่ Jenkins Dashboard
- คลิกที่ job name
- ดู Build History

### 2. ดู Logs
```powershell
# ดู Docker logs
docker-compose -p myapp2 logs -f

# ดู Jenkins logs
Get-Content C:\jenkins\logs\jenkins.log -Wait
```

### 3. Health Check Commands
```powershell
# ตรวจสอบ containers
docker ps

# ตรวจสอบ network
docker network ls

# ตรวจสอบ volumes
docker volume ls
```

## 🛡️ Security Best Practices

### 1. Jenkins Security
- เปิดใช้ "Enable security"
- สร้าง user accounts
- กำหนด permissions อย่างเหมาะสม

### 2. Docker Security
```yaml
# เพิ่มใน docker-compose.yml
services:
  php-apache-environment:
    user: "1000:1000"
    read_only: true
    tmpfs:
      - /tmp:exec,size=100M
```

### 3. Environment Variables
```groovy
// ใช้ Jenkins Credentials แทนการ hardcode
environment {
    DB_PASSWORD = credentials('mysql-password')
    DOCKER_REGISTRY_CREDS = credentials('docker-registry')
}
```

## 🚨 Troubleshooting

### ปัญหาที่พบบ่อย:

1. **Docker permission denied**
   ```powershell
   # เพิ่ม Jenkins user เข้า Docker group (Linux)
   # หรือรัน Jenkins as Administrator (Windows)
   ```

2. **Port already in use**
   ```powershell
   # หา process ที่ใช้ port
   netstat -ano | findstr :80
   # Kill process
   taskkill /PID <process-id> /F
   ```

3. **Container startup timeout**
   ```groovy
   // เพิ่ม timeout ใน Jenkinsfile
   timeout(time: 10, unit: 'MINUTES') {
       // deployment commands
   }
   ```

## 📱 การแจ้งเตือน

### Slack Integration
```groovy
post {
    success {
        slackSend(
            channel: '#deployments',
            color: 'good',
            message: "✅ MyApp2 deployed successfully!"
        )
    }
    failure {
        slackSend(
            channel: '#deployments',
            color: 'danger',
            message: "❌ MyApp2 deployment failed!"
        )
    }
}
```

### Email Notifications
```groovy
post {
    failure {
        emailext(
            subject: "Build Failed: ${env.JOB_NAME} - ${env.BUILD_NUMBER}",
            body: "Build failed. Check console output at ${env.BUILD_URL}",
            to: "admin@company.com"
        )
    }
}
```

## 🎯 คำสั่งที่มีประโยชน์

```powershell
# Manual deployment
docker-compose -p myapp2 up -d

# Stop services
docker-compose -p myapp2 down

# View logs
docker-compose -p myapp2 logs -f

# Rebuild without cache
docker-compose -p myapp2 build --no-cache

# Remove unused images
docker image prune -f

# Remove unused volumes
docker volume prune -f
```

## 🔄 Pipeline Workflow

1. **Checkout**: ดึง source code จาก Git
2. **Environment Check**: ตรวจสอบ Docker
3. **Cleanup**: ลบ containers เก่า
4. **Build**: Build Docker images
5. **Start Services**: เริ่ม containers
6. **Health Check**: ตรวจสอบ services
7. **Run Tests**: รัน tests
8. **Deploy Verification**: ยืนยันการ deploy

การตั้งค่านี้จะทำให้คุณมี CI/CD pipeline ที่สมบูรณ์สำหรับ PHP application ของคุณ!