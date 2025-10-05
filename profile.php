<?php
require __DIR__ . '/config.php';
if (!is_logged_in()) { header('Location: login.php'); exit; }
$user = current_user();

// Fetch citizen record or latest application for this user
$citizen = null;
$app = null;
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
} catch (Throwable $e) {
  // ignore DB errors for profile display
}

function fmt($v) {
  if ($v === null || $v === '') return '<span style="opacity:.6;">&mdash;</span>';
  return nl2br(sanitize($v));
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'])) {
  $new = $_POST['new_password'] ?? '';
  $confirm = $_POST['confirm_password'] ?? '';
  if ($new !== $confirm) {
    flash_set('error','Passwords do not match.');
    header('Location: profile.php'); exit;
  }
  if (strlen($new) < 8) {
    flash_set('error','Password must be at least 8 characters.');
    header('Location: profile.php'); exit;
  }
  $hash = password_hash($new, PASSWORD_BCRYPT);
  $stmt = db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
  $stmt->execute([$hash, $user['id']]);
  flash_set('success','Password updated.');
  header('Location: profile.php'); exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Profile - Qamarshan</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    .profile-wrap { max-width:980px; margin:6rem auto 3rem; padding:1.5rem; }
    .profile-grid { display:grid; grid-template-columns: 320px 1fr; gap:1.5rem; align-items:start; }
    .profile-card { padding:1.5rem; border-radius:12px; background:rgba(255,255,255,0.95); }
    .profile-header { text-align:center; padding-bottom: .5rem; }
    .avatar { width:110px; height:110px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:2.25rem; font-weight:700; color:#fff; background:linear-gradient(135deg,#48bb78,#38a169); box-shadow:0 8px 30px rgba(56,161,105,.18); margin-bottom:.75rem; }
  .username { display:block; font-size:1.35rem; font-weight:800; margin-bottom:.25rem; background: linear-gradient(135deg,#48bb78,#38a169); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; }
    .pill { display:inline-block; padding:.3rem .6rem; border-radius:999px; background:rgba(72,187,120,.12); color:#22543d; font-weight:700; font-size:.85rem; }
    .muted { color:#4a5568; }
    @media (max-width:900px){ .profile-grid{grid-template-columns:1fr; } .avatar{width:88px;height:88px;font-size:1.6rem;} }
  </style>
</head>
<body>
<?php include __DIR__ . '/inc_nav.php'; ?>
<div class="profile-wrap">
  <div class="profile-wrap">
    <?php if ($m = flash_get('success')): ?><div style="margin:0 0 1rem; padding:.75rem 1rem; background:rgba(72,187,120,.15); border:1px solid rgba(72,187,120,.4); border-radius:10px; color:#22543d; font-weight:600; "><?php echo sanitize($m); ?></div><?php endif; ?>
    <?php if ($m = flash_get('error')): ?><div style="margin:0 0 1rem; padding:.75rem 1rem; background:rgba(229,62,62,.12); border:1px solid rgba(229,62,62,.4); border-radius:10px; color:#742a2a; font-weight:600; "><?php echo sanitize($m); ?></div><?php endif; ?>

    <div class="profile-grid">
      <div class="profile-card card" aria-labelledby="profile-heading">
      <div class="profile-header">
        <?php if (!empty($app['photo_path']) || !empty($citizen['photo_path'])): ?>
          <?php $photo = $citizen['photo_path'] ?? $app['photo_path']; ?>
          <div class="avatar photo-frame" role="img" aria-label="Profile photo">
            <img src="<?php echo sanitize($photo); ?>" alt="Profile photo" style="width:100%;height:100%;object-fit:cover;border-radius:50%;" />
          </div>
        <?php else: ?>
          <div class="avatar" aria-hidden="true"><?php echo strtoupper(substr($user['username'] ?? 'U',0,1)); ?></div>
        <?php endif; ?>
        <span class="username"><?php echo sanitize($user['username']); ?></span>
        <div class="muted"><?php echo sanitize($user['email']); ?></div>
        </div>
          <?php if (is_logged_in()): ?>
            <div style="margin-top:.6rem; text-align:center;"><span class="pill"><a class="btn-small" href="profile_edit.php">Edit profile</a></span></div>
          <?php endif; ?>
          <p style="margin:0;">Welcome to your profile. Below are the details we have on record from your application or citizen record.</p>
      </div>

      <div class="card">
        <h3 id="profile-heading" style="margin-top:0;">Citizen Details</h3>
        <?php if ($citizen || $app):
              $src = $citizen ?: $app;
        ?>
          <table style="width:100%;border-collapse:collapse;font-size:.95rem;">
            <tbody>
              <tr><td style="width:36%;padding:.4rem .6rem;opacity:.85;font-weight:700;">Full name</td><td style="padding:.4rem .6rem;"><?php echo fmt($src['fullname'] ?? ($user['username'] ?? '')); ?></td></tr>
              <tr><td style="padding:.4rem .6rem;font-weight:700;">Email</td><td style="padding:.4rem .6rem;"><?php echo fmt($src['email'] ?? $user['email']); ?></td></tr>
              <tr><td style="padding:.4rem .6rem;font-weight:700;">Phone</td><td style="padding:.4rem .6rem;"><?php echo fmt($src['phone'] ?? ''); ?></td></tr>
              <tr><td style="padding:.4rem .6rem;font-weight:700;">Date of birth</td><td style="padding:.4rem .6rem;"><?php echo fmt($src['dob'] ?? ''); ?></td></tr>
              <tr><td style="padding:.4rem .6rem;font-weight:700;">Address</td><td style="padding:.4rem .6rem;"><?php echo fmt($src['address'] ?? ''); ?></td></tr>
              <tr><td style="padding:.4rem .6rem;font-weight:700;">Motivation / Reason</td><td style="padding:.4rem .6rem;"><?php echo fmt($src['motivation'] ?? ($src['decision_notes'] ?? '')); ?></td></tr>
              <tr><td style="padding:.4rem .6rem;font-weight:700;">Application Type</td><td style="padding:.4rem .6rem;"><?php echo fmt($src['application_type'] ?? $src['citizenship_type'] ?? ''); ?></td></tr>
              <tr><td style="padding:.4rem .6rem;font-weight:700;">Status</td><td style="padding:.4rem .6rem;"><?php echo fmt($src['status'] ?? $src['active_status'] ?? ''); ?></td></tr>
              <tr><td style="padding:.4rem .6rem;font-weight:700;">Submitted / Granted</td><td style="padding:.4rem .6rem;"><?php echo fmt($src['submitted_at'] ?? $src['granted_at'] ?? ''); ?></td></tr>
            </tbody>
          </table>
        <?php else: ?>
          <p style="opacity:.7;">No application or citizen record found linked to this account.</p>
        <?php endif; ?>
        <div style="margin-top:1rem;">
          <a href="logout.php" class="form-link">Log out</a>
        </div>
      </div>
    </div>

    <!-- Passport Card -->
    <div class="passport-card">
      <div class="passport-bg"></div>
      <div class="passport-content">
        <div class="passport-photo">
          <img src="<?php
            $photo = $citizen['photo_path'] ?? $app['photo_path'] ?? 'default_profile.jpg';
            echo sanitize($photo);
          ?>" alt="Profile Photo">
        </div>
        <div class="passport-info">
          <h2>Mashaykhat Qamarshan</h2>
          <h3>Passport</h3>
          <table>
            <tr><td>Full Name:</td><td><?php echo fmt($citizen['fullname'] ?? $app['fullname'] ?? ''); ?></td></tr>
            <tr><td>Nationality:</td><td>Qamarshani</td></tr>
            <tr><td>Date of Birth:</td><td>2025-10-23</td></tr>
            <?php
              $issue = $citizen['granted_at'] ?? $citizen['approved_at'] ?? $app['granted_at'] ?? $app['approved_at'] ?? '';
              $expiry = '';
              if ($issue) {
                $dt = date_create($issue);
                if ($dt) {
                  $expiry = date_format(date_add($dt, date_interval_create_from_date_string('10 years')), 'Y-m-d');
                  $issue = date_format($dt, 'Y-m-d');
                }
              }
            ?>
            <tr><td>Date of Issue:</td><td><?php echo fmt($issue); ?></td></tr>
            <tr><td>Date of Expiry:</td><td><?php echo fmt($expiry); ?></td></tr>
          </table>
        </div>
      </div>
    </div>
    <div style="max-width:640px;margin:1.5rem auto;">
      <div class="card">
        <h3 style="margin-top:0;">Change password</h3>
        <form method="POST" style="max-width:480px;margin-top:.5rem;">
          <div class="form-group">
            <label for="new_password">New password</label>
            <input id="new_password" name="new_password" type="password" class="form-control" required>
          </div>
          <div class="form-group">
            <label for="confirm_password">Confirm password</label>
            <input id="confirm_password" name="confirm_password" type="password" class="form-control" required>
          </div>
          <button class="form-btn" type="submit">Update password</button>
        </form>
      </div>
    </div>