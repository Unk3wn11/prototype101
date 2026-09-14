<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$pageTitle = 'Admin Dashboard — Tantiado Online E-bike Registration';

include '../includes/db.php';

// Handle delete
if (isset($_GET['delete'])) {
    $did = (int)$_GET['delete'];
    $res = $conn->query("SELECT doc_picture FROM registrations WHERE id=$did");
    if ($res && $row = $res->fetch_assoc()) {
        $path = UPLOAD_DIR . $row['doc_picture'];
        if ($row['doc_picture'] && file_exists($path)) @unlink($path);
    }
    $conn->query("DELETE FROM registrations WHERE id=$did");
    header('Location: index.php?msg=deleted');
    exit;
}

// Handle status update (from view/review modal)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $id       = (int)($_POST['id'] ?? 0);
    $status   = in_array($_POST['status'] ?? '', ['Pending','Approved','Rejected']) ? $_POST['status'] : 'Pending';
    $response = $conn->real_escape_string(trim($_POST['admin_response'] ?? ''));
    $conn->query("UPDATE registrations SET status='$status', admin_response='$response' WHERE id=$id");
    header('Location: index.php?msg=updated');
    exit;
}

$msg = $_GET['msg'] ?? '';

// Filters
$search        = trim($conn->real_escape_string($_GET['search'] ?? ''));
$filter_status = $_GET['status'] ?? '';
$page          = max(1, (int)($_GET['page'] ?? 1));
$per_page      = 10;
$offset        = ($page - 1) * $per_page;

$where = '1=1';
if ($search)        $where .= " AND (applicant_name LIKE '%$search%' OR email LIKE '%$search%' OR contact_number LIKE '%$search%' OR serial_no LIKE '%$search%' OR reference_no LIKE '%$search%')";
if ($filter_status && in_array($filter_status, ['Pending','Approved','Rejected']))
                    $where .= " AND status='$filter_status'";

$total_res = $conn->query("SELECT COUNT(*) as c FROM registrations WHERE $where");
$total     = (int)$total_res->fetch_assoc()['c'];
$pages     = max(1, (int)ceil($total / $per_page));

$rows = $conn->query("SELECT * FROM registrations WHERE $where ORDER BY created_at DESC LIMIT $per_page OFFSET $offset");

// Counts
$counts = ['Total' => 0];
foreach (['Pending','Approved','Rejected'] as $s) {
    $r = $conn->query("SELECT COUNT(*) as c FROM registrations WHERE status='$s'");
    $counts[$s] = (int)$r->fetch_assoc()['c'];
    $counts['Total'] += $counts[$s];
}

include '../includes/header.php';
?>

<style>
.admin-wrap { padding: 5.5rem 0 4rem; }
.summary-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; margin-bottom:2rem; }
.summary-card { background:var(--dark2); border:1px solid rgba(255,255,255,0.08); border-radius:8px; padding:1.25rem 1.5rem; }
.summary-card .s-num { font-family:'Bebas Neue',sans-serif; font-size:2.2rem; line-height:1; }
.summary-card .s-label { font-size:0.75rem; color:var(--gray); text-transform:uppercase; letter-spacing:1px; margin-top:0.2rem; }
.toolbar { display:flex; align-items:center; gap:1rem; margin-bottom:1.5rem; flex-wrap:wrap; }
.toolbar input[type=text] {
  background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1);
  border-radius:6px; padding:0.6rem 1rem; color:var(--white);
  font-family:'DM Sans',sans-serif; font-size:0.9rem; outline:none;
  flex:1; min-width:200px; max-width:320px;
}
.toolbar input[type=text]:focus { border-color:var(--green); }
.toolbar select {
  background:var(--dark3); border:1px solid rgba(255,255,255,0.1);
  border-radius:6px; padding:0.6rem 1rem; color:var(--white);
  font-family:'DM Sans',sans-serif; font-size:0.9rem; outline:none;
}
.action-btns { display:flex; gap:0.4rem; flex-wrap:wrap; }
.pagination { display:flex; gap:0.4rem; margin-top:1.5rem; flex-wrap:wrap; }
.pagination a, .pagination span {
  padding:0.45rem 0.85rem; border-radius:5px; font-size:0.85rem;
  border:1px solid rgba(255,255,255,0.1); color:var(--gray-light);
  text-decoration:none; transition:all 0.2s;
}
.pagination a:hover { border-color:var(--green); color:var(--green); }
.pagination .active { background:var(--green); color:var(--dark); border-color:var(--green); font-weight:700; }
.empty-state { text-align:center; padding:4rem 2rem; color:var(--gray); }
.view-grid { display:grid; grid-template-columns:1fr 1fr; gap:0.9rem; margin-bottom:1.25rem; }
.view-item { display:flex; flex-direction:column; gap:0.15rem; }
.view-item.full { grid-column:1/-1; }
.view-label { font-size:0.68rem; color:var(--gray); text-transform:uppercase; letter-spacing:1px; }
.view-value { font-size:0.9rem; }
.doc-preview { width:100%; max-height:320px; object-fit:contain; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:var(--dark3); margin-bottom:1.25rem; }
.doc-preview-link { display:block; margin-bottom:1.25rem; }
@media(max-width:768px){ .summary-grid{grid-template-columns:repeat(2,1fr);} .view-grid{grid-template-columns:1fr;} }
</style>

<div class="container">
  <div class="admin-wrap">

    <div style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:1rem;margin-bottom:2rem">
      <div>
        <div class="section-label">Admin Panel</div>
        <h2 class="section-title" style="font-size:2.2rem">REGISTRATIONS</h2>
      </div>
      <div style="display:flex;gap:0.75rem">
        <a href="../register.php" class="btn btn-primary">+ New Registration</a>
        <a href="logout.php" class="btn btn-outline" style="color:var(--danger);border-color:var(--danger)">Logout</a>
      </div>
    </div>

    <?php if ($msg === 'updated'): ?>
      <div class="alert alert-success">✅ Status updated successfully.</div>
    <?php elseif ($msg === 'deleted'): ?>
      <div class="alert alert-error">🗑️ Registration deleted.</div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="summary-grid">
      <div class="summary-card"><div class="s-num"><?= $counts['Total'] ?></div><div class="s-label">Total</div></div>
      <div class="summary-card"><div class="s-num" style="color:var(--warning)"><?= $counts['Pending'] ?></div><div class="s-label">🟡 Pending</div></div>
      <div class="summary-card"><div class="s-num" style="color:var(--green)"><?= $counts['Approved'] ?></div><div class="s-label">🟢 Approved</div></div>
      <div class="summary-card"><div class="s-num" style="color:var(--danger)"><?= $counts['Rejected'] ?></div><div class="s-label">🔴 Rejected</div></div>
    </div>

    <!-- Filter toolbar -->
    <form method="GET" action="index.php">
      <div class="toolbar">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="🔍  Search name, email, serial, reference…">
        <select name="status">
          <option value="">All Statuses</option>
          <?php foreach(['Pending','Approved','Rejected'] as $s): ?>
            <option value="<?= $s ?>" <?= $filter_status===$s?'selected':'' ?>><?= $s ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-outline">Filter</button>
        <?php if ($search || $filter_status): ?>
          <a href="index.php" class="btn btn-outline" style="color:var(--danger);border-color:var(--danger)">✕ Clear</a>
        <?php endif; ?>
      </div>
    </form>

    <!-- Table -->
    <?php if ($rows && $rows->num_rows > 0): ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Reference No.</th>
            <th>Applicant</th>
            <th>E-Bike</th>
            <th>Serial No.</th>
            <th>Submitted</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $rows->fetch_assoc()): ?>
          <tr>
            <td style="color:var(--gray);font-size:0.8rem;font-family:monospace"><?= htmlspecialchars($row['reference_no']) ?></td>
            <td><strong><?= htmlspecialchars($row['applicant_name']) ?></strong></td>
            <td style="font-size:0.85rem"><?= htmlspecialchars($row['ebike_brand_model']) ?></td>
            <td style="font-family:monospace;font-size:0.82rem"><?= htmlspecialchars($row['serial_no']) ?></td>
            <td style="font-size:0.82rem;color:var(--gray)"><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
            <td>
              <span class="badge badge-<?= strtolower($row['status']) ?>">
                <?php if ($row['status'] === 'Pending'): ?>🟡 Pending
                <?php elseif ($row['status'] === 'Approved'): ?>🟢 Approved
                <?php else: ?>🔴 Rejected<?php endif; ?>
              </span>
            </td>
            <td>
              <div class="action-btns">
                <button type="button" class="btn btn-sm btn-outline"
                  onclick='openView(<?= json_encode([
                    "id"                => $row["id"],
                    "reference_no"      => $row["reference_no"],
                    "applicant_name"    => $row["applicant_name"],
                    "address"           => $row["address"],
                    "contact_number"    => $row["contact_number"],
                    "email"             => $row["email"],
                    "ebike_brand_model" => $row["ebike_brand_model"],
                    "serial_no"         => $row["serial_no"],
                    "year_bought"       => $row["year_bought"],
                    "registration_type" => $row["registration_type"],
                    "doc_picture"       => $row["doc_picture"],
                    "status"            => $row["status"],
                    "admin_response"    => $row["admin_response"],
                    "created_at"        => date("M d, Y", strtotime($row["created_at"])),
                  ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>👁️ View</button>
                <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">✏️ Edit</a>
                <a href="index.php?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger"
                   onclick="return confirm('Delete <?= htmlspecialchars($row['reference_no']) ?>? This cannot be undone.')">🗑️</a>
              </div>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>

    <?php if ($pages > 1): ?>
    <div class="pagination">
      <?php for ($p = 1; $p <= $pages; $p++): ?>
        <?php $q = http_build_query(['search'=>$search,'status'=>$filter_status,'page'=>$p]); ?>
        <?php if ($p === $page): ?>
          <span class="active"><?= $p ?></span>
        <?php else: ?>
          <a href="index.php?<?= $q ?>"><?= $p ?></a>
        <?php endif; ?>
      <?php endfor; ?>
    </div>
    <?php endif; ?>

    <p style="font-size:0.8rem;color:var(--gray);margin-top:0.75rem">
      Showing <?= min($per_page, $total - $offset) ?> of <?= $total ?> registrations
    </p>

    <?php else: ?>
    <div class="empty-state">
      <p style="font-size:2rem">📋</p>
      <p style="font-size:1rem;margin-top:0.5rem">No registrations found<?= $search ? " for \"$search\"" : '' ?>.</p>
      <?php if ($search || $filter_status): ?>
        <a href="index.php" class="btn btn-outline" style="margin-top:1rem">Clear Filters</a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

  </div>
</div>

<!-- View / Review Modal -->
<div class="modal-overlay" id="viewModal">
  <div class="modal" style="max-width:560px">
    <div class="modal-header">
      <h3>Review Application</h3>
      <button type="button" class="modal-close" onclick="closeView()">✕</button>
    </div>

    <div class="view-grid">
      <div class="view-item"><span class="view-label">Reference No.</span><span class="view-value" id="vRef" style="font-family:monospace"></span></div>
      <div class="view-item"><span class="view-label">Submitted</span><span class="view-value" id="vDate"></span></div>
      <div class="view-item"><span class="view-label">Applicant Name</span><span class="view-value" id="vName"></span></div>
      <div class="view-item"><span class="view-label">Registration Type</span><span class="view-value" id="vType"></span></div>
      <div class="view-item"><span class="view-label">Contact Number</span><span class="view-value" id="vPhone"></span></div>
      <div class="view-item"><span class="view-label">Email</span><span class="view-value" id="vEmail"></span></div>
      <div class="view-item"><span class="view-label">E-Bike Brand / Model</span><span class="view-value" id="vBike"></span></div>
      <div class="view-item"><span class="view-label">Serial / Year</span><span class="view-value" id="vSerial"></span></div>
      <div class="view-item full"><span class="view-label">Address / Barangay</span><span class="view-value" id="vAddress"></span></div>
    </div>

    <div class="view-label" style="margin-bottom:0.4rem">Documentation Picture</div>
    <a id="vDocLink" class="doc-preview-link" href="#" target="_blank" rel="noopener">
      <img id="vDocImg" class="doc-preview" src="" alt="Documentation picture">
    </a>

    <form method="POST" action="index.php">
      <input type="hidden" name="action" value="update_status">
      <input type="hidden" name="id" id="editId">
      <div class="form-group">
        <label>Registration Status</label>
        <select name="status" id="editStatus" onchange="toggleResponse(this.value)">
          <option value="Pending">🟡 Pending</option>
          <option value="Approved">🟢 Approved</option>
          <option value="Rejected">🔴 Rejected</option>
        </select>
      </div>
      <div class="form-group" id="responseGroup" style="display:none">
        <label id="responseLabel">Admin Remark</label>
        <textarea name="admin_response" id="editResponse" placeholder="Type a short remark for the applicant…" style="min-height:90px"></textarea>
        <span style="font-size:0.75rem;color:var(--gray)">This remark is shown to the applicant on the Check Status page.</span>
      </div>
      <div style="display:flex;gap:0.75rem;margin-top:1.25rem">
        <button type="submit" class="btn btn-primary" style="flex:1;justify-content:center">Save Decision</button>
        <button type="button" class="btn btn-outline" onclick="closeView()">Close</button>
      </div>
    </form>
  </div>
</div>

<script>
function toggleResponse(status) {
  var group = document.getElementById('responseGroup');
  var label = document.getElementById('responseLabel');
  if (status === 'Approved' || status === 'Rejected') {
    group.style.display = 'block';
    label.textContent = status === 'Approved' ? '🟢 Message to Applicant' : '🔴 Reason for Rejection';
    document.getElementById('editResponse').disabled = false;
  } else {
    group.style.display = 'none';
    document.getElementById('editResponse').disabled = true;
  }
}

function openView(r) {
  document.getElementById('vRef').textContent     = r.reference_no;
  document.getElementById('vDate').textContent    = r.created_at;
  document.getElementById('vName').textContent    = r.applicant_name;
  document.getElementById('vType').textContent    = r.registration_type;
  document.getElementById('vPhone').textContent   = r.contact_number;
  document.getElementById('vEmail').textContent   = r.email;
  document.getElementById('vBike').textContent    = r.ebike_brand_model;
  document.getElementById('vSerial').textContent  = r.serial_no + ' · ' + r.year_bought;
  document.getElementById('vAddress').textContent = r.address;

  var docUrl = '../uploads/' + r.doc_picture;
  document.getElementById('vDocImg').src  = docUrl;
  document.getElementById('vDocLink').href = docUrl;

  document.getElementById('editId').value       = r.id;
  document.getElementById('editStatus').value   = r.status;
  document.getElementById('editResponse').value = r.admin_response || '';
  toggleResponse(r.status);

  document.getElementById('viewModal').style.display = 'flex';
}

function closeView() {
  document.getElementById('viewModal').style.display = 'none';
}

window.addEventListener('DOMContentLoaded', function() {
  document.getElementById('viewModal').style.display = 'none';
  document.getElementById('viewModal').addEventListener('click', function(e) {
    if (e.target === this) closeView();
  });
});
</script>

<?php include '../includes/footer.php'; ?>
