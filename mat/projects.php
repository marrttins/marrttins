<?php
require_once 'config.php';
checkLogin();

$message = '';

$available_tools = [
    "React",
    "Next.js",
    "Nest.js",
    "PHP",
    "Laravel",
    "MySQL",
    "WordPress",
    "WooCommerce",
    "Shopify",
    "Wix",
    "Webflow",
    "Elementor",
    "Paystack",
    "Stripe",
    "Tailwind",
    "Google Ads",
    "Meta Ads",
    "Node.js",
    "PostgreSQL",
    "Supabase",
    "Neon",
    "Firebase",
    "UI/UX Design"
];

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: projects?success=deleted");
    exit;
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $live_link = $_POST['live_link'];
    $tools_arr = $_POST['tools'] ?? [];
    $tools = implode(', ', $tools_arr);
    $id = $_POST['id'] ?? null;

    // Handle Image Upload
    $image_url = $_POST['existing_image'] ?? '';
    if (isset($_FILES['project_image']) && $_FILES['project_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../assets/uploads/';
        if (!is_dir($upload_dir))
            mkdir($upload_dir, 0777, true);

        $file_ext = pathinfo($_FILES['project_image']['name'], PATHINFO_EXTENSION);
        $file_name = time() . '_' . uniqid() . '.' . $file_ext;
        $upload_path = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['project_image']['tmp_name'], $upload_path)) {
            $image_url = 'assets/uploads/' . $file_name;
        }
    }

    if ($id) {
        $stmt = $pdo->prepare("UPDATE projects SET title=?, category=?, description=?, image_url=?, live_link=?, tools=? WHERE id=?");
        $stmt->execute([$title, $category, $description, $image_url, $live_link, $tools, $id]);
        $message = "Project updated successfully!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO projects (title, category, description, image_url, live_link, tools) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $category, $description, $image_url, $live_link, $tools]);
        $message = "Project added successfully!";
    }
}

$projects = $pdo->query("SELECT * FROM projects ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Projects | Admin Panel</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@700;800&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --blue: #1a56db;
            --orange: #f97316;
            --dark: #0f172a;
            --white: #ffffff;
            --gray-light: #f8fafc;
            --gray-border: #e2e8f0;
            --text-muted: #64748b;
            --sidebar-width: 280px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--gray-light);
            color: var(--dark);
            display: flex;
        }

        .sidebar {
            width: var(--sidebar-width);
            background: var(--white);
            height: 100vh;
            position: fixed;
            border-right: 1px solid var(--gray-border);
            padding: 2rem;
            display: flex;
            flex-direction: column;
        }

        .logo {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 3rem;
        }

        .logo span {
            color: var(--orange);
        }

        .nav-links {
            list-style: none;
            flex-grow: 1;
        }

        .nav-links a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.8rem 1.2rem;
            text-decoration: none;
            color: var(--text-muted);
            font-weight: 600;
            border-radius: 12px;
        }

        .nav-links a.active {
            background: rgba(26, 86, 219, 0.05);
            color: var(--blue);
        }

        .main-content {
            margin-left: var(--sidebar-width);
            flex-grow: 1;
            padding: 3rem;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .btn-add {
            background: var(--blue);
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            border: none;
            cursor: pointer;
        }

        .projects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .project-card {
            background: var(--white);
            border-radius: 20px;
            border: 1px solid var(--gray-border);
            overflow: hidden;
        }

        .project-thumb {
            height: 160px;
            background: #eee;
            overflow: hidden;
            position: relative;
        }

        .project-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .project-info {
            padding: 1.5rem;
        }

        .project-info h3 {
            margin-bottom: 0.5rem;
        }

        .project-info p {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-bottom: 1rem;
            height: 3.2em;
            overflow: hidden;
        }

        .actions {
            display: flex;
            gap: 10px;
            border-top: 1px solid var(--gray-border);
            padding: 1rem 1.5rem;
        }

        .btn-edit {
            color: var(--blue);
            text-decoration: none;
            font-weight: 700;
            font-size: 0.85rem;
        }

        .btn-delete {
            color: #ef4444;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.85rem;
        }

        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            padding: 2.5rem;
            border-radius: 24px;
            width: 100%;
            max-width: 650px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .form-group {
            margin-bottom: 1.2rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.8rem;
            border-radius: 10px;
            border: 1px solid var(--gray-border);
            font-family: inherit;
        }

        .tools-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 10px;
            background: var(--gray-light);
            padding: 1rem;
            border-radius: 12px;
            border: 1px solid var(--gray-border);
        }

        .tool-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
            cursor: pointer;
        }

        .tool-item input {
            width: auto;
            cursor: pointer;
        }

        .image-preview {
            width: 100%;
            height: 200px;
            border-radius: 12px;
            border: 2px dashed var(--gray-border);
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: var(--gray-light);
        }

        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: none;
        }

        .image-preview span {
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        .success-banner {
            background: #dcfce7;
            color: #10b981;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <div class="logo">Marr<span>things</span></div>
        <ul class="nav-links">
            <li><a href="dashboard">Dashboard</a></li>
            <li><a href="projects" class="active">Projects</a></li>
            <li><a href="partnerships">Partnerships</a></li>
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
            <h1>Manage Portfolio</h1>
            <button class="btn-add" onclick="openModal()">+ Add New Project</button>
        </div>

        <?php if ($message || isset($_GET['success'])): ?>
            <div class="success-banner"><?php echo $message ?: "Operation successful!"; ?></div>
        <?php endif; ?>

        <div class="projects-grid">
            <?php foreach ($projects as $p): ?>
                <div class="project-card">
                    <div class="project-thumb">
                        <img src="../<?php echo $p['image_url'] ?: 'assets/images/p1.png'; ?>" alt="">
                    </div>
                    <div class="project-info">
                        <h3><?php echo htmlspecialchars($p['title']); ?></h3>
                        <p><?php echo htmlspecialchars($p['description']); ?></p>
                        <div style="font-size: 0.75rem; color: var(--blue); font-weight:700"><?php echo $p['category']; ?>
                        </div>
                    </div>
                    <div class="actions">
                        <a href="javascript:void(0)" class="btn-edit"
                            onclick='editProject(<?php echo json_encode($p); ?>)'>Edit Details</a>
                        <a href="?delete=<?php echo $p['id']; ?>" class="btn-delete"
                            onclick="return confirm('Are you sure?')">Delete</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div class="modal" id="projectModal">
        <div class="modal-content">
            <h2 id="modalTitle" style="margin-bottom: 2rem;">Add New Project</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" id="projId">
                <input type="hidden" name="existing_image" id="existingImage">

                <div class="form-group">
                    <label>Project Title</label>
                    <input type="text" name="title" id="projTitle" required>
                </div>

                <div class="form-group">
                    <label>Category</label>
                    <select name="category" id="projCategory" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat; ?>"><?php echo $cat; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Project Image</label>
                    <input type="file" name="project_image" id="projImageInput" accept="image/*"
                        onchange="previewImage(this)">
                    <div class="image-preview" id="imagePreviewContainer">
                        <span>No image selected</span>
                        <img src="" id="imagePreviewImg">
                    </div>
                </div>

                <div class="form-group">
                    <label>Live Link</label>
                    <input type="url" name="live_link" id="projLink">
                </div>

                <div class="form-group">
                    <label>Tools Used</label>
                    <div class="tools-grid">
                        <?php foreach ($available_tools as $tool): ?>
                            <label class="tool-item">
                                <input type="checkbox" name="tools[]" value="<?php echo $tool; ?>" class="tool-checkbox">
                                <span><?php echo $tool; ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="projDesc" rows="4"></textarea>
                </div>

                <div style="display:flex; gap:10px; margin-top:2rem">
                    <button type="submit" class="btn-add" style="flex-grow:1">Save Project</button>
                    <button type="button" class="btn-add" style="background:#eee; color:#333;"
                        onclick="closeModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('projectModal');
        const previewImg = document.getElementById('imagePreviewImg');
        const previewText = document.querySelector('#imagePreviewContainer span');

        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImg.src = e.target.result;
                    previewImg.style.display = 'block';
                    previewText.style.display = 'none';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function openModal() {
            document.getElementById('modalTitle').innerText = "Add New Project";
            document.getElementById('projId').value = "";
            document.getElementById('existingImage').value = "";
            document.getElementById('projTitle').value = "";
            document.getElementById('projCategory').value = "";
            document.getElementById('projLink').value = "";
            document.getElementById('projDesc').value = "";
            document.getElementById('projImageInput').value = "";
            previewImg.style.display = 'none';
            previewText.style.display = 'block';
            document.querySelectorAll('.tool-checkbox').forEach(cb => cb.checked = false);
            modal.style.display = 'flex';
        }

        function closeModal() {
            modal.style.display = 'none';
        }

        function editProject(p) {
            document.getElementById('modalTitle').innerText = "Edit Project";
            document.getElementById('projId').value = p.id;
            document.getElementById('existingImage').value = p.image_url;
            document.getElementById('projTitle').value = p.title;
            document.getElementById('projCategory').value = p.category;
            document.getElementById('projLink').value = p.live_link;
            document.getElementById('projDesc').value = p.description;

            // Image Preview
            if (p.image_url) {
                previewImg.src = '../' + p.image_url;
                previewImg.style.display = 'block';
                previewText.style.display = 'none';
            } else {
                previewImg.style.display = 'none';
                previewText.style.display = 'block';
            }

            // Tools Checkboxes
            document.querySelectorAll('.tool-checkbox').forEach(cb => cb.checked = false);
            if (p.tools) {
                const projectTools = p.tools.split(',').map(t => t.trim());
                document.querySelectorAll('.tool-checkbox').forEach(cb => {
                    if (projectTools.includes(cb.value)) cb.checked = true;
                });
            }

            modal.style.display = 'flex';
        }

        window.onclick = function (event) {
            if (event.target == modal) closeModal();
        }
    </script>
</body>

</html>