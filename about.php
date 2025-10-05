<?php require __DIR__ . '/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>About – Mashaykhat Qamarshan</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
<?php include __DIR__ . '/inc_nav.php'; ?>

<main style="max-width:1200px;margin:6rem auto 3rem;padding:0 1.25rem;">
  <!-- Hero / Identity Card -->
  <section class="card" style="margin-bottom:2rem;padding:2.5rem 2rem;display:flex;flex-direction:column;gap:1.25rem;">
    <div style="display:flex;flex-direction:column;gap:.5rem;align-items:center;text-align:center;">
      <h1 style="margin:0;font-size:2.4rem;">Mashaykhat Qamarshan</h1>
      <p style="margin:0;font-weight:500;opacity:.85;">“If you can't find happiness, create it”</p>
    </div>
    <div style="display:flex;flex-wrap:wrap;gap:1.5rem;align-items:center;">
      <figure style="margin:0;max-width:260px;flex:1 1 240px;text-align:center;">
        <img src="qamarshan_flag.jpg" alt="National Flag" style="width:100%;border-radius:14px;box-shadow:0 10px 30px rgba(0,0,0,.12);" />
        <figcaption style="margin-top:.5rem;font-size:.75rem;letter-spacing:.5px;opacity:.7;font-weight:600;">Flag</figcaption>
      </figure>
      <div style="flex:2 1 340px;min-width:300px;display:flex;flex-direction:column;align-items:center;text-align:center;"> 
        <p style="margin:0 0 1rem;">Founded <strong>25 Radjab 1442 / 9 Mar 2021</strong> (as Ahlamistan), renamed <strong>21 Rabi' al‑Awwal 1447 / 15 Sept 2025</strong>. An <strong>Absolute Monarchy</strong> under Sheikh Ul Qamarshan Muhammad Sheikh Ishtiak bin Mortuza (Sheikh VI).</p>
        <p style="margin:0 0 1rem;">
          <strong>Territorial Claim:</strong> Ramna Park, Dhaka<br>
          <strong>Languages:</strong> Bangla • English • Urdu • Arabic<br>
          <strong>Currency:</strong> Dīnār (DNR)
        </p>
      </div>
      <figure style="margin:0;max-width:220px;flex:1 1 200px;text-align:center;">
        <?php $emblem = 'qamarshan_emblem.jpg'; if (!is_file(__DIR__ . '/' . $emblem)) { $emblem = 'qamarshan_flag.jpg'; } ?>
        <img src="<?php echo sanitize($emblem); ?>" alt="National Emblem" style="width:100%;border-radius:50%;box-shadow:0 10px 30px rgba(0,0,0,.12);background:#fff;padding:0.75rem;object-fit:contain;" />
        <figcaption style="margin-top:.5rem;font-size:.75rem;letter-spacing:.5px;opacity:.7;font-weight:600;">Emblem</figcaption>
      </figure>
    </div>
  </section>

  <!-- Content Grid -->
  <section style="display:grid;gap:1.5rem;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));margin-bottom:2rem;">
    <div class="card" style="padding:1.5rem 1.25rem;text-align:center;">
      <h2 style="margin:0 0 .6rem;font-size:1.15rem;">Name & Meaning</h2>
      <p style="margin:0;font-size:.9rem;line-height:1.5;">Derived from <em>Qamar</em> (Crescent) and <em>Arshan</em> (Noble Land), Qamarshan stands for “Noble Land of Crescent”, reflecting muslim bengal's cultural and spiritual symbolism.</p>
    </div>
    <div class="card" style="padding:1.5rem 1.25rem;text-align:center;">
      <h2 style="margin:0 0 .6rem;font-size:1.15rem;">Governance & Society</h2>
      <p style="margin:0;font-size:.9rem;line-height:1.5;">Legislative authority resides with the Sheikh within Shariah principles. Current administrative structure is legally based on Shariah. Multicultural engagement encouraged.</p>
    </div>
    <div class="card" style="padding:1.5rem 1.25rem;text-align:center;">
      <h2 style="margin:0 0 .6rem;font-size:1.15rem;">Territorial Claim</h2>
      <p style="margin:0;font-size:.9rem;line-height:1.5;">Territorial claim was declared on <strong>3 Aug 2021</strong> over Ramna Park (Dhaka), a significant green area situated on the heart of the city.</p>
    </div>
    <div class="card" style="padding:1.5rem 1.25rem;text-align:center;">
      <h2 style="margin:0 0 .6rem;font-size:1.15rem;">Economy & Symbols</h2>
      <p style="margin:0;font-size:.9rem;line-height:1.5;">A Gold standard Dīnār is declared on April 5, 2025, open for intermicronational use; heraldic set (flag, emblem, tughra) designed by the Sheikh drawing on Turkic & Islamic art influences.</p>
    </div>
    <div class="card" style="padding:1.5rem 1.25rem;text-align:center;">
      <h2 style="margin:0 0 .6rem;font-size:1.15rem;">Diplomacy</h2>
      <p style="margin:0;font-size:.9rem;line-height:1.5;">Selective micronational engagement; prioritises principled, respectful recognition over volume of treaties.</p>
    </div>
    <div class="card" style="padding:1.5rem 1.25rem;">
      <h2 style="margin:0 0 .6rem;font-size:1.15rem;text-align:center;">Honours</h2>
      <p style="margin:0;font-size:.9rem;line-height:1.5;">
      <ul style="margin:0;padding-left:1.1rem;line-height:1.4;font-size:.9rem;">
        <li><strong>Nishan e Imtiaz:</strong> Order of Distinction</li>
        <li><strong>Nishan e Muhtasham:</strong> Order of Magnificence</li>
        <li><strong>Nishan e Shafqat:</strong> Order of Kindness</li>
        <li><strong>Nishan e Iftikhar:</strong> Order of Glory</li>
      </ul>
    </div>
    <div class="card" style="padding:1.5rem 1.25rem;">
      <h2 style="margin:0 0 .6rem;font-size:1.15rem;text-align:center;">At a Glance</h2>
      <ul style="margin:0;padding-left:1.1rem;line-height:1.4;font-size:.9rem;">
        <li>Founded 2021</li>
        <li>Renamed 2025</li>
        <li>Absolute Monarchy</li>
        <li>Claim: Qamarabad</li>
        <li>Multi‑lingual cultural scope</li>
        <li>Motto embodies proactive joy</li>
      </ul>
    </div>
    <div class="card" style="padding:1.5rem 1.25rem;text-align:center;">
      <h2 style="margin:0 0 .6rem;font-size:1.05rem;">Attribution</h2>
      <p style="margin:0;font-size:.75rem;line-height:1.4;opacity:.8;">Portions adapted from the public MicroWiki article “Qamarshan”. Content under <a href="https://creativecommons.org/licenses/by-sa/4.0/" class="form-link" target="_blank" rel="noopener">CC‑BY‑SA 4.0</a>. Images & heraldry © respective creators.</p>
    </div>
  </section>


</main>

<footer>
  <p>&copy; 2025 Mashaykhat Qamarshan | <a href="https://micronations.wiki/wiki/Qamarshan">Full Profile</a></p>
</footer>
</body>
</html>