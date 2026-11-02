<?php
declare(strict_types=1);
require_once 'C:\xampp\htdocs\mini-job-board\config\database.php';
require_once __DIR__ . '/helpers.php';

$stmt = $pdo->query(
    'SELECT id, title, company, location, employment_type, description 
     FROM jobs 
     WHERE is_active = 1 
     ORDER BY created_at DESC'
);
$jobs = $stmt->fetchAll();
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>الوظائف المتاحة</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <h1>منصة فرص</h1>
            <p>وظائف وتدريب تعاوني في مكان واحد</p>
        </div>
    </header>
    <main class="container">
        <h2>الوظائف المتاحة</h2>
        <?php if (!$jobs): ?>
            <div class="alert">لا توجد وظائف منشورة حاليًا.</div>
        <?php else: ?>
            <section class="jobs-grid" aria-label="قائمة الوظائف">
                <?php foreach ($jobs as $job): ?>
                    <article class="job-card">
                        <h3><?= e($job['title']) ?></h3>
                        <p><strong>الشركة:</strong> <?= e($job['company']) ?></p>
                        <p><strong>الموقع:</strong> <?= e($job['location']) ?></p>
                        <p><?= e(mb_substr($job['description'], 0, 140)) ?>...</p>
                        <a class="button" href="job.php?id=<?= (int) $job['id'] ?>">عرض التفاصيل</a>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>