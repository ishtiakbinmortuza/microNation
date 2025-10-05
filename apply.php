<?php require __DIR__ . '/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Citizenship Application - Mashaykhat Qamarshan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include __DIR__ . '/inc_nav.php'; ?>

<!-- Form Container -->
<main class="form-main">
    <div class="form-container application-form">
        <div class="form-card">
            <div class="form-header">
                <h1>Citizenship Application</h1>
                <p>Please complete the form below to apply for Qamarshan citizenship</p>
            </div>
            
                        <?php if ($err = flash_get('error')): ?>
                            <div style="margin-bottom:1rem;padding:.75rem 1rem;background:rgba(229,62,62,.12);border:1px solid rgba(229,62,62,.4);border-radius:10px;color:#742a2a; font-weight:600;"><?php echo sanitize($err); ?></div>
                        <?php endif; ?>
                        <?php if ($ok = flash_get('success')): ?>
                            <div style="margin-bottom:1rem;padding:.75rem 1rem;background:rgba(72,187,120,.15);border:1px solid rgba(72,187,120,.4);border-radius:10px;color:#22543d; font-weight:600;"><?php echo sanitize($ok); ?></div>
                        <?php endif; ?>
                        <form action="submit_application.php" method="POST" class="form-content" enctype="multipart/form-data">
                <!-- Personal Info -->
                <div class="form-group">
                    <label for="fullname">Full Name</label>
                    <input type="text" id="fullname" name="fullname" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="photo">Upload Photo</label>
                    <input type="file" id="photo" name="photo" accept="image/*">
                </div>

                <div class="form-group">
                    <label for="dob">Date of Birth</label>
                    <input type="date" id="dob" name="dob">
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone">
                </div>

                <div class="form-group">
                    <label for="password">Choose a password (will be set if your application is approved)</label>
                    <input type="password" id="password" name="password" placeholder="Create a strong password" autocomplete="new-password">
                </div>

                <!-- Address -->
                <div class="form-group">
                    <label for="address">Residential Address</label>
                    <textarea id="address" name="address" rows="3"></textarea>
                </div>

                <!-- Motivation -->
                <div class="form-group">
                    <label for="reason">Why do you want to become a citizen?</label>
                    <textarea id="reason" name="reason" rows="4"></textarea>
                </div>

                <!-- Citizenship Type -->
                <div class="form-group">
                    <label for="type">Application Type</label>
                    <select id="type" name="type">
                        <option value="">-- Select Type --</option>
                        <option value="permanent">Permanent Citizenship</option>
                        <option value="honorary">Honorary Citizenship</option>
                        <option value="dual">Dual Citizenship</option>
                    </select>
                </div>

                <button type="submit" class="form-btn">Submit Application</button>
            </form>
            
            <div class="form-footer">
                <p>Already a citizen? <a href="login.php" class="form-link-primary">Sign In Here</a></p>
            </div>
        </div>
    </div>
</main>

<footer>
    <p>&copy; 2025 Mashaykhat Qamarshan | <a href="https://micronations.wiki/wiki/Qamarshan">Learn More</a></p>
</footer>
</body>
</html>
