<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';

$jobId = filter_input(INPUT_GET, 'job_id', FILTER_VALIDATE_INT);
if (!$jobId) {
    http_response_code(400);
    exit('معرف الوظيفة غير صالح.');
}

$stmt = $pdo->prepare('SELECT id, title, company FROM jobs WHERE id = :id AND is_active = 1 LIMIT 1');
$stmt->execute(['id' => $jobId]);
$job = $stmt->fetch();

if (!$job) {
    http_response_code(404);
    exit('الوظيفة غير موجودة.');
}
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>التقديم على الوظيفة</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <main class="container narrow">
        <h1>التقديم على: <?= e($job['title']) ?></h1>
        <p><?= e($job['company']) ?></p>

        <form action="submit_application.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="job_id" value="<?= (int) $job['id'] ?>">

            <label for="applicant_name">الاسم الكامل</label>
            <input id="applicant_name" name="applicant_name" type="text" minlength="2" maxlength="120" required>

            <label for="email">البريد الإلكتروني</label>
            <input id="email" name="email" type="email" maxlength="190" required>

            <label for="phone">رقم الهاتف</label>
            <input id="phone" name="phone" type="tel" maxlength="30">

            <label for="cover_message">رسالة مختصرة</label>
            <textarea id="cover_message" name="cover_message" rows="6"></textarea>

            <label for="cv">السيرة الذاتية (PDF أو DOC أو DOCX بحد أقصى 5 MB)</label>
            <input id="cv" name="cv" type="file" accept=".pdf,.doc,.docx" required>

            <button class="button" type="submit">إرسال الطلب</button>
        </form>
    </main>
</body>
</html>