pipeline {
    agent any

    environment {
        COMPOSE_FILE = "docker-compose.yml"
        PROJECT_NAME = "myapp2"
        DOCKER_REGISTRY = "natthawut1234/myapp" // เปลี่ยนเป็น registry ของคุณ
        GIT_REPO = "https://github.com/Natthawut1234/myapp.git" // URL ของ repo จริง
    }

    stages {
        stage('Checkout') {
            steps {
                echo 'Checking out source code...'
                git branch: 'main', url: "${GIT_REPO}"
            }
        }

        stage('Environment Check') {
            steps {
                script {
                    echo 'Checking Docker and Docker Compose versions...'
                    bat "docker --version"
                    bat "docker-compose --version"
                    bat "docker ps"
                }
            }
        }

        stage('Cleanup Previous Deployment') {
            steps {
                script {
                    echo 'Stopping and removing old containers...'
                    // ใช้ || exit 0 เพื่อไม่ให้ pipeline fail ถ้าไม่มี container ที่กำลังรัน
                    bat "docker-compose -p %PROJECT_NAME% -f %COMPOSE_FILE% down || exit 0"
                    
                    // ลบ dangling images
                    bat "docker image prune -f || exit 0"
                }
            }
        }

        stage('Build Services') {
            steps {
                script {
                    echo 'Building Docker images...'
                    bat "docker-compose -p %PROJECT_NAME% -f %COMPOSE_FILE% build --no-cache"
                }
            }
        }

        stage('Start Services') {
            steps {
                script {
                    echo 'Starting services...'
                    bat "docker-compose -p %PROJECT_NAME% -f %COMPOSE_FILE% up -d"
                    
                    // รอให้ services พร้อม
                    echo 'Waiting for services to be ready...'
                    bat "timeout /t 30"
                    
                    // ตรวจสอบ container status
                    bat "docker-compose -p %PROJECT_NAME% -f %COMPOSE_FILE% ps"
                }
            }
        }

        stage('Health Check') {
            steps {
                script {
                    echo 'Performing health checks...'
                    
                    // ตรวจสอบว่า MySQL พร้อมหรือยัง
                    bat """
                        timeout /t 60
                        docker exec mysql mysqladmin ping -h localhost -u root -pmy-secret-pw
                    """
                    
                    // ตรวจสอบว่า Apache พร้อมหรือยัง
                    powershell """
                        \$response = Invoke-WebRequest -Uri "http://localhost" -UseBasicParsing -TimeoutSec 30
                        if (\$response.StatusCode -eq 200) {
                            Write-Host "Web server is responding successfully"
                        } else {
                            throw "Web server health check failed"
                        }
                    """
                }
            }
        }

        stage('Run Tests') {
            steps {
                script {
                    echo 'Running application tests...'
                    
                    // Basic connectivity tests
                    bat "docker exec php-apache php -v"
                    
                    // Test database connection
                    bat '''
                        docker exec php-apache php -r "
                        try {
                            \$pdo = new PDO('mysql:host=mysql;dbname=myappdb', 'myapp', 'myapp1234');
                            echo 'Database connection successful!';
                        } catch (PDOException \$e) {
                            echo 'Database connection failed: ' . \$e->getMessage();
                            exit(1);
                        }
                        "
                    '''
                    
                    // Test web endpoint
                    powershell """
                        try {
                            \$response = Invoke-WebRequest -Uri "http://localhost/products.php" -UseBasicParsing
                            Write-Host "Products page accessible: " \$response.StatusCode
                        } catch {
                            Write-Warning "Products page test failed: " \$_.Exception.Message
                        }
                    """
                }
            }
        }

        stage('Deploy Verification') {
            steps {
                script {
                    echo 'Verifying deployment...'
                    bat "docker-compose -p %PROJECT_NAME% -f %COMPOSE_FILE% ps"
                    
                    // แสดง logs ของ containers
                    bat "docker-compose -p %PROJECT_NAME% -f %COMPOSE_FILE% logs --tail=20"
                    
                    echo 'Application deployed successfully!'
                    echo 'Access your application at: http://localhost'
                    echo 'phpMyAdmin available at: http://localhost:8080'
                }
            }
        }
    }

    post {
        always {
            echo 'Pipeline completed'
            // บันทึก logs
            bat "docker-compose -p %PROJECT_NAME% -f %COMPOSE_FILE% logs > deployment.log || exit 0"
            
            // Archive logs as artifacts
            archiveArtifacts artifacts: 'deployment.log', fingerprint: true, allowEmptyArchive: true
        }
        
        success {
            echo 'Deployment succeeded!'
            // ส่งการแจ้งเตือนเมื่อสำเร็จ (ถ้าต้องการ)
        }
        
        failure {
            echo 'Deployment failed!'
            // แสดง container logs เมื่อมีปัญหา
            bat "docker-compose -p %PROJECT_NAME% -f %COMPOSE_FILE% logs || exit 0"
            
            // ทำความสะอาดเมื่อ deploy ล้มเหลว
            bat "docker-compose -p %PROJECT_NAME% -f %COMPOSE_FILE% down || exit 0"
        }
    }
}