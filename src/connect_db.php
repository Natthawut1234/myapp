<?php
// connect_db.php (PDO version)
$host = 'mysql';
$db   = 'myappdb';
$user = 'myapp';
$pass = 'myapp1234';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => true,
];
$maxTries = 10;
$tries = 0;
while (true) {
    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
        break;
    } catch (PDOException $e) {
        $tries++;
        if ($tries >= $maxTries) {
            die("Database connection failed after $tries attempts: " . $e->getMessage());
        }
        sleep(2);
    }
}