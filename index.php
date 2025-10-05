<?php require __DIR__ . '/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mashaykhat Qamarshan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include __DIR__ . '/inc_nav.php'; ?>

<!-- Hero -->
<section class="hero-container">
    <div class="hero-card">
        <h1>Mashaykhat Qamarshan</h1>
        <p>Official Website Representing the Noble Land of Crescent</p>
        <a href="apply.php" class="btn">Apply for Citizenship</a>
        <a href="login.php" class="btn">Citizen Portal Login</a>
    </div>

</section>
 
<!-- Features -->
<section class="features">
    <h2>Look around</h2>
    <div class="feature-grid">
        <a href="apply.php" class="btn">Apply for Citizenship</a>
        <a href="login.php" class="btn">Log In</a>
        <a href="diplomacy.php" class="btn">Diplomatic Communication</a>
    </div>
</section>

<!-- News & Notices Section -->
<section style="max-width:700px;margin:3rem auto 0;">
    <div class="card" style="padding:2rem 1.5rem;">
        <h2 style="margin-top:0;margin-bottom:1.2rem;font-size:1.5rem;">News & Notices</h2>
        <ul style="list-style:disc;padding-left:1.2rem;line-height:1.6;margin:0;">
            <li><strong>2025-10-05:</strong> Qamarshan website relaunch with new citizen portal and profile features.</li>
            <li><strong>2025-09-15:</strong> Official renaming to Mashaykhat Qamarshan and new emblem adopted.</li>
            <li><strong>2025-08-01:</strong> Applications for citizenship now open to the public.</li>
            <li><strong>2025-07-20:</strong> Diplomatic communication form launched for micronational outreach.</li>
        </ul>
        <p style="margin-top:1.5rem;font-size:.95rem;opacity:.7;">For official notices and updates, check this section regularly.</p>
    </div>
</section>

<!-- Video Section -->
<section class="video-section">
        <div style="max-width:700px;margin:0 auto;">
            <div id="video-container" style="background:#fff;border-radius:18px;box-shadow:0 8px 32px rgba(72,187,120,.13);border:2px solid #48bb78;padding:1.2rem;display:flex;justify-content:center;align-items:center;min-height:380px;"></div>
        </div>
    <div style="text-align:center;margin-top:2rem;">
    <a href="https://micronations.wiki" class="btn-small"><u>About Micronations</u></a>
    </div>
</section>

<!-- Add script before closing body -->
<script src="index.js"></script>
<!-- Footer -->
<footer>
    <p>&copy 2025 Mashaykhat Qamarshan | <a href="https://micronations.wiki/wiki/Qamarshan" class="btn-small">Learn More</a></p>
</footer>
</body>
</html>