<?php
require_once 'config.php';
checkLogin();

$message = '';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: reviews?success=deleted");
    exit;
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $role = $_POST['role'];
    $content = $_POST['content'];
    $rating = $_POST['rating'];
    $id = $_POST['id'] ?? null;

    // Handle Image Upload
    $image_url = $_POST['existing_image'] ?? '';
    if (isset($_FILES['review_image']) && $_FILES['review_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../assets/uploads/reviews/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $file_ext = pathinfo($_FILES['review_image']['name'], PATHINFO_EXTENSION);
        $file_name = 'review_' . time() . '_' . uniqid() . '.' . $file_ext;
        $upload_path = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['review_image']['tmp_name'], $upload_path)) {
            $image_url = 'assets/uploads/reviews/' . $file_name;
        }
    }

    if ($id) {
        $stmt = $pdo->prepare("UPDATE reviews SET name=?, role=?, content=?, rating=?, image_url=? WHERE id=?");
        $stmt->execute([$name, $role, $content, $rating, $image_url, $id]);
        $message = "Review updated successfully!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO reviews (name, role, content, rating, image_url) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $role, $content, $rating, $image_url]);
        $message = "Review added successfully!";
    }
}

$reviews = $pdo->query("SELECT * FROM reviews ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reviews | Admin Panel</title>
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
        .review-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; }
        .review-card { background: var(--white); border-radius: 20px; border: 1px solid var(--gray-border); padding: 1.5rem; }
        .rating { color: var(--orange); margin-bottom: 1rem; }
        .review-text { font-style: italic; color: var(--text-muted); margin-bottom: 1.5rem; font-size: 0.9rem; line-height: 1.6; }
        .author-info { display: flex; align-items: center; gap: 12px; }
        .author-avatar { width: 40px; height: 40px; border-radius: 50%; background: var(--blue); color: white; display: flex; align-items: center; justify-content: center; font-weight: 800; overflow: hidden; }
        .author-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .actions { margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--gray-border); display: flex; gap: 15px; }
        .btn-edit { color: var(--blue); text-decoration: none; font-weight: 700; font-size: 0.85rem; }
        .btn-delete { color: #ef4444; text-decoration: none; font-weight: 700; font-size: 0.85rem; }

        .modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; }
        .modal-content { background: white; padding: 2.5rem; border-radius: 24px; width: 100%; max-width: 500px; max-height: 90vh; overflow-y: auto; }
        .form-group { margin-bottom: 1.2rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.9rem; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 0.8rem; border-radius: 10px; border: 1px solid var(--gray-border); font-family: inherit; }
        .image-preview { width: 100px; height: 100px; border-radius: 50%; border: 2px dashed var(--gray-border); margin: 10px auto; display: flex; align-items: center; justify-content: center; overflow: hidden; background: var(--gray-light); }
        .image-preview img { width: 100%; height: 100%; object-fit: cover; display: none; }
        .success-banner { background: #dcfce7; color: #10b981; padding: 1rem; border-radius: 12px; margin-bottom: 2rem; font-weight: 600; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">Marr<span>things</span></div>
        <ul class="nav-links">
            <li><a href="dashboard">Dashboard</a></li>
            <li><a href="projects">Projects</a></li>
            <li><a href="partnerships">Partnerships</a></li>
            <li><a href="reviews" class="active">Reviews</a></li>
            <li><a href="booking_settings">Booking Settings</a></li>
            <li><a href="bookings">Bookings</a></li>
            <li><a href="messages">Messages</a></li>
            <li><a href="profile">My Profile</a></li>
            <li><a href="logout" style="color:#ef4444">Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Manage Reviews</h1>
            <button class="btn-add" onclick="openModal()">+ Add New Review</button>
        </div>

        <?php if ($message || isset($_GET['success'])): ?>
            <div class="success-banner"><?php echo $message ?: "Operation successful!"; ?></div>
        <?php endif; ?>

        <div class="review-grid">
            <?php foreach($reviews as $r): ?>
                <div class="review-card">
                    <div class="rating"><?php echo str_repeat('★', $r['rating']); ?></div>
                    <p class="review-text">"<?php echo htmlspecialchars($r['content']); ?>"</p>
                    <div class="author-info">
                        <div class="author-avatar">
                            <?php if($r['image_url']): ?>
                                <img src="../<?php echo $r['image_url']; ?>" alt="">
                            <?php else: ?>
                                <?php 
                                    $names = explode(' ', $r['name']);
                                    echo strtoupper(substr($names[0], 0, 1) . (isset($names[1]) ? substr($names[1], 0, 1) : ''));
                                ?>
                            <?php endif; ?>
                        </div>
                        <div>
                            <h4 style="font-size: 0.9rem;"><?php echo htmlspecialchars($r['name']); ?></h4>
                            <p style="font-size: 0.7rem; color: var(--text-muted);"><?php echo htmlspecialchars($r['role']); ?></p>
                        </div>
                    </div>
                    <div class="actions">
                        <a href="javascript:void(0)" class="btn-edit" onclick='editReview(<?php echo htmlspecialchars(json_encode($r), ENT_QUOTES, "UTF-8"); ?>)'>Edit</a>
                        <a href="?delete=<?php echo $r['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure?')">Delete</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="modal" id="reviewModal">
        <div class="modal-content">
            <h2 id="modalTitle" style="margin-bottom: 2rem;">Add New Review</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" id="reviewId">
                <input type="hidden" name="existing_image" id="existingImage">

                <div class="form-group">
                    <label>Client Photo (Optional)</label>
                    <input type="file" name="review_image" accept="image/*" onchange="previewImage(this)">
                    <div class="image-preview" id="imagePreview">
                        <span style="font-size: 0.7rem; color: var(--text-muted);">Optional</span>
                        <img src="" id="previewImg">
                    </div>
                </div>

                <div class="form-group">
                    <label>Client Name</label>
                    <input type="text" name="name" id="reviewName" required>
                </div>
                <div class="form-group">
                    <label>Client Role / Company</label>
                    <input type="text" name="role" id="reviewRole" placeholder="e.g. CEO, TechFlow" required>
                </div>
                <div class="form-group">
                    <label>Rating</label>
                    <select name="rating" id="reviewRating">
                        <option value="5">5 Stars</option>
                        <option value="4">4 Stars</option>
                        <option value="3">3 Stars</option>
                        <option value="2">2 Stars</option>
                        <option value="1">1 Star</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Review Content</label>
                    <textarea name="content" id="reviewContent" rows="4" required></textarea>
                </div>
                <div style="display:flex; gap:10px; margin-top:2rem">
                    <button type="submit" class="btn-add" style="flex-grow:1">Save Review</button>
                    <button type="button" class="btn-add" style="background:#eee; color:#333;" onclick="closeModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('reviewModal');
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
            document.getElementById('modalTitle').innerText = "Add New Review";
            document.getElementById('reviewId').value = "";
            document.getElementById('existingImage').value = "";
            document.getElementById('reviewName').value = "";
            document.getElementById('reviewRole').value = "";
            document.getElementById('reviewRating').value = "5";
            document.getElementById('reviewContent').value = "";
            previewImg.style.display = 'none';
            previewText.style.display = 'block';
            modal.style.display = 'flex';
        }

        function closeModal() { modal.style.display = 'none'; }

        function editReview(r) {
            document.getElementById('modalTitle').innerText = "Edit Review";
            document.getElementById('reviewId').value = r.id;
            document.getElementById('existingImage').value = r.image_url;
            document.getElementById('reviewName').value = r.name;
            document.getElementById('reviewRole').value = r.role;
            document.getElementById('reviewRating').value = r.rating;
            document.getElementById('reviewContent').value = r.content;
            
            if(r.image_url) {
                previewImg.src = '../' + r.image_url;
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
