# Update Summary — Essential Changes + Small Improvements

## Setup
1. Import `database.sql` fresh (it replaces the old table — back up first if you
   have real data in the old schema).
2. Make sure the `uploads/` folder is writable by the web server
   (`chmod 755 uploads` on Linux; on XAMPP/Windows this is usually fine by default).
3. Everything else runs the same way as before (XAMPP, `http://localhost/TOER/`).

## 1. Registration Form (`register.php`)
Now collects: Applicant Name, Address/Barangay, Contact Number, Email,
E-Bike Brand/Model, Serial Number, Year Bought, Registration Type
(New Registration / Renewal / Transfer of Ownership).

## 2. Documentation Picture Upload
One required file uploader on the registration form. Accepts JPG/JPEG/PNG,
capped at 3 MB, verified with `getimagesize()` so a renamed non-image file is
rejected. Files are saved to `/uploads` with a randomized filename
(`doc_<random>.jpg`) — the original filename is never trusted or stored.
`/uploads/.htaccess` disables PHP execution in that folder as a safety measure.

## 3. Reference Number
Auto-generated after insert: `EB-<year>-<5-digit id>`, e.g. `EB-2026-00001`.
Note: numbering runs off the database's auto-increment id rather than resetting
every calendar year — a reasonable simplification for a prototype.

## 4. Check Application Status (`check_status.php`)
Now looks up by **reference number** instead of phone number. Shows
🟡 Pending / 🟢 Approved / 🔴 Rejected and the admin's remark when set.
Approved applications get a "View Certificate" button.

## 5. Admin Panel (`admin/index.php`, `admin/edit.php`)
- Table now shows Reference No., Applicant, E-Bike, Serial No., Submitted, Status.
- "View" opens a modal with the full applicant/e-bike info **and the
  documentation picture**, plus the approve/reject + remark controls
  (this also fixes a broken/truncated button in the original file).
- "Edit" lets the admin correct any field and optionally replace the
  documentation picture.
- Deleting a registration also deletes its uploaded picture from disk.

## 6. Digital Registration Certificate (`certificate.php`, new file)
Look up by reference number; only renders for **Approved** applications.
Shows applicant name, e-bike details, reference number, approval date, and
status. Has a print/Save-as-PDF button with print-friendly styling.

## 7–8. Database & Validation
- New columns: `reference_no`, `doc_picture`, `address`, `ebike_brand_model`,
  `registration_type` (see `database.sql`).
- All required fields are validated server-side (name, address, contact,
  email format, brand/model, serial number, year, registration type).
- Duplicate serial numbers are rejected on both add and edit.
- Uploaded files are restricted to JPG/JPEG/PNG and 3 MB max.

## 9. Homepage Wording (`index.php`)
Removed the "No fees, no paperwork" line (inaccurate — applicants now upload
document pictures) and the "24/7 ... no paperwork" framing. Wording now
focuses on online submission, uploading documents, getting a reference
number, and tracking status — plus a couple of small copy fixes
(a truncated "Tantiado's o" eyebrow label, and the old
`EB-000001` placeholder updated to the new `EB-2026-00001` format).
