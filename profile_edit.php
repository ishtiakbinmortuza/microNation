<?php
require __DIR__ . '/config.php';
if (!is_logged_in()) { header('Location: login.php'); exit; }
$user = current_user();

// Load existing citizen or latest application
$citizen = null; $app = null;
try {
    $stmt = db()->prepare("SELECT * FROM citizens WHERE user_id = ? LIMIT 1");
    $stmt->execute([$user['id']]);
    $citizen = $stmt->fetch();
    if ($citizen) {
        if (!empty($citizen['application_id'])) {
            $s2 = db()->prepare("SELECT * FROM citizenship_applications WHERE id = ? LIMIT 1");
            $s2->execute([$citizen['application_id']]);
            $app = $s2->fetch();
        }
    } else {
        $s3 = db()->prepare("SELECT * FROM citizenship_applications WHERE email = ? ORDER BY id DESC LIMIT 1");
        $s3->execute([strtolower($user['email'])]);
        $app = $s3->fetch();
    }
} catch (Throwable $e) {}

// Handle POST update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $dob = $_POST['dob'] ?? null;
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $motivation = trim($_POST['motivation'] ?? '');

    // Handle optional photo upload
    $photo_path = null;
    if (!empty($_FILES['photo']['name'])) {
        $photo = $_FILES['photo'];
        if ($photo['error'] === UPLOAD_ERR_OK) {
            if ($photo['size'] > QAMAR_MAX_PHOTO_BYTES) {
                flash_set('error','Photo exceeds size limit (2MB).');
                header('Location: profile_edit.php'); exit;
            }
            $ext = strtolower(pathinfo($photo['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
                flash_set('error','Unsupported photo format.');
                header('Location: profile_edit.php'); exit;
            }
            $fname = 'profile_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $dest = QAMAR_UPLOAD_DIR . DIRECTORY_SEPARATOR . $fname;
            if (!move_uploaded_file($photo['tmp_name'], $dest)) {
                flash_set('error','Failed to save photo.'); header('Location: profile_edit.php'); exit;
            }
            $photo_path = 'uploads/' . $fname;
        }
    }

    try {
        if ($citizen) {
            $sql = "UPDATE citizens SET fullname=?, dob=?, phone=?, address=?, motivation=?";
            $params = [$fullname ?: $citizen['fullname'], $dob ?: $citizen['dob'], $phone ?: $citizen['phone'], $address ?: $citizen['address'], $motivation ?: ($citizen['motivation'] ?? null)];
            if ($photo_path) { $sql .= ", photo_path=?"; $params[] = $photo_path; }
            $sql .= " WHERE id=?"; $params[] = $citizen['id'];
            db()->prepare($sql)->execute($params);
            flash_set('success','Profile updated.');
        } elseif ($app) {
            $sql = "UPDATE citizenship_applications SET fullname=?, dob=?, phone=?, address=?, motivation=?";
            $params = [$fullname ?: $app['fullname'], $dob ?: $app['dob'], $phone ?: $app['phone'], $address ?: $app['address'], $motivation ?: $app['motivation']];
            if ($photo_path) { $sql .= ", photo_path=?"; $params[] = $photo_path; }
            $sql .= " WHERE id=?"; $params[] = $app['id'];
            db()->prepare($sql)->execute($params);
            flash_set('success','Application updated.');
        } else {
            // No existing record — create a minimal application
            $sql = "INSERT INTO citizenship_applications (fullname,email,dob,phone,address,motivation,photo_path) VALUES (?,?,?,?,?,?,?)";
            db()->prepare($sql)->execute([$fullname ?: $user['username'], $user['email'], $dob ?: null, $phone ?: null, $address ?: null, $motivation ?: null, $photo_path]);
            flash_set('success','Application created.');
        }
    } catch (Throwable $e) {
        flash_set('error','Update failed: ' . $e->getMessage());
    }

    header('Location: profile.php'); exit;
}

// Prefill values
$pref = [];
if ($citizen) $pref = $citizen; elseif ($app) $pref = $app; else $pref = ['fullname'=>$user['username'],'email'=>$user['email']];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Edit Profile - Qamarshan</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    .edit-wrap { max-width:760px; margin:6rem auto 3rem; }
  </style>
</head>
<body>
<?php include __DIR__ . '/inc_nav.php'; ?>
<main class="edit-wrap">
  <div class="card">
    <h2>Edit Profile</h2>
    <?php if ($m = flash_get('error')): ?><div style="margin:1rem 0;padding:.75rem 1rem;background:rgba(229,62,62,.12);border:1px solid rgba(229,62,62,.4);border-radius:10px;color:#742a2a; font-weight:600; "><?php echo sanitize($m); ?></div><?php endif; ?>
    <form action="profile_edit.php" method="POST" enctype="multipart/form-data">
      <div class="form-group"><label for="fullname">Full name</label><input id="fullname" name="fullname" value="<?php echo sanitize($pref['fullname'] ?? ''); ?>"></div>
      <div class="form-group"><label for="dob">Date of birth</label><input id="dob" name="dob" type="date" value="<?php echo sanitize($pref['dob'] ?? ''); ?>"></div>
      <div class="form-group"><label for="phone">Phone</label><input id="phone" name="phone" value="<?php echo sanitize($pref['phone'] ?? ''); ?>"></div>
      <div class="form-group"><label for="address">Address</label><textarea id="address" name="address"><?php echo sanitize($pref['address'] ?? ''); ?></textarea></div>
      <div class="form-group"><label for="motivation">Motivation / Reason</label><textarea id="motivation" name="motivation"><?php echo sanitize($pref['motivation'] ?? ''); ?></textarea></div>
      <div class="form-group"><label for="photo">Upload photo (optional)</label><input id="photo" name="photo" type="file" accept="image/*"></div>
      <button class="form-btn" type="submit">Save changes</button>
      <a href="profile.php" class="form-link" style="margin-left:1rem;">Cancel</a>
    </form>
  </div>
</main>
</body>
</html>
