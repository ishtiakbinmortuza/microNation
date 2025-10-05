<?php
require __DIR__ . '/config.php';
if (!is_logged_in()) { header('Location: login.php'); exit; }
$user = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard - Qamarshan</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    .dash-wrap { max-width: 1100px; margin: 6rem auto 3rem; padding: 2rem; }
    .dash-grid { display:grid; gap:1.5rem; grid-template-columns: repeat(auto-fit,minmax(260px,1fr)); }
    .dash-card { background: rgba(255,255,255,0.9); border-radius:18px; padding:1.5rem 1.25rem; box-shadow:0 10px 30px rgba(0,0,0,.08); border:1px solid rgba(255,255,255,0.4); }
    .dash-card h3 { margin:0 0 .6rem; font-size:1.05rem; }
    .status-pill { display:inline-block; padding:.25rem .6rem; border-radius:999px; font-size:.7rem; letter-spacing:.5px; background:linear-gradient(135deg,#48bb78,#38a169); color:#fff; }
    table.small { width:100%; border-collapse:collapse; font-size:.82rem; }
    table.small th, table.small td { padding:.4rem .55rem; text-align:left; }
    table.small tbody tr { background:rgba(255,255,255,0.6); }
    table.small thead { background:rgba(72,187,120,0.15); }
  </style>
</head>
<body>
<?php include __DIR__ . '/inc_nav.php'; ?>
<div class="dash-wrap">
  <h1 style="margin-top:0;">Dashboard</h1>
  <p>Welcome, <?php echo sanitize($user['username']); ?> (role: <?php echo sanitize($user['role']); ?>)</p>
  <?php if ($msg = flash_get('success')): ?><div style="margin:1rem 0; padding:.75rem 1rem; background:rgba(72,187,120,.15); border:1px solid rgba(72,187,120,.4); border-radius:10px; color:#22543d; font-weight:600; "><?php echo sanitize($msg); ?></div><?php endif; ?>
  <div class="dash-grid">
    <div class="dash-card">
      <h3>Recent Applications</h3>
      <?php
        $apps = db()->query("SELECT id, fullname, email, application_type, status, submitted_at FROM citizenship_applications ORDER BY id DESC LIMIT 5")->fetchAll();
        if (!$apps) echo '<p style="opacity:.6;">None yet.</p>'; else {
          echo '<table class="small"><thead><tr><th>Name</th><th>Type</th><th>Status</th></tr></thead><tbody>';
          foreach ($apps as $a) {
            echo '<tr><td>'.sanitize($a['fullname']).'</td><td>'.sanitize($a['application_type'] ?? '-').'</td><td>'.sanitize($a['status']).'</td></tr>';
          }
          echo '</tbody></table>';
        }
      ?>
    </div>
    <div class="dash-card">
      <h3>Diplomatic Messages</h3>
      <?php
        $dips = db()->query("SELECT id, state_name, category, status, submitted_at FROM diplomatic_messages ORDER BY id DESC LIMIT 5")->fetchAll();
        if (!$dips) echo '<p style="opacity:.6;">None yet.</p>'; else {
          echo '<table class="small"><thead><tr><th>State</th><th>Type</th><th>Status</th></tr></thead><tbody>';
          foreach ($dips as $d) {
            echo '<tr><td>'.sanitize($d['state_name']).'</td><td>'.sanitize($d['category']).'</td><td>'.sanitize($d['status']).'</td></tr>';
          }
          echo '</tbody></table>';
        }
      ?>
    </div>
    <div class="dash-card">
      <h3>Your Account</h3>
      <p><strong>User:</strong> <?php echo sanitize($user['username']); ?><br>
         <strong>Email:</strong> <?php echo sanitize($user['email']); ?><br>
         <strong>Role:</strong> <?php echo sanitize($user['role']); ?></p>
      <p style="margin:.7rem 0 0;"><a href="logout.php" class="form-link-primary">Log out</a></p>
    </div>
  </div>
</div>
<footer>
  <p>&copy; 2025 Mashaykhat Qamarshan | <a href="https://micronations.wiki/wiki/Qamarshan">Learn More</a></p>
</footer>
</body>
</html>