<?php
$pageTitle = 'Register Your E-Bike — Tantiado Online E-bike Registration';

$success = $error = '';
$errors  = [];
$old     = [];

$registrationTypes = ['New Registration', 'Renewal', 'Transfer of Ownership'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'includes/db.php';

    $fields = ['applicant_name','address','contact_number','email','ebike_brand_model','serial_no','year_bought','registration_type'];
    foreach ($fields as $f) {
        $old[$f] = trim($conn->real_escape_string($_POST[$f] ?? ''));
    }

    if (!$old['applicant_name'])    $errors['applicant_name']    = 'Applicant name is required.';
    if (!$old['address'])           $errors['address']           = 'Address / Barangay is required.';
    if (!$old['contact_number'])    $errors['contact_number']    = 'Contact number is required.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Enter a valid email address.';
    if (!$old['ebike_brand_model']) $errors['ebike_brand_model'] = 'E-bike brand / model is required.';
    if (!$old['serial_no'])         $errors['serial_no']         = 'Serial number is required.';
    if (!$old['year_bought'] || $old['year_bought'] < 2000 || $old['year_bought'] > (int)date('Y'))
                                     $errors['year_bought']       = 'Enter a valid year purchased.';
    if (!in_array($old['registration_type'], $registrationTypes, true))
                                     $errors['registration_type'] = 'Choose a registration type.';

    if (empty($errors['serial_no'])) {
        $chk = $conn->query("SELECT id FROM registrations WHERE serial_no = '{$old['serial_no']}'");
        if ($chk && $chk->num_rows > 0) $errors['serial_no'] = 'This serial number is already registered.';
    }

    // Documentation picture upload
    $docFilename = '';
    if (!isset($_FILES['doc_picture']) || $_FILES['doc_picture']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors['doc_picture'] = 'Please upload a picture of your documents.';
    } elseif ($_FILES['doc_picture']['error'] !== UPLOAD_ERR_OK) {
        $errors['doc_picture'] = 'There was a problem uploading your file. Please try again.';
    } else {
        $file = $_FILES['doc_picture'];
        $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($file['size'] > UPLOAD_MAX_BYTES) {
            $errors['doc_picture'] = 'File is too large. Maximum size is 3 MB.';
        } elseif (!in_array($ext, UPLOAD_ALLOWED_EXT, true)) {
            $errors['doc_picture'] = 'Only JPG, JPEG, or PNG files are accepted.';
        } else {
            // Verify it's really an image (not just a renamed file) before trusting it.
            $imgInfo = @getimagesize($file['tmp_name']);
            if ($imgInfo === false || !in_array($imgInfo['mime'], UPLOAD_ALLOWED_MIME, true)) {
                $errors['doc_picture'] = 'That file does not look like a valid image.';
            } else {
                $docFilename = 'doc_' . bin2hex(random_bytes(8)) . '.' . $ext;
            }
        }
    }

    if (empty($errors)) {
        if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
        if (!move_uploaded_file($_FILES['doc_picture']['tmp_name'], UPLOAD_DIR . $docFilename)) {
            $error = 'Could not save your documentation picture. Please try again.';
        } else {
            $sql = "INSERT INTO registrations
                      (reference_no, applicant_name, address, contact_number, email, ebike_brand_model,
                       serial_no, year_bought, registration_type, doc_picture, status)
                    VALUES
                      ('', '{$old['applicant_name']}', '{$old['address']}', '{$old['contact_number']}', '{$old['email']}',
                       '{$old['ebike_brand_model']}', '{$old['serial_no']}', '{$old['year_bought']}',
                       '{$old['registration_type']}', '{$docFilename}', 'Pending')";
            if ($conn->query($sql)) {
                $newId  = $conn->insert_id;
                $refNo  = generate_reference_no($conn, $newId);
                $conn->query("UPDATE registrations SET reference_no = '$refNo' WHERE id = $newId");
                $success = $refNo;
                $old     = [];
            } else {
                @unlink(UPLOAD_DIR . $docFilename);
                $error = 'Database error: ' . $conn->error;
            }
        }
    }
}

include 'includes/header.php';
?>

<style>
.reg-wrap {
  display: grid; grid-template-columns: 1fr 1.5fr; gap: 4rem; align-items: start;
  padding: 7rem 0 5rem;
}
.reg-left { position: sticky; top: 90px; }
.reg-left h2 { font-family:'Bebas Neue',sans-serif; font-size:clamp(2.2rem,4vw,3.2rem); letter-spacing:2px; line-height:1; margin-bottom:0.75rem; }
.checklist { list-style:none; margin-top:1.5rem; display:flex; flex-direction:column; gap:0.65rem; }
.checklist li { display:flex; align-items:center; gap:0.6rem; font-size:0.88rem; color:rgba(255,255,255,0.55); }
.checklist li::before { content:'✓'; color:var(--green); font-weight:700; font-size:0.9rem; }
.success-card {
  text-align:center; padding:2.5rem;
  background:rgba(0,230,122,0.07); border:1px solid rgba(0,230,122,0.25); border-radius:10px;
}
.success-card .big-check { font-size:3rem; margin-bottom:1rem; }
.success-card h3 { font-family:'Bebas Neue',sans-serif; font-size:2rem; letter-spacing:1px; color:var(--green); margin-bottom:0.5rem; }
.ref-badge {
  display:inline-block; background:rgba(0,230,122,0.15); border:1px solid var(--green);
  color:var(--green); border-radius:6px; padding:0.6rem 1.5rem;
  font-family:'Bebas Neue',sans-serif; font-size:1.8rem; letter-spacing:3px; margin:1rem 0;
}
.upload-box {
  border: 1.5px dashed rgba(255,255,255,0.2); border-radius: 8px; padding: 1.25rem;
  text-align: center; background: rgba(255,255,255,0.02); cursor: pointer;
}
.upload-box:hover { border-color: var(--green); }
.upload-box input[type=file] { display:none; }
.upload-hint { font-size: 0.78rem; color: var(--gray); margin-top: 0.4rem; }
.upload-filename { font-size: 0.85rem; color: var(--green); margin-top: 0.5rem; word-break: break-all; }
@media(max-width:900px){
  .reg-wrap { grid-template-columns:1fr; padding:6rem 0 4rem; gap:2rem; }
  .reg-left { position:static; }
}
</style>

<div class="container">
  <div class="reg-wrap">

    <div class="reg-left">
      <div class="section-label">Register Now</div>
      <h2>SUBMIT YOUR <span class="text-green">E-BIKE</span></h2>
      <p class="section-sub" style="margin-bottom:1rem">Fill out all required fields, upload a picture of your documents, and you'll receive a reference number to track your application.</p>
      <ul class="checklist">
        <li>Applicant's name, address, and contact info</li>
        <li>E-bike brand / model and serial number</li>
        <li>A clear photo of your documents</li>
      </ul>
    </div>

    <div>
      <?php if ($success): ?>
        <div class="success-card">
          <div class="big-check">✅</div>
          <h3>Registration Submitted!</h3>
          <p style="color:rgba(255,255,255,0.55);margin-bottom:0.5rem">Your reference number is:</p>
          <div class="ref-badge"><?= htmlspecialchars($success) ?></div>
          <p style="color:rgba(255,255,255,0.5);font-size:0.88rem;margin-bottom:1.5rem">
            Save this number — you'll need it to check your status and view your certificate once approved.
          </p>
          <a href="check_status.php?ref=<?= urlencode($success) ?>" class="btn btn-primary">Check My Status →</a>
          &nbsp;
          <a href="register.php" class="btn btn-outline">Register Another</a>
        </div>

      <?php else: ?>

        <?php if ($error): ?>
          <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
          <div class="alert alert-error">⚠️ Please fix the errors below before submitting.</div>
        <?php endif; ?>

        <div class="card">
          <form method="POST" action="register.php" id="regForm" enctype="multipart/form-data" novalidate>

            <div class="form-group">
              <label>Applicant Name *</label>
              <input type="text" name="applicant_name" value="<?= htmlspecialchars($old['applicant_name'] ?? '') ?>"
                     class="<?= isset($errors['applicant_name'])?'error':'' ?>" placeholder="Juan Dela Cruz" required>
              <?php if (isset($errors['applicant_name'])): ?><span class="field-error"><?= $errors['applicant_name'] ?></span><?php endif; ?>
            </div>

            <div class="form-group">
              <label>Address / Barangay *</label>
              <input type="text" name="address" value="<?= htmlspecialchars($old['address'] ?? '') ?>"
                     class="<?= isset($errors['address'])?'error':'' ?>" placeholder="Purok 2, Barangay Cadulawan, Talisay City" required>
              <?php if (isset($errors['address'])): ?><span class="field-error"><?= $errors['address'] ?></span><?php endif; ?>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Contact Number *</label>
                <input type="tel" name="contact_number" value="<?= htmlspecialchars($old['contact_number'] ?? '') ?>"
                       class="<?= isset($errors['contact_number'])?'error':'' ?>" placeholder="+63 9XX XXX XXXX" required>
                <?php if (isset($errors['contact_number'])): ?><span class="field-error"><?= $errors['contact_number'] ?></span><?php endif; ?>
              </div>
              <div class="form-group">
                <label>Email Address *</label>
                <input type="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                       class="<?= isset($errors['email'])?'error':'' ?>" placeholder="you@email.com" required>
                <?php if (isset($errors['email'])): ?><span class="field-error"><?= $errors['email'] ?></span><?php endif; ?>
              </div>
            </div>

            <div class="form-group">
              <label>E-Bike Brand / Model *</label>
              <input type="text" name="ebike_brand_model" value="<?= htmlspecialchars($old['ebike_brand_model'] ?? '') ?>"
                     class="<?= isset($errors['ebike_brand_model'])?'error':'' ?>" placeholder="e.g. Kyoto Surge X1" required>
              <?php if (isset($errors['ebike_brand_model'])): ?><span class="field-error"><?= $errors['ebike_brand_model'] ?></span><?php endif; ?>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Serial Number *</label>
                <input type="text" name="serial_no" value="<?= htmlspecialchars($old['serial_no'] ?? '') ?>"
                       class="<?= isset($errors['serial_no'])?'error':'' ?>" placeholder="Frame / serial number" required>
                <?php if (isset($errors['serial_no'])): ?><span class="field-error"><?= $errors['serial_no'] ?></span><?php endif; ?>
              </div>
              <div class="form-group">
                <label>Year Bought *</label>
                <input type="number" name="year_bought" value="<?= htmlspecialchars($old['year_bought'] ?? '') ?>"
                       class="<?= isset($errors['year_bought'])?'error':'' ?>" placeholder="e.g. 2023" min="2000" max="<?= date('Y') ?>" required>
                <?php if (isset($errors['year_bought'])): ?><span class="field-error"><?= $errors['year_bought'] ?></span><?php endif; ?>
              </div>
            </div>

            <div class="form-group">
              <label>Registration Type *</label>
              <select name="registration_type" class="<?= isset($errors['registration_type'])?'error':'' ?>" required>
                <option value="">— Select —</option>
                <?php foreach ($registrationTypes as $rt): ?>
                  <option value="<?= $rt ?>" <?= (($old['registration_type'] ?? '') === $rt) ? 'selected' : '' ?>><?= $rt ?></option>
                <?php endforeach; ?>
              </select>
              <?php if (isset($errors['registration_type'])): ?><span class="field-error"><?= $errors['registration_type'] ?></span><?php endif; ?>
            </div>

            <div class="form-group">
              <label>Documentation Picture *</label>
              <label class="upload-box" for="docPicture">
                <div>📎 Click to upload a clear picture of your documents</div>
                <div class="upload-hint">Accepted: JPG, JPEG, PNG · Max 3 MB · One picture for this prototype</div>
                <div class="upload-filename" id="docFilename"></div>
              </label>
              <input type="file" id="docPicture" name="doc_picture" accept=".jpg,.jpeg,.png,image/jpeg,image/png" required>
              <?php if (isset($errors['doc_picture'])): ?><span class="field-error"><?= $errors['doc_picture'] ?></span><?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:1rem" id="submitBtn">
              Submit Registration →
            </button>
            <p style="font-size:0.78rem;color:var(--gray);text-align:center;margin-top:0.75rem">🔒 Your data is handled securely.</p>

          </form>
        </div>

      <?php endif; ?>
    </div>

  </div>
</div>

<script>
document.getElementById('docPicture')?.addEventListener('change', function() {
  const label = document.getElementById('docFilename');
  label.textContent = this.files.length ? '✓ ' + this.files[0].name : '';
});

document.getElementById('regForm')?.addEventListener('submit', function() {
  const btn = document.getElementById('submitBtn');
  btn.textContent = 'Submitting…';
  btn.disabled = true;
});
</script>

<?php include 'includes/footer.php'; ?>
