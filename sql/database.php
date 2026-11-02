<?php
declare(strict_types=1);

$host = '127.0.0.1'; // هذا هو الخادم المحلي (يقابل localhost)
$db   = 'mini_job_board'; // اسم قاعدة البيانات التي أنشأتها
$user = 'root'; // اسم المستخدم الافتراضي في XAMPP
$pass = ''; // كلمة المرور فارغة افتراضياً
$charset = 'utf8mb4';

$dsn = "mysql:host={$host};dbname={$db};charset={$charset}";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit('حدث خطأ في الاتصال بالخادم.');
}