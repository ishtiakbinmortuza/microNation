<?php
// Reusable navigation include. Expects config.php to be required by the caller.
$user = current_user();
?>
<header>
    <nav>
        <div class="logo">Qamarshan</div>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="diplomacy.php">Diplomacy</a></li>
            <li><a href="apply.php">Application</a></li>
            <?php if (is_logged_in()): ?>
                <li><a href="profile.php">Profile<?php echo isset($user['username']) ? ' ('.htmlspecialchars($user['username'], ENT_QUOTES).' )' : ''; ?></a></li>
            <?php else: ?>
                <li><a href="login.php">Log In</a></li>
            <?php endif; ?>
            <li><a href="about.php">About</a></li>
            <?php if (is_logged_in()): ?>
                <li><a href="logout.php">Logout</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>