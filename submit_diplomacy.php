<?php
require __DIR__ . '/config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: diplomacy.php'); exit; }

$state_name = trim($_POST['state_name'] ?? '');
$contact_person = trim($_POST['contact_person'] ?? '');
$email = trim($_POST['email'] ?? '');
$category = $_POST['category'] ?? '';
$message = trim($_POST['message'] ?? '');

if ($state_name === '' || $contact_person === '' || $email === '' || $category === '' || $message === '') {
    flash_set('error', 'All required fields must be filled.');
    header('Location: diplomacy.php'); exit;
}

$attachment_path = null;
if (!empty($_FILES['attachment']['name'])) {
    $att = $_FILES['attachment'];
    if ($att['error'] === UPLOAD_ERR_OK) {
        if ($att['size'] > QAMAR_MAX_ATTACHMENT_BYTES) {
            flash_set('error', 'Attachment too large (max 5MB).');
            header('Location: diplomacy.php'); exit;
        }
        $ext = strtolower(pathinfo($att['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['pdf','jpg','jpeg','png','gif','webp','doc','docx','txt'])) {
            flash_set('error', 'Unsupported attachment type.');
            header('Location: diplomacy.php'); exit;
        }
        $fname = 'diplomacy_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dest = QAMAR_UPLOAD_DIR . DIRECTORY_SEPARATOR . $fname;
        if (!move_uploaded_file($att['tmp_name'], $dest)) {
            flash_set('error', 'Failed to save attachment.');
            header('Location: diplomacy.php'); exit;
        }
        $attachment_path = 'uploads/' . $fname;
    }
}

$stmt = db()->prepare("INSERT INTO diplomatic_messages
(state_name,contact_person,email,category,message_body,attachment_path)
VALUES (:state_name,:contact_person,:email,:category,:message_body,:attachment_path)");
$stmt->execute([
    ':state_name' => $state_name,
    ':contact_person' => $contact_person,
    ':email' => $email,
    ':category' => $category,
    ':message_body' => $message,
    ':attachment_path' => $attachment_path,
]);

flash_set('success', 'Diplomatic communication sent.');
header('Location: diplomacy.php');
