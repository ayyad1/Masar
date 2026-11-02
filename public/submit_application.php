<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('الطريقة غير مسموحة.');
}

$jobId   = filter_input(INPUT_POST, 'job_id', FILTER_VALIDATE_INT);
$name    = trim((string) ($_POST['applicant_name'] ?? ''));
$email   = trim((string) ($_POST['email'] ?? ''));
$phone   = trim((string) ($_POST['phone'] ?? ''));
$message = trim((string) ($_POST['cover_message'] ?? ''));
$errors  = [];

if (!$jobId) {
    $errors[] = 'الوظيفة غير صالحة.';
}
if (mb_strlen($name) < 2 || mb_strlen($name) > 120) {
    $errors[] = 'الاسم يجب أن يكون بين حرفين و 120 حرفًا.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'البريد الإلكتروني غير صالح.';
}
if (mb_strlen($phone) > 30) {
    $errors[] = 'رقم الهاتف طويل جدًا.';
}

$allowedExtensions = ['pdf', 'doc', 'docx'];
$maxSize = 5 * 1024 * 1024;
$extension = '';

if (!isset($_FILES['cv']) || $_FILES['cv']['error'] !== UPLOAD_ERR_OK) {
    $errors[] = 'يرجى رفع ملف CV صالح.';
} else {
    $originalName = (string) $_FILES['cv']['name'];
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $size = (int) $_FILES['cv']['size'];

    if (!in_array($extension, $allowedExtensions, true)) {
        $errors[] = 'نوع الملف غير مسموح.';
    }
    if ($size > $maxSize) {
        $errors[] = 'حجم الملف يتجاوز 5 MB.';
    }
}

if ($errors) {
    http_response_code(422);
    echo '<h1>تعذر إرسال الطلب</h1><ul>';
    foreach ($errors as $error) {
        echo '<li>' . e($error) . '</li>';
    }
    echo '</ul><a href="javascript:history.back()">العودة للنموذج</a>';
    exit;
}

$jobStmt = $pdo->prepare('SELECT id FROM jobs WHERE id = :id AND is_active = 1 LIMIT 1');
$jobStmt->execute(['id' => $jobId]);
if (!$jobStmt->fetch()) {
    http_response_code(404);
    exit('الوظيفة غير موجودة أو غير نشطة.');
}

$storageDir = dirname(__DIR__) . '/storage/cvs';
if (!is_dir($storageDir) && !mkdir($storageDir, 0750, true)) {
    http_response_code(500);
    exit('تعذر تجهيز مساحة تخزين الملفات.');
}

$storedFilename = bin2hex(random_bytes(16)) . '.' . $extension;
$destination = $storageDir . '/' . $storedFilename;

if (!move_uploaded_file($_FILES['cv']['tmp_name'], $destination)) {
    http_response_code(500);
    exit('تعذر حفظ ملف CV.');
}

try {
    $insert = $pdo->prepare(
        'INSERT INTO applications (job_id, applicant_name, email, phone, cover_message, cv_filename)
         VALUES (:job_id, :name, :email, :phone, :message, :cv_filename)'
    );
    $insert->execute([
        'job_id'      => $jobId,
        'name'        => $name,
        'email'       => $email,
        'phone'       => $phone !== '' ? $phone : null,
        'message'     => $message !== '' ? $message : null,
        'cv_filename' => $storedFilename,
    ]);
    redirect('success.php');
} catch (Throwable $e) {
    if (is_file($destination)) {
        unlink($destination);
    }
    error_log($e->getMessage());
    http_response_code(500);
    exit('حدث خطأ أثناء حفظ الطلب.');
}