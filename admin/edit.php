<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$pageTitle = 'Edit Registration — Tantiado Online E-bike Registration Admin';

include '../includes/db.php';

$registrationTypes = ['New Registration', 'Renewal', 'Transfer of Ownership'];

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: index.php'); exit; }

$res = $conn->query("SELECT * FROM registrations WHERE id=$id");
if (!$res || $res->num_rows === 0) { header('Location: index.php'); exit; }
$reg = $res->fetch_assoc();

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = ['applicant_name','address','contact_number','email','ebike_brand_model','serial_no','year_bought','registration_type','admin_response','status'];
    $data   = [];
    foreach ($fields as $f) {
        $data[$f] = trim($conn->real_escape_string($_POST[$f] ?? ''));
    }

    if (!$data['applicant_name'])    $errors['applicant_name']    = 'Required.';
    if (!$data['address'])           $errors['address']           = 'Required.';
    if (!$data['contact_number'])    $errors['contact_number']    = 'Required.';
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Invalid email.';
    if (!$data['ebike_brand_model']) $errors['ebike_brand_model'] = 'Required.';
    if (!$data['serial_no'])         $errors['serial_no']         = 'Required.';
    if (!in_array($data['registration_type'], $registrationTypes, true)) $errors['registration_type'] = 'Invalid type.';
    if (!in_array($data['status'], ['Pending','Approved','Rejected']))   $errors['status']             = 'Invalid status.';

    if (empty($errors['serial_no'])) {
        $chk = $conn->query("SELECT id FROM registrations WHERE serial_no='{$data['serial_no']}' AND id != $id");
        if ($chk && $chk->num_rows > 0) $errors['serial_no'] = 'This serial number belongs to another registration.';
    }

    // Optional: replace the documentation picture
    $newDocFilename = null;
    if (isset($_FILES['doc_picture']) && $_FILES['doc_picture']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['doc_picture'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors['doc_picture'] = 'There was a problem uploading that file.';
        } else {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if ($file['size'] > UPLOAD_MAX_BYTES) {
                $errors['doc_picture'] = 'File is too large. Maximum size is 3 MB.';
            } elseif (!in_array($ext, UPLOAD_ALLOWED_EXT, true)) {
                $errors['doc_picture'] = 'Only JPG, JPEG, or PNG files are accepted.';
            } else {
                $imgInfo = @getimagesize($file['tmp_name']);
                if ($imgInfo === false || !in_array($imgInfo['mime'], UPLOAD_ALLOWED_MIME, true)) {
                    $errors['doc_picture'] = 'That file does not look like a valid image.';
                } else {
                    $newDocFilename = 'doc_' . bin2hex(random_bytes(8)) . '.' . $ext;
                }
            }
        }
    }

    if (empty($errors)) {
        if ($newDocFilename) {
            if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
            move_uploaded_file($_FILES['doc_picture']['tmp_name'], UPLOAD_DIR . $newDocFilename);
            $oldPath = UPLOAD_DIR . $reg['doc_picture'];
            if ($reg['doc_picture'] && file_exists($oldPath)) @unlink($oldPath);
            $data['doc_picture'] = $newDocFilename;
        } else {
            $data['doc_picture'] = $reg['doc_picture'];
        }

        $sql = "UPDATE registrations SET
            applicant_name='{$data['applicant_name']}', address='{$data['address']}',
            contact_number='{$data['contact_number']}', email='{$data['email']}',
            ebike_brand_model='{$data['ebike_brand_model']}', serial_no='{$data['serial_no']}',
            year_bought='{$data['year_bought']}', registration_type='{$data['registration_type']}',
            doc_picture='{$data['doc_picture']}',
            admin_response='{$data['admin_response']}', status='{$data['status']}'
            WHERE id=$id";
        if ($conn->query($sql)) {
            $success = true;
            $reg = array_merge($reg, $data);
        }
    }
}

include '../includes/header.php';
?>

<style>
.edit-wrap { max-width:760px; margin:0 auto; padding:6.5rem 0 5rem; }
.edit-wrap h2 { font-family:'Bebas Neue',sans-serif; font-size:2.4rem; letter-spacing:2px; margin-bottom:0.5rem; }
.back-link { font-size:0.85rem; color:var(--gray); text-decoration:none; display:inline-flex; align-items:center; gap:0.4rem; margin-bottom:1.5rem; }
.back-link:hover { color:var(--white); }
.response-hint { font-size:0.78rem; color:var(--gray); margin-top:0.25rem; }
.current-doc { display:flex; align-items:center; gap:1rem; margin-bottom:0.75rem; }
.current-doc img { width:90px; height:90px; object-fit:cover; border-radius:6px; border:1px solid rgba(255,255,255,0.1); }
</style>

<div class="container">
  <div class="edit-wrap">

    <a href="index.php" class="back-link">← Back to Admin</a>
    <div class="section-label">Admin — Edit Record</div>
    <h2>EDIT REGISTRATION <span class="text-green"><?= htmlspecialchars($reg['reference_no']) ?></span></h2>

    <?php if ($success): ?>
      <div class="alert alert-success">✅ Registration updated successfully.</div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
      <div class="alert alert-error">⚠️ Please fix the errors below.</div>
    <?php endif; ?>

    <div class="card" style="margin-top:1.5rem">
      <form method="POST" enctype="multipart/form-data">

        <div class="form-group">
          <label>Applicant Name *</label>
          <input type="text" name="applicant_name" value="<?= htmlspecialchars($reg['applicant_name']) ?>"
                 class="<?= isset($errors['applicant_name'])?'error':'' ?>" required>
          <?php if(isset($errors['applicant_name'])): ?><span class="field-error"><?= $errors['applicant_name'] ?></span><?php endif; ?>
        </div>

        <div class="form-group">
          <label>Address / Barangay *</label>
          <input type="text" name="address" value="<?= htmlspecialchars($reg['address']) ?>"
                 class="<?= isset($errors['address'])?'error':'' ?>" required>
          <?php if(isset($errors['address'])): ?><span class="field-error"><?= $errors['address'] ?></span><?php endif; ?>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Contact Number *</label>
            <input type="tel" name="contact_number" value="<?= htmlspecialchars($reg['contact_number']) ?>"
                   class="<?= isset($errors['contact_number'])?'error':'' ?>" required>
            <?php if(isset($errors['contact_number'])): ?><span class="field-error"><?= $errors['contact_number'] ?></span><?php endif; ?>
          </div>
          <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" value="<?= htmlspecialchars($reg['email']) ?>"
                   class="<?= isset($errors['email'])?'error':'' ?>" required>
            <?php if(isset($errors['email'])): ?><span class="field-error"><?= $errors['email'] ?></span><?php endif; ?>
          </div>
        </div>

        <div class="form-group">
          <label>E-Bike Brand / Model *</label>
          <input type="text" name="ebike_brand_model" value="<?= htmlspecialchars($reg['ebike_brand_model']) ?>"
                 class="<?= isset($errors['ebike_brand_model'])?'error':'' ?>" required>
          <?php if(isset($errors['ebike_brand_model'])): ?><span class="field-error"><?= $errors['ebike_brand_model'] ?></span><?php endif; ?>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Serial Number *</label>
            <input type="text" name="serial_no" value="<?= htmlspecialchars($reg['serial_no']) ?>"
                   class="<?= isset($errors['serial_no'])?'error':'' ?>" required>
            <?php if(isset($errors['serial_no'])): ?><span class="field-error"><?= $errors['serial_no'] ?></span><?php endif; ?>
          </div>
          <div class="form-group">
            <label>Year Bought</label>
            <input type="number" name="year_bought" value="<?= htmlspecialchars($reg['year_bought']) ?>" min="2000" max="<?= date('Y') ?>">
          </div>
        </div>

        <div class="form-group">
          <label>Registration Type *</label>
          <select name="registration_type">
            <?php foreach($registrationTypes as $rt): ?>
              <option value="<?= $rt ?>" <?= $reg['registration_type']===$rt?'selected':'' ?>><?= $rt ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label>Documentation Picture</label>
          <div class="current-doc">
            <img src="../uploads/<?= htmlspecialchars($reg['doc_picture']) ?>" alt="Current documentation picture">
            <span style="font-size:0.82rem;color:var(--gray)">Current file. Upload a new one below to replace it.</span>
          </div>
          <input type="file" name="doc_picture" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
          <?php if(isset($errors['doc_picture'])): ?><span class="field-error"><?= $errors['doc_picture'] ?></span><?php endif; ?>
        </div>

        <div class="form-group">
          <label>Registration Status</label>
          <select name="status" id="statusSelect" onchange="toggleResponse(this.value)">
            <?php foreach(['Pending','Approved','Rejected'] as $s): ?>
              <option value="<?= $s ?>" <?= $reg['status']===$s?'selected':'' ?>><?= $s ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group" id="responseGroup">
          <label id="responseLabel">
            <?= $reg['status'] === 'Rejected' ? '🔴 Reason for Rejection' : '🟢 Remark to Applicant' ?>
          </label>
          <textarea name="admin_response" id="adminResponse" style="min-height:100px"
            placeholder="<?= $reg['status'] === 'Pending' ? 'Set status to Approved or Rejected to add a remark…' : 'Type a short remark for the applicant…' ?>"
            <?= $reg['status'] === 'Pending' ? 'disabled' : '' ?>
          ><?= htmlspecialchars($reg['admin_response'] ?? '') ?></textarea>
          <span class="response-hint">This remark is shown to the applicant on the Check Status page.</span>
        </div>

        <div style="display:flex;gap:1rem;margin-top:0.5rem;flex-wrap:wrap">
          <button type="submit" class="btn btn-primary" style="flex:1;justify-content:center">Save Changes</button>
          <a href="index.php" class="btn btn-outline">Cancel</a>
        </div>

      </form>
    </div>

  </div>
</div>

<script>
function toggleResponse(status) {
  const textarea = document.getElementById('adminResponse');
  const label    = document.getElementById('responseLabel');
  if (status === 'Pending') {
    textarea.disabled    = true;
    textarea.placeholder = 'Set status to Approved or Rejected to add a remark…';
    label.textContent    = 'Admin Remark';
  } else {
    textarea.disabled    = false;
    textarea.placeholder = 'Type a short remark for the applicant…';
    label.textContent    = status === 'Approved' ? '🟢 Remark to Applicant' : '🔴 Reason for Rejection';
  }
}
toggleResponse(document.getElementById('statusSelect').value);
</script>

<?php include '../includes/footer.php'; ?>
