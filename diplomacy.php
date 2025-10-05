<?php require __DIR__ . '/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diplomatic Communication - Mashaykhat Qamarshan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include __DIR__ . '/inc_nav.php'; ?>

<!-- Form Container -->
<main class="form-main">
    <div class="form-container" style="max-width:650px;">
        <div class="form-card">
            <div class="form-header">
                <h1>Diplomatic Communication</h1>
                <p>Reach out formally to the Mashaykhat for recognition, treaties, or inquiries.</p>
            </div>
            
                        <?php if ($err = flash_get('error')): ?>
                            <div style="margin-bottom:1rem;padding:.75rem 1rem;background:rgba(229,62,62,.12);border:1px solid rgba(229,62,62,.4);border-radius:10px;color:#742a2a; font-weight:600; "><?php echo sanitize($err); ?></div>
                        <?php endif; ?>
                        <?php if ($ok = flash_get('success')): ?>
                            <div style="margin-bottom:1rem;padding:.75rem 1rem;background:rgba(72,187,120,.15);border:1px solid rgba(72,187,120,.4);border-radius:10px;color:#22543d; font-weight:600; "><?php echo sanitize($ok); ?></div>
                        <?php endif; ?>
                        <form action="submit_diplomacy.php" method="POST" class="form-content" enctype="multipart/form-data">
                <!-- State / Organization Info -->
                <div class="form-group">
                    <label for="state_name">Your State / Organization Name</label>
                    <input type="text" id="state_name" name="state_name" required>
                </div>

                <div class="form-group">
                    <label for="contact_person">Primary Contact Person</label>
                    <input type="text" id="contact_person" name="contact_person" required>
                </div>

                <div class="form-group">
                    <label for="email">Official Contact Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <!-- Communication Details -->
                <div class="form-group">
                    <label for="category">Communication Type</label>
                    <select id="category" name="category" required>
                        <option value="">-- Select Type --</option>
                        <option value="recognition">Request for Mutual Recognition</option>
                        <option value="treaty">Treaty / Partnership Proposal</option>
                        <option value="visit">Official Visit Inquiry</option>
                        <option value="press">Press / Media Inquiry</option>
                        <option value="other">Other Diplomatic Matter</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="message">Formal Message / Proposal</label>
                    <textarea id="message" name="message" rows="6" required></textarea>
                </div>

                <div class="form-group">
                    <label for="attachment">Attach Supporting Document (Optional)</label>
                    <input type="file" id="attachment" name="attachment" accept="application/pdf,image/*,.doc,.docx,.txt">
                </div>

                <button type="submit" class="form-btn">Send Communication</button>
            </form>
            
            <div class="form-footer">
                <p>Looking to become a citizen instead? <a href="apply.php" class="form-link-primary">Apply Here</a></p>
            </div>
        </div>
    </div>
</main>

<section style="max-width:650px;margin:2.5rem auto 0;">
    <div class="card" style="padding:2rem 1.5rem;">
        <h2 style="margin-top:0;margin-bottom:1.2rem;font-size:1.15rem;text-align:center;">Current Diplomatic Status</h2>
        <div style="display:flex;gap:1.2rem;flex-wrap:wrap;justify-content:center;">
            <div class="card" style="flex:1 1 180px;min-width:180px;padding:1.1rem .8rem;box-shadow:none;margin:0;">
                <h3 style="margin:0 0 .4rem;font-size:1rem;opacity:.8;">Mutual Recognition</h3>
                <ul style="margin:0 0 .7rem .7rem;opacity:.7;">
                    <li>None</li>
                </ul>
            </div>
            <div class="card" style="flex:1 1 180px;min-width:180px;padding:1.1rem .8rem;box-shadow:none;margin:0;">
                <h3 style="margin:0 0 .4rem;font-size:1rem;opacity:.8;">Unilateral Recognition</h3>
                <ul style="margin:0 0 .7rem .7rem;">
                    <li>Arakan</li>
                    <li>Ichkeria</li>
                    <li>Kashmir</li>
                    <li>Palestine</li>
                    <li>Khalistan</li>
                    <li>Dravida Nadu</li>
                    <li>East Turkistan</li>
                    <li>Northern Cyprus</li>
                </ul>
            </div>
            <div class="card" style="flex:1 1 180px;min-width:180px;padding:1.1rem .8rem;box-shadow:none;margin:0;">
                <h3 style="margin:0 0 .4rem;font-size:1rem;opacity:.8;">Not Recognised</h3>
                <ul style="margin:0 0 .7rem .7rem;">
                    <li>Israel<em> (Considered a terrorist entity)</em></li>
                    <li>India<em> (Considered an occupier entity)</em></li>
                </ul>
            </div>
        </div>
    </div>
</section>
<footer>
    <p>&copy; 2025 Mashaykhat Qamarshan | <a href="https://micronations.wiki/wiki/Qamarshan">Learn More</a></p>
</footer>
</body>
</html>
