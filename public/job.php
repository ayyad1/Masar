<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(400);
    exit('معرف الوظيفة غير صالح.');
}

$stmt = $pdo->prepare('SELECT * FROM jobs WHERE id = :id AND is_active = 1 LIMIT 1');
$stmt->execute(['id' => $id]);
$job = $stmt->fetch();

if (!$job) {
    http_response_code(404);
    exit('الوظيفة غير موجودة أو غير منشورة.');
}
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($job['title']) ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <main class="container narrow">
        <a href="index.php">&larr; العودة إلى الوظائف</a>
        <article class="job-details">
            <h1><?= e($job['title']) ?></h1>
            <p><strong>الشركة:</strong> <?= e($job['company']) ?></p>
            <p><strong>الموقع:</strong> <?= e($job['location']) ?></p>
            <p><strong>نوع العمل:</strong> <?= e($job['employment_type']) ?></p>
            
            <h2>الوصف</h2>
            <p><?= nl2br(e($job['description'])) ?></p>
            
            <h2>المتطلبات</h2>
            <p><?= nl2br(e($job['requirements'])) ?></p>
            
            <a class="button" href="apply.php?job_id=<?= (int) $job['id'] ?>">قدم الآن</a>
        </article>
    </main>
</body>
</html>