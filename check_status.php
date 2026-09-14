<?php
$pageTitle = 'Check Registration Status — Tantiado Online E-bike Registration';

$result = null;
$error  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['ref'])) {
    include 'includes/db.php';
    $ref = trim($conn->real_escape_string($_POST['reference_no'] ?? $_GET['ref'] ?? ''));

    if (!$ref) {
        $error = 'Please enter your reference number.';
    } else {
        $res = $conn->query("SELECT * FROM registrations WHERE reference_no = '$ref' LIMIT 1");
        if ($res && $res->num_rows > 0) {
            $result = $res->fetch_assoc();
        } else {
            $error = 'No registration found for that reference number. Double-check and try again.';
        }
    }
}

include 'includes/header.php';
?>

<style>
.status-wrap { max-width: 640px; margin: 0 auto; padding: 7rem 0 5rem; }
.search-card { background:var(--dark2); border:1px solid rgba(255,255,255,0.08); border-radius:10px; padding:2rem; margin-bottom:2rem; }
.result-card { background:var(--dark2); border:1px solid rgba(255,255,255,0.08); border-radius:10px; overflow:hidden; margin-bottom:1.5rem; }
.result-header { padding:1.25rem 1.5rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem; }
.result-header.pending  { background:rgba(243,156,18,0.08);  border-bottom:1px solid rgba(243,156,18,0.2); }
.result-header.approved { background:rgba(0,230,122,0.08);   border-bottom:1px solid rgba(0,230,122,0.2); }
.result-header.rejected { background:rgba(231,76,60,0.08);   border-bottom:1px solid rgba(231,76,60,0.2); }
.result-body { padding:1.5rem; }
.detail-grid { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
.detail-item { display:flex; flex-direction:column; gap:0.2rem; }
.detail-item.full { grid-column: 1 / -1; }
.detail-label { font-size:0.72rem; font-weight:600; color:var(--gray); text-transform:uppercase; letter-spacing:1px; }
.detail-value { font-size:0.92rem; }
.status-bar { padding:1rem 1.5rem; background:rgba(255,255,255,0.03); border-top:1px solid rgba(255,255,255,0.06); font-size:1.1rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.75rem; }
.admin-response-box { margin-top:1rem; padding:1rem 1.25rem; border-radius:8px; font-size:0.9rem; line-height:1.6; }
.admin-response-box.approved { background:rgba(0,230,122,0.08); border:1px solid rgba(0,230,122,0.25); }
.admin-response-box.rejected { background:rgba(231,76,60,0.08);  border:1px solid rgba(231,76,60,0.25); }
.admin-response-label { font-size:0.72rem; font-weight:600; text-transform:uppercase; letter-spacing:1px; margin-bottom:0.4rem; }
.admin-response-box.approved .admin-response-label { color:var(--green); }
.admin-response-box.rejected .admin-response-label { color:var(--danger); }
.status-dot { font-size: 1rem; }
</style>

<div class="container">
  <div class="status-wrap">

    <div style="padding-top:0;margin-bottom:2rem">
      <div class="section-label">Status Lookup</div>
      <h2 class="section-title">CHECK YOUR STATUS</h2>
      <p class="section-sub" style="margin-bottom:0">Enter the reference number you received when you registered (e.g. EB-2026-00001).</p>
    </div>

    <div class="search-card">
      <form method="POST" action="check_status.php">
        <div class="form-group">
          <label>Reference Number</label>
          <input type="text" name="reference_no" value="<?= htmlspecialchars($_POST['reference_no'] ?? $_GET['ref'] ?? '') ?>"
                 placeholder="EB-2026-00001" required autofocus>
        </div>
        <?php if ($error): ?>
          <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Look Up Status →</button>
      </form>
    </div>

    <?php if ($result): $r = $result; ?>
      <div class="result-card">
        <div class="result-header <?= strtolower($r['status']) ?>">
          <div>
            <div style="font-size:0.75rem;color:var(--gray);margin-bottom:0.2rem">Reference Number</div>
            <strong style="font-family:'Bebas Neue',sans-serif;font-size:1.3rem;letter-spacing:1px">
              <?= htmlspecialchars($r['reference_no']) ?>
            </strong>
          </div>
          <span class="badge badge-<?= strtolower($r['status']) ?>">
            <?php if ($r['status'] === 'Pending'): ?>🟡 Pending
            <?php elseif ($r['status'] === 'Approved'): ?>🟢 Approved
            <?php else: ?>🔴 Rejected<?php endif; ?>
          </span>
        </div>
        <div class="result-body">
          <div class="detail-grid">
            <div class="detail-item">
              <span class="detail-label">Applicant Name</span>
              <span class="detail-value"><?= htmlspecialchars($r['applicant_name']) ?></span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Contact Number</span>
              <span class="detail-value"><?= htmlspecialchars($r['contact_number']) ?></span>
            </div>
            <div class="detail-item">
              <span class="detail-label">E-Bike Brand / Model</span>
              <span class="detail-value"><?= htmlspecialchars($r['ebike_brand_model']) ?></span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Serial No.</span>
              <span class="detail-value" style="font-family:monospace"><?= htmlspecialchars($r['serial_no']) ?></span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Registration Type</span>
              <span class="detail-value"><?= htmlspecialchars($r['registration_type']) ?></span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Submitted</span>
              <span class="detail-value"><?= date('M d, Y', strtotime($r['created_at'])) ?></span>
            </div>
            <div class="detail-item full">
              <span class="detail-label">Address / Barangay</span>
              <span class="detail-value"><?= htmlspecialchars($r['address']) ?></span>
            </div>
          </div>

          <?php if ($r['status'] !== 'Pending' && $r['admin_response']): ?>
            <div class="admin-response-box <?= strtolower($r['status']) ?>">
              <div class="admin-response-label">
                <?= $r['status'] === 'Approved' ? '✅ Message from Admin' : '❌ Reason for Rejection' ?>
              </div>
              <?= nl2br(htmlspecialchars($r['admin_response'])) ?>
            </div>
          <?php endif; ?>
        </div>
        <div class="status-bar">
          <div>
            <?php if ($r['status'] === 'Pending'): ?>
              <span style="font-size:0.88rem;color:var(--gray)">🟡 Your registration is under review. Please check back soon.</span>
            <?php elseif ($r['status'] === 'Approved'): ?>
              <span style="font-size:0.88rem;color:var(--gray)">🟢 Your e-bike is officially registered. Congratulations!</span>
            <?php else: ?>
              <span style="font-size:0.88rem;color:var(--gray)">🔴 Your registration was rejected. See the reason above.</span>
            <?php endif; ?>
          </div>
          <?php if ($r['status'] === 'Approved'): ?>
            <a href="certificate.php?ref=<?= urlencode($r['reference_no']) ?>" class="btn btn-primary btn-sm">View Certificate →</a>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>

  </div>
</div>

<?php include 'includes/footer.php'; ?>
