<?php
require_once 'config.php';
checkLogin();

$message = '';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM partnerships WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: partnerships?success=deleted");
    exit;
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $id = $_POST['id'] ?? null;

    // Handle Logo Upload
    $logo_url = $_POST['existing_logo'] ?? '';
    if (isset($_FILES['partner_logo']) && $_FILES['partner_logo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../assets/uploads/partners/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $file_ext = pathinfo($_FILES['partner_logo']['name'], PATHINFO_EXTENSION);
        $file_name = 'partner_' . time() . '_' . uniqid() . '.' . $file_ext;
        $upload_path = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['partner_logo']['tmp_name'], $upload_path)) {
            $logo_url = 'assets/uploads/partners/' . $file_name;
        }
    }

    if ($id) {
        $stmt = $pdo->prepare("UPDATE partnerships SET name=?, logo_url=? WHERE id=?");
        $stmt->execute([$name, $logo_url, $id]);
        $message = "Partner updated successfully!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO partnerships (name, logo_url) VALUES (?, ?)");
        $stmt->execute([$name, $logo_url]);
        $message = "Partner added successfully!";
    }
}

$partners = $pdo->query("SELECT * FROM partnerships ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Partnerships | Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
    <style>
        :root { --blue: #1a56db; --orange: #f97316; --dark: #0f172a; --white: #ffffff; --gray-light: #f8fafc; --gray-border: #e2e8f0; --text-muted: #64748b; --sidebar-width: 280px; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Outfit', sans-serif; background: var(--gray-light); color: var(--dark); display: flex; }
        .sidebar { width: var(--sidebar-width); background: var(--white); height: 100vh; position: fixed; border-right: 1px solid var(--gray-border); padding: 2rem; display: flex; flex-direction: column; }
        .logo { font-family: 'Plus Jakarta Sans', sans-serif; font-size: 1.5rem; font-weight: 800; margin-bottom: 3rem; }
        .logo span { color: var(--orange); }
        .nav-links { list-style: none; flex-grow: 1; }
        .nav-links a { display: flex; align-items: center; gap: 12px; padding: 0.8rem 1.2rem; text-decoration: none; color: var(--text-muted); font-weight: 600; border-radius: 12px; }
        .nav-links a.active { background: rgba(26, 86, 219, 0.05); color: var(--blue); }
        .main-content { margin-left: var(--sidebar-width); flex-grow: 1; padding: 3rem; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .btn-add { background: var(--blue); color: white; padding: 0.8rem 1.5rem; border-radius: 12px; text-decoration: none; font-weight: 700; border: none; cursor: pointer; }
        .partner-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1.5rem; }
        .partner-card { background: var(--white); border-radius: 20px; border: 1px solid var(--gray-border); padding: 1.5rem; text-align: center; }
        .partner-logo-preview { height: 80px; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem; background: var(--gray-light); border-radius: 12px; overflow: hidden; }
        .partner-logo-preview img { max-width: 80%; max-height: 80%; object-fit: contain; }
        .partner-name { font-weight: 700; margin-bottom: 1rem; }
        .actions { display: flex; justify-content: center; gap: 15px; border-top: 1px solid var(--gray-border); padding-top: 1rem; }
        .btn-edit { color: var(--blue); text-decoration: none; font-weight: 700; font-size: 0.85rem; }
        .btn-delete { color: #ef4444; text-decoration: none; font-weight: 700; font-size: 0.85rem; }

        .modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; }
        .modal-content { background: white; padding: 2.5rem; border-radius: 24px; width: 100%; max-width: 450px; }
        .form-group { margin-bottom: 1.2rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.9rem; }
        .form-group input { width: 100%; padding: 0.8rem; border-radius: 10px; border: 1px solid var(--gray-border); font-family: inherit; }
        .image-preview { width: 100%; height: 120px; border: 2px dashed var(--gray-border); border-radius: 12px; margin-top: 10px; display: flex; align-items: center; justify-content: center; overflow: hidden; background: var(--gray-light); }
        .image-preview img { max-width: 90%; max-height: 90%; object-fit: contain; display: none; }
        .success-banner { background: #dcfce7; color: #10b981; padding: 1rem; border-radius: 12px; margin-bottom: 2rem; font-weight: 600; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">Marr<span>things</span></div>
        <ul class="nav-links">
            <li><a href="dashboard">Dashboard</a></li>
            <li><a href="projects">Projects</a></li>
            <li><a href="partnerships" class="active">Partnerships</a></li>
            <li><a href="reviews">Reviews</a></li>
            <li><a href="booking_settings">Booking Settings</a></li>
            <li><a href="bookings">Bookings</a></li>
            <li><a href="messages">Messages</a></li>
            <li><a href="profile">My Profile</a></li>
            <li><a href="logout" style="color:#ef4444">Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Trusted Partnerships</h1>
            <button class="btn-add" onclick="openModal()">+ Add New Partner</button>
        </div>

        <?php if ($message || isset($_GET['success'])): ?>
            <div class="success-banner"><?php echo $message ?: "Operation successful!"; ?></div>
        <?php endif; ?>

        <div class="partner-grid">
            <?php foreach($partners as $p): ?>
                <div class="partner-card">
                    <div class="partner-logo-preview">
                        <?php if($p['logo_url']): ?>
                            <img src="../<?php echo $p['logo_url']; ?>" alt="">
                        <?php else: ?>
                            <span style="font-weight: 800; color: var(--blue);"><?php echo htmlspecialchars($p['name']); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="partner-name"><?php echo htmlspecialchars($p['name']); ?></div>
                    <div class="actions">
                        <a href="javascript:void(0)" class="btn-edit" onclick='editPartner(<?php echo json_encode($p); ?>)'>Edit</a>
                        <a href="?delete=<?php echo $p['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure?')">Delete</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="modal" id="partnerModal">
        <div class="modal-content">
            <h2 id="modalTitle" style="margin-bottom: 2rem;">Add New Partner</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" id="partnerId">
                <input type="hidden" name="existing_logo" id="existingLogo">
                
                <div class="form-group">
                    <label>Partner Name</label>
                    <input type="text" name="name" id="partnerName" required>
                </div>
                
                <div class="form-group">
                    <label>Partner Logo (Optional)</label>
                    <input type="file" name="partner_logo" accept="image/*" onchange="previewImage(this)">
                    <div class="image-preview" id="imagePreview">
                        <span>No logo selected</span>
                        <img src="" id="previewImg">
                    </div>
                </div>

                <div style="display:flex; gap:10px; margin-top:2rem">
                    <button type="submit" class="btn-add" style="flex-grow:1">Save Partner</button>
                    <button type="button" class="btn-add" style="background:#eee; color:#333;" onclick="closeModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('partnerModal');
        const previewImg = document.getElementById('previewImg');
        const previewText = document.querySelector('#imagePreview span');

        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewImg.style.display = 'block';
                    previewText.style.display = 'none';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function openModal() {
            document.getElementById('modalTitle').innerText = "Add New Partner";
            document.getElementById('partnerId').value = "";
            document.getElementById('existingLogo').value = "";
            document.getElementById('partnerName').value = "";
            previewImg.style.display = 'none';
            previewText.style.display = 'block';
            modal.style.display = 'flex';
        }

        function closeModal() { modal.style.display = 'none'; }

        function editPartner(p) {
            document.getElementById('modalTitle').innerText = "Edit Partner";
            document.getElementById('partnerId').value = p.id;
            document.getElementById('existingLogo').value = p.logo_url;
            document.getElementById('partnerName').value = p.name;
            if(p.logo_url) {
                previewImg.src = '../' + p.logo_url;
                previewImg.style.display = 'block';
                previewText.style.display = 'none';
            } else {
                previewImg.style.display = 'none';
                previewText.style.display = 'block';
            }
            modal.style.display = 'flex';
        }

        window.onclick = function(event) { if (event.target == modal) closeModal(); }
    </script>
</body>
</html>
