<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Change to your MySQL username
define('DB_PASS', '');           // Change to your MySQL password
define('DB_NAME', 'ebike_registry');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die('<div style="font-family:sans-serif;padding:2rem;color:#e74c3c">
        <strong>Database connection failed:</strong> ' . htmlspecialchars($conn->connect_error) . '
        <br><br>Make sure XAMPP MySQL is running and the database <code>ebike_registry</code> exists.
    </div>');
}

$conn->set_charset('utf8mb4');

// Where documentation pictures are stored on disk, and the public folder name.
define('UPLOAD_DIR_NAME', 'uploads');
define('UPLOAD_DIR', __DIR__ . '/../' . UPLOAD_DIR_NAME . '/');
define('UPLOAD_MAX_BYTES', 3 * 1024 * 1024); // 3 MB
define('UPLOAD_ALLOWED_EXT', ['jpg', 'jpeg', 'png']);
define('UPLOAD_ALLOWED_MIME', ['image/jpeg', 'image/png']);

/**
 * Generate the next reference number in the format EB-<year>-00001.
 * Numbering runs off the primary key, so it keeps climbing across years
 * (a prototype-level simplification — a production system would reset
 * the counter every calendar year).
 */
function generate_reference_no($conn, $id) {
    return 'EB-' . date('Y') . '-' . str_pad($id, 5, '0', STR_PAD_LEFT);
}
?>
