<?php
require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: apply.php');
    exit;
}

$fullname = trim($_POST['fullname'] ?? '');
$email    = trim($_POST['email'] ?? '');
$dob      = $_POST['dob'] ?? null;
$phone    = trim($_POST['phone'] ?? '');
$address  = trim($_POST['address'] ?? '');
$reason   = trim($_POST['reason'] ?? '');
$type     = $_POST['type'] ?? null; // optional now

if ($fullname === '' || $email === '') {
    flash_set('error', 'Full name and email are required.');
    header('Location: apply.php');
    exit;
}

$email_lc = strtolower($email);

// Check if already a citizen
$chk = db()->prepare("SELECT id FROM citizens WHERE email = ? LIMIT 1");
$chk->execute([$email_lc]);
if ($chk->fetch()) {
    flash_set('error', 'Our records show you are already a citizen. If this is a mistake, contact support.');
    header('Location: apply.php'); exit;
}

// Check for existing application status
$appChk = db()->prepare("SELECT status FROM citizenship_applications WHERE email = ? ORDER BY id DESC LIMIT 1");
$appChk->execute([$email_lc]);
if ($row = $appChk->fetch()) {
    $st = $row['status'];
    if (in_array($st, ['pending','under_review'], true)) {
        flash_set('error', 'An application using this email is already under review or pending.');
        header('Location: apply.php'); exit;
    }
    if ($st === 'approved') {
        flash_set('success', 'An application using this email has already been approved. You can log in.');
        header('Location: login.php'); exit;
    }
}

$photo_path = null;
if (!empty($_FILES['photo']['name'])) {
    $photo = $_FILES['photo'];
    if ($photo['error'] === UPLOAD_ERR_OK) {
        if ($photo['size'] > QAMAR_MAX_PHOTO_BYTES) {
            flash_set('error', 'Photo exceeds size limit (2MB).');
            header('Location: apply.php'); exit;
        }
        $ext = strtolower(pathinfo($photo['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
            flash_set('error', 'Unsupported photo format.');
            header('Location: apply.php'); exit;
        }
        $fname = 'citizen_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dest = QAMAR_UPLOAD_DIR . DIRECTORY_SEPARATOR . $fname;
        if (!move_uploaded_file($photo['tmp_name'], $dest)) {
            flash_set('error', 'Failed to save photo.');
            header('Location: apply.php'); exit;
        }
        $photo_path = 'uploads/' . $fname;
    }
}

// Ensure column for desired password exists (best-effort, harmless if already present)
try {
    db()->exec("ALTER TABLE citizenship_applications ADD COLUMN IF NOT EXISTS desired_password_hash VARCHAR(255) DEFAULT NULL;");
} catch (Throwable $e) {
    // Some MySQL versions don't support IF NOT EXISTS for ALTER COLUMN; ignore failures here
}

// Best-effort: ensure citizens table has motivation column so admin approval can copy it
try {
    db()->exec("ALTER TABLE citizens ADD COLUMN IF NOT EXISTS motivation TEXT DEFAULT NULL;");
} catch (Throwable $__e) {
    // ignore
}

$desired_password_hash = null;
if (!empty($_POST['password'])) {
    $pw = $_POST['password'];
    if (strlen($pw) >= 8) {
        $desired_password_hash = password_hash($pw, PASSWORD_BCRYPT);
    } else {
        flash_set('error', 'Password must be at least 8 characters if provided.');
        header('Location: apply.php'); exit;
    }
}

$sql = "INSERT INTO citizenship_applications
    (fullname,email,photo_path,dob,phone,address,motivation,application_type,desired_password_hash)
    VALUES (:fullname,:email,:photo_path,:dob,:phone,:address,:motivation,:application_type,:desired_password_hash)";
$stmt = db()->prepare($sql);
$stmt->execute([
    ':fullname' => $fullname,
    ':email' => $email,
    ':photo_path' => $photo_path,
    ':dob' => $dob ?: null,
    ':phone' => $phone ?: null,
    ':address' => $address ?: null,
    ':motivation' => $reason ?: null,
    ':application_type' => $type ?: null,
    ':desired_password_hash' => $desired_password_hash,
]);

flash_set('success', 'Application submitted successfully.');
header('Location: apply.php');
