<?php
$pageTitle = 'Registration Certificate — Tantiado Online E-bike Registration';

$result = null;
$error  = '';
$ref    = trim($_GET['ref'] ?? $_POST['reference_no'] ?? '');

include 'includes/db.php';

if ($ref) {
    $safeRef = $conn->real_escape_string($ref);
    $res = $conn->query("SELECT * FROM registrations WHERE reference_no = '$safeRef' LIMIT 1");
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        if ($row['status'] === 'Approved') {
            $result = $row;
        } else {
            $error = 'This registration has not been approved yet, so no certificate is available.';
        }
    } else {
        $error = 'No registration found for that reference number.';
    }
}

include 'includes/header.php';
?>

<style>
.cert-wrap { max-width: 720px; margin: 0 auto; padding: 7rem 0 5rem; }
.lookup-card { background:var(--dark2); border:1px solid rgba(255,255,255,0.08); border-radius:10px; padding:2rem; margin-bottom:2rem; }
.certificate {
  background: linear-gradient(180deg, rgba(0,230,122,0.05), transparent 40%), var(--dark2);
  border: 2px solid var(--green); border-radius: 14px; padding: 3rem 2.5rem; position: relative;
}
.certificate::before {
  content: ''; position: absolute; inset: 10px; border: 1px solid rgba(0,230,122,0.35); border-radius: 8px; pointer-events: none;
}
.cert-eyebrow { text-align:center; font-size:0.75rem; letter-spacing:3px; text-transform:uppercase; color: var(--green); margin-bottom:0.5rem; }
.cert-title { text-align:center; font-family:'Bebas Neue',sans-serif; font-size: clamp(1.8rem,4vw,2.6rem); letter-spacing:2px; margin-bottom:0.25rem; }
.cert-sub { text-align:center; color: rgba(255,255,255,0.55); font-size:0.88rem; margin-bottom:2rem; }
.cert-name { text-align:center; font-family:'Bebas Neue',sans-serif; font-size: clamp(2.2rem,5vw,3rem); letter-spacing:1px; margin: 1.5rem 0; color: var(--white); }
.cert-grid { display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; margin: 2rem 0; }
.cert-item { text-align:center; }
.cert-label { font-size:0.7rem; color:var(--gray); text-transform:uppercase; letter-spacing:1px; margin-bottom:0.25rem; }
.cert-value { font-size:0.95rem; }
.cert-ref { text-align:center; margin: 1.5rem 0; }
.cert-ref-badge {
  display:inline-block; background:rgba(0,230,122,0.15); border:1px solid var(--green);
  color:var(--green); border-radius:6px; padding:0.5rem 1.25rem;
  font-family:'Bebas Neue',sans-serif; font-size:1.4rem; letter-spacing:3px;
}
.cert-footer { text-align:center; margin-top:2rem; padding-top:1.5rem; border-top:1px dashed rgba(255,255,255,0.15); font-size:0.8rem; color:var(--gray); }
.cert-actions { text-align:center; margin-top:1.5rem; display:flex; gap:0.75rem; justify-content:center; flex-wrap:wrap; }

@media print {
  .main-nav, .main-footer, .lookup-card, .cert-actions { display:none !important; }
  body { background: #fff !important; color: #000 !important; }
  .certificate { border-color:#000; background:#fff; }
  .cert-name, .cert-title, .cert-value, .cert-label { color:#000 !important; }
}
</style>

<div class="container">
  <div class="cert-wrap">

    <div style="padding-top:0;margin-bottom:2rem" class="no-print">
      <div class="section-label">Digital Certificate</div>
      <h2 class="section-title">REGISTRATION CERTIFICATE</h2>
      <p class="section-sub" style="margin-bottom:0">Enter your reference number to view your certificate. Only approved applications can generate one.</p>
    </div>

    <div class="lookup-card">
      <form method="GET" action="certificate.php">
        <div class="form-group">
          <label>Reference Number</label>
          <input type="text" name="ref" value="<?= htmlspecialchars($ref) ?>" placeholder="EB-2026-00001" required autofocus>
        </div>
        <?php if ($error): ?>
          <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">View Certificate →</button>
      </form>
    </div>

    <?php if ($result): $r = $result; ?>
      <div class="certificate" id="certificateBlock">
        <div class="cert-eyebrow">⚡ Tantiado's Online E-Bike Registration</div>
        <div class="cert-title">CERTIFICATE OF REGISTRATION</div>
        <div class="cert-sub">This certifies that the e-bike described below is officially registered.</div>

        <div class="cert-name"><?= htmlspecialchars($r['applicant_name']) ?></div>

        <div class="cert-grid">
          <div class="cert-item">
            <div class="cert-label">E-Bike Brand / Model</div>
            <div class="cert-value"><?= htmlspecialchars($r['ebike_brand_model']) ?></div>
          </div>
          <div class="cert-item">
            <div class="cert-label">Serial Number</div>
            <div class="cert-value" style="font-family:monospace"><?= htmlspecialchars($r['serial_no']) ?></div>
          </div>
          <div class="cert-item">
            <div class="cert-label">Registration Type</div>
            <div class="cert-value"><?= htmlspecialchars($r['registration_type']) ?></div>
          </div>
          <div class="cert-item">
            <div class="cert-label">Year Bought</div>
            <div class="cert-value"><?= htmlspecialchars($r['year_bought']) ?></div>
          </div>
        </div>

        <div class="cert-ref">
          <div class="cert-label" style="margin-bottom:0.5rem">Reference Number</div>
          <div class="cert-ref-badge"><?= htmlspecialchars($r['reference_no']) ?></div>
        </div>

        <div class="cert-footer">
          <div>Date Approved: <?= date('F d, Y', strtotime($r['updated_at'])) ?></div>
          <div style="margin-top:0.25rem">Status: <strong style="color:var(--green)">🟢 Approved</strong></div>
        </div>
      </div>

      <div class="cert-actions">
        <button type="button" class="btn btn-primary" onclick="window.print()">🖨️ Print / Save as PDF</button>
        <a href="check_status.php?ref=<?= urlencode($r['reference_no']) ?>" class="btn btn-outline">← Back to Status</a>
      </div>
    <?php endif; ?>

  </div>
</div>

<?php include 'includes/footer.php'; ?>
