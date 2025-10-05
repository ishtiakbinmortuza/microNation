<?php
require __DIR__ . '/config.php';
require_admin();
$pdo = db();

// Handle POST actions
$action = $_POST['action'] ?? null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action) {
    try {
    // Best-effort: add motivation column to citizens if missing
    try {
      $pdo->exec("ALTER TABLE citizens ADD COLUMN IF NOT EXISTS motivation TEXT DEFAULT NULL;");
    } catch (Throwable $__e) {
      // ignore — some MySQL versions don't support IF NOT EXISTS on ALTER
    }
        if ($action === 'app_approve') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare("SELECT * FROM citizenship_applications WHERE id=? FOR UPDATE");
            $pdo->beginTransaction();
            $stmt->execute([$id]);
            $app = $stmt->fetch();
            if (!$app) throw new RuntimeException('Application not found');
            if ($app['status'] !== 'approved') {
                $pdo->prepare("UPDATE citizenship_applications SET status='approved', reviewed_by=?, reviewed_at=NOW(), decision_notes='Approved via admin_manage' WHERE id=?")
                    ->execute([current_user()['id'], $id]);
        // Create citizen if not existing
        $check = $pdo->prepare("SELECT id FROM citizens WHERE application_id=?");
        $check->execute([$id]);
        if (!$check->fetch()) {
          // If applicant provided a desired password hash, create or update a users account
          $userId = null;
          $desiredHash = $app['desired_password_hash'] ?? null;
          if (!empty($desiredHash)) {
            // Try to find existing user by email
            $u = $pdo->prepare("SELECT id, role FROM users WHERE email = ? LIMIT 1");
            $u->execute([strtolower($app['email'])]);
            $existing = $u->fetch();
            if ($existing) {
              // If existing user is not an admin, update their password_hash
              if (($existing['role'] ?? '') !== 'admin') {
                $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$desiredHash, $existing['id']]);
              }
              $userId = $existing['id'];
            } else {
              // Create a unique username from email or fullname
              $base = '';
              if (strpos($app['email'], '@') !== false) {
                $base = strstr($app['email'], '@', true);
              }
              if (!$base) {
                $base = preg_replace('/[^a-z0-9]+/i', '', strtolower($app['fullname'] ?? 'user')) ?: 'user';
              }
              $base = substr(preg_replace('/[^a-z0-9._-]/i','', $base), 0, 24);
              $candidate = $base;
              $i = 1;
              while (true) {
                $chkU = $pdo->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
                $chkU->execute([$candidate]);
                if (!$chkU->fetch()) break;
                $candidate = $base . $i;
                $i++;
                if ($i > 1000) break; // safety
              }
              $username = $candidate;
              $email_lc = strtolower($app['email']);
              $stmtU = $pdo->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?,?,?,?)");
              $stmtU->execute([$username, $email_lc, $desiredHash, 'citizen']);
              $userId = (int)$pdo->lastInsertId();
            }
          }

          // Insert citizen record, linking user_id if available (copy motivation too)
          $insert = $pdo->prepare("INSERT INTO citizens (application_id, user_id, fullname, email, photo_path, dob, phone, address, motivation, citizenship_type) VALUES (?,?,?,?,?,?,?,?,?,?)");
          $insert->execute([
            $app['id'], $userId, $app['fullname'], $app['email'], $app['photo_path'], $app['dob'], $app['phone'], $app['address'], $app['motivation'] ?? null, $app['application_type']
          ]);
        }
            }
            $pdo->commit();
            flash_set('success','Application #' . $id . ' approved.');
        } elseif ($action === 'app_status') {
            $id = (int)($_POST['id'] ?? 0);
            $new = $_POST['status'] ?? 'pending';
            $allowed = ['pending','under_review','approved','rejected','withdrawn'];
            if (!in_array($new,$allowed,true)) throw new RuntimeException('Invalid status');
            $pdo->prepare("UPDATE citizenship_applications SET status=?, reviewed_by=IF(? IN ('approved','rejected'),?,reviewed_by), reviewed_at=IF(? IN ('approved','rejected'),NOW(),reviewed_at) WHERE id=?")
                ->execute([$new,$new,current_user()['id'],$new,$id]);
            flash_set('success','Application status updated.');
        } elseif ($action === 'app_delete') {
            $id=(int)($_POST['id']??0);
            $pdo->prepare("DELETE FROM citizenship_applications WHERE id=? LIMIT 1")->execute([$id]);
            flash_set('success','Application deleted.');
        } elseif ($action === 'cit_status') {
            $id=(int)($_POST['id']??0); $new=$_POST['status']??'active';
            $allowed=['active','suspended','revoked','resigned'];
            if(!in_array($new,$allowed,true)) throw new RuntimeException('Bad status');
            $pdo->prepare("UPDATE citizens SET active_status=? WHERE id=?")->execute([$new,$id]);
            flash_set('success','Citizen status updated.');
        } elseif ($action === 'cit_delete') {
            $id=(int)($_POST['id']??0);
            $pdo->prepare("DELETE FROM citizens WHERE id=? LIMIT 1")->execute([$id]);
            flash_set('success','Citizen deleted.');
        }
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        flash_set('error','Action failed: ' . $e->getMessage());
    }
    header('Location: admin_manage.php?section=' . urlencode($_GET['section'] ?? 'applications'));
    exit;
}

$section = $_GET['section'] ?? 'applications';

// Fetch data for display
if ($section === 'citizens') {
  $citizens = $pdo->query("SELECT id, fullname, email, citizenship_type, active_status, granted_at FROM citizens ORDER BY id DESC LIMIT 100")->fetchAll();
} else {
  $applications = $pdo->query("SELECT * FROM citizenship_applications ORDER BY id DESC LIMIT 100")->fetchAll();
  $view_app_id = isset($_GET['view_app']) ? (int)$_GET['view_app'] : null;
  $view_app = null;
  if ($view_app_id) {
    $stmt = $pdo->prepare("SELECT * FROM citizenship_applications WHERE id=? LIMIT 1");
    $stmt->execute([$view_app_id]);
    $view_app = $stmt->fetch();
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Management - Qamarshan</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    .manage-wrap { max-width:1200px; margin:6rem auto 3rem; padding:0 1.2rem; }
    .tabs { display:flex; gap:.75rem; margin:0 0 1.5rem; flex-wrap:wrap; }
    .tabs a { text-decoration:none; padding:.6rem 1rem; border-radius:10px; background:rgba(255,255,255,.6); font-weight:600; color:#2d3748; border:1px solid rgba(0,0,0,.05); backdrop-filter:blur(6px); }
    .tabs a.active { background:linear-gradient(135deg,#48bb78,#38a169); color:#fff; box-shadow:0 6px 18px rgba(72,187,120,.35); }
    table.list { width:100%; border-collapse:collapse; font-size:.85rem; }
    table.list th, table.list td { padding:.55rem .7rem; text-align:left; }
    table.list thead { background:rgba(72,187,120,.15); }
    table.list tbody tr { background:rgba(255,255,255,.85); }
    table.list tbody tr + tr { border-top:2px solid rgba(0,0,0,.03); }
    form.inline { display:inline-block; margin:0 .25rem; }
    form.inline select, form.inline button { font-size:.7rem; }
    .flash-box { margin:1rem 0; padding:.85rem 1rem; border-radius:12px; font-weight:600; }
    .flash-ok { background:rgba(72,187,120,.15); border:1px solid rgba(72,187,120,.5); color:#22543d; }
    .flash-err { background:rgba(229,62,62,.12); border:1px solid rgba(229,62,62,.4); color:#742a2a; }
  </style>
</head>
<body>
<?php include __DIR__ . '/inc_nav.php'; ?>
<div class="manage-wrap card">
  <h1 style="margin:0 0 .2rem;">Administration</h1>
  <p style="opacity:.8;margin:0 0 1.2rem;">Applications and Citizenship Management</p>
  <?php if ($m = flash_get('success')): ?><div class="flash-box flash-ok"><?php echo sanitize($m); ?></div><?php endif; ?>
  <?php if ($m = flash_get('error')): ?><div class="flash-box flash-err"><?php echo sanitize($m); ?></div><?php endif; ?>
  <div class="tabs">
    <a href="admin_manage.php?section=applications" class="<?php echo $section==='applications'?'active':''; ?>">Applications</a>
    <a href="admin_manage.php?section=citizens" class="<?php echo $section==='citizens'?'active':''; ?>">Citizens</a>
  </div>

  <?php if ($section === 'citizens'): ?>
  <table class="list">
      <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Type</th><th>Status</th><th>Granted</th><th>Actions</th></tr></thead>
      <tbody>
        <?php if (empty($citizens)): ?>
          <tr><td colspan="7" style="opacity:.6;">No citizens yet.</td></tr>
        <?php else: foreach ($citizens as $c): ?>
          <tr>
            <td><?php echo (int)$c['id']; ?></td>
            <td><?php echo sanitize($c['fullname']); ?></td>
            <td><?php echo sanitize($c['email']); ?></td>
            <td><?php echo sanitize($c['citizenship_type'] ?? '-'); ?></td>
            <td><?php echo sanitize($c['active_status']); ?></td>
            <td><?php echo sanitize($c['granted_at']); ?></td>
            <td>
              <form class="inline" method="post">
                <input type="hidden" name="action" value="cit_status" />
                <input type="hidden" name="id" value="<?php echo (int)$c['id']; ?>" />
                <select name="status" class="select-small">
                  <?php foreach(['active','suspended','revoked','resigned'] as $st): ?>
                    <option value="<?php echo $st; ?>" <?php if($c['active_status']===$st) echo 'selected'; ?>><?php echo $st; ?></option>
                  <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-small">Save</button>
              </form>
              <form class="inline" method="post" onsubmit="return confirm('Delete citizen?');">
                <input type="hidden" name="action" value="cit_delete" />
                <input type="hidden" name="id" value="<?php echo (int)$c['id']; ?>" />
                <button type="submit" class="btn-small danger">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  <?php else: ?>
  <table class="list">
      <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Type</th><th>Status</th><th>Submitted</th><th>Actions</th></tr></thead>
      <tbody>
        <?php if (empty($applications)): ?>
          <tr><td colspan="7" style="opacity:.6;">No applications yet.</td></tr>
        <?php else: foreach ($applications as $a): ?>
          <tr>
            <td><?php echo (int)$a['id']; ?></td>
            <td><?php echo sanitize($a['fullname']); ?></td>
            <td><?php echo sanitize($a['email']); ?></td>
            <td><?php echo sanitize($a['application_type'] ?? '-'); ?></td>
            <td><?php echo sanitize($a['status']); ?></td>
            <td><?php echo sanitize($a['submitted_at']); ?></td>
            <td>
              <a href="admin_manage.php?section=applications&amp;view_app=<?php echo (int)$a['id']; ?>" class="btn-small" style="margin-bottom:.3rem;">View Details</a>
              <?php if ($a['status'] !== 'approved'): ?>
              <form class="inline" method="post">
                <input type="hidden" name="action" value="app_approve" />
                <input type="hidden" name="id" value="<?php echo (int)$a['id']; ?>" />
                <button type="submit" class="btn-small">Approve</button>
              </form>
              <?php endif; ?>
              <form class="inline" method="post">
                <input type="hidden" name="action" value="app_status" />
                <input type="hidden" name="id" value="<?php echo (int)$a['id']; ?>" />
                <select name="status" class="select-small">
                  <?php foreach(['pending','under_review','approved','rejected','withdrawn'] as $st): ?>
                    <option value="<?php echo $st; ?>" <?php if($a['status']===$st) echo 'selected'; ?>><?php echo $st; ?></option>
                  <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-small">Set</button>
              </form>
              <form class="inline" method="post" onsubmit="return confirm('Delete application? This cannot be undone.');">
                <input type="hidden" name="action" value="app_delete" />
                <input type="hidden" name="id" value="<?php echo (int)$a['id']; ?>" />
                <button type="submit" class="btn-small danger">Delete</button>
              </form>
            </td>
          </tr>
          <?php if (isset($view_app) && $view_app && $view_app['id'] == $a['id']): ?>
          <tr>
            <td colspan="7">
              <div class="card" style="margin:1.2rem 0 0 0;padding:1.5rem 1.2rem;">
                <h3 style="margin-top:0;margin-bottom:1rem;font-size:1.15rem;">Application Details</h3>
                <table style="width:100%;font-size:.97rem;line-height:1.5;">
                  <tr><td><strong>Full Name:</strong></td><td><?php echo sanitize($view_app['fullname'] ?? ''); ?></td></tr>
                  <tr><td><strong>Email:</strong></td><td><?php echo sanitize($view_app['email'] ?? ''); ?></td></tr>
                  <tr><td><strong>Date of Birth:</strong></td><td><?php echo sanitize($view_app['dob'] ?? ''); ?></td></tr>
                  <tr><td><strong>Phone:</strong></td><td><?php echo sanitize($view_app['phone'] ?? ''); ?></td></tr>
                  <tr><td><strong>Address:</strong></td><td><?php echo sanitize($view_app['address'] ?? ''); ?></td></tr>
                  <tr><td><strong>Motivation:</strong></td><td><?php echo sanitize($view_app['motivation'] ?? ''); ?></td></tr>
                  <tr><td><strong>Application Type:</strong></td><td><?php echo sanitize($view_app['application_type'] ?? ''); ?></td></tr>
                  <tr><td><strong>Status:</strong></td><td><?php echo sanitize($view_app['status'] ?? ''); ?></td></tr>
                  <tr><td><strong>Submitted At:</strong></td><td><?php echo sanitize($view_app['submitted_at'] ?? ''); ?></td></tr>
                  <?php if (!empty($view_app['photo_path'])): ?>
                  <tr><td><strong>Photo:</strong></td><td><a href="<?php echo sanitize($view_app['photo_path']); ?>" target="_blank"><img src="<?php echo sanitize($view_app['photo_path']); ?>" alt="photo" style="max-width:120px;border-radius:10px;box-shadow:0 2px 8px rgba(72,187,120,.13);vertical-align:middle;" /></a></td></tr>
                  <?php endif; ?>
                </table>
                <div style="margin-top:1.2rem;text-align:right;"><a href="admin_manage.php?section=applications" class="btn-small">Close</a></div>
              </div>
            </td>
          </tr>
          <?php endif; ?>
        <?php endforeach; endif; ?>
  <?php endif; ?>
</div>
</body>
</html>