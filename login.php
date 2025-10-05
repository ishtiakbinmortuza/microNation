<?php require __DIR__ . '/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Citizen Login - Mashaykhat Qamarshan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include __DIR__ . '/inc_nav.php'; ?>

<!-- Form Container -->
<main class="form-main">
    <div class="form-container">
        <div class="form-card">
            <div class="form-header">
                <h1>Citizen Portal</h1>
                <p>Access your Qamarshan citizenship account</p>
            </div>
            
                        <?php if ($err = flash_get('error')): ?>
                            <div style="margin-bottom:1rem;padding:.75rem 1rem;background:rgba(229,62,62,.12);border:1px solid rgba(229,62,62,.4);border-radius:10px;color:#742a2a; font-weight:600;"><?php echo sanitize($err); ?></div>
                        <?php endif; ?>
                        <?php if ($ok = flash_get('success')): ?>
                            <div style="margin-bottom:1rem;padding:.75rem 1rem;background:rgba(72,187,120,.15);border:1px solid rgba(72,187,120,.4);border-radius:10px;color:#22543d; font-weight:600;"><?php echo sanitize($ok); ?></div>
                        <?php endif; ?>
                        <form action="login_handler.php" method="POST" class="form-content">
                <div class="form-group">
                    <label for="identifier">Username or Email</label>
                    <input type="text" id="identifier" name="identifier" placeholder="you@example.com or your-username" autocomplete="username" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="form-btn">Sign In</button>
                
                <div class="form-links">
                    <a href="#" class="form-link">Forgot Password?</a>
                </div>
            </form>
            
            <div class="form-footer">
                <p>Not a citizen yet? <a href="apply.php" class="form-link-primary">Apply for Citizenship</a></p>
            </div>
        </div>
    </div>
</main>

<footer>
    <p>&copy; 2025 Mashaykhat Qamarshan | <a href="https://micronations.wiki/wiki/Qamarshan">Learn More</a></p>
</footer>
</body>
</html>
