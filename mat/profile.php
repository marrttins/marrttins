<?php
require_once 'config.php';
checkLogin();

$admin_id = $_SESSION['admin_id'];
$message = '';
$error = '';

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $new_password = $_POST['password'];
    
    // Handle Profile Pic Upload
    $profile_pic = $_POST['existing_pic'];
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'assets/uploads/admin/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $file_ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
        $file_name = 'admin_' . time() . '.' . $file_ext;
        $upload_path = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $upload_path)) {
            $profile_pic = 'Mat/assets/uploads/admin/' . $file_name;
        }
    }

    try {
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE admins SET username = ?, email = ?, password = ?, profile_pic = ? WHERE id = ?");
            $stmt->execute([$username, $email, $hashed_password, $profile_pic, $admin_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE admins SET username = ?, email = ?, profile_pic = ? WHERE id = ?");
            $stmt->execute([$username, $email, $profile_pic, $admin_id]);
        }
        
        $_SESSION['admin_user'] = $username;
        $message = "Profile updated successfully!";
    } catch (PDOException $e) {
        $error = "Error updating profile: " . $e->getMessage();
    }
}

// Fetch Admin Data
$stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Admin Panel</title>
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
        
        .profile-container { max-width: 800px; background: white; border-radius: 30px; border: 1px solid var(--gray-border); padding: 3rem; }
        .profile-header { display: flex; align-items: center; gap: 2rem; margin-bottom: 3rem; }
        .profile-pic-wrapper { position: relative; width: 120px; height: 120px; }
        .profile-pic { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; border: 4px solid var(--white); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .upload-btn { position: absolute; bottom: 0; right: 0; background: var(--blue); color: white; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 3px solid var(--white); }
        
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.9rem; }
        .form-control { width: 100%; padding: 0.8rem 1rem; border: 1px solid var(--gray-border); border-radius: 12px; font-family: inherit; font-size: 0.95rem; }
        .btn-save { background: var(--blue); color: white; padding: 1rem 2rem; border-radius: 12px; border: none; font-weight: 700; cursor: pointer; width: 100%; margin-top: 1rem; }
        
        .alert { padding: 1rem; border-radius: 12px; margin-bottom: 2rem; font-weight: 600; font-size: 0.9rem; }
        .alert-success { background: #dcfce7; color: #10b981; }
        .alert-error { background: #fee2e2; color: #ef4444; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">Marr<span>things</span></div>
        <ul class="nav-links">
            <li><a href="dashboard">Dashboard</a></li>
            <li><a href="projects">Projects</a></li>
            <li><a href="partnerships">Partnerships</a></li>
            <li><a href="reviews">Reviews</a></li>
            <li><a href="booking_settings">Booking Settings</a></li>
            <li><a href="bookings">Bookings</a></li>
            <li><a href="messages">Messages</a></li>
            <li><a href="profile" class="active">My Profile</a></li>
            <li><a href="logout" style="color:#ef4444">Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div style="margin-bottom: 2rem;">
            <h1>Account Settings</h1>
            <p style="color: var(--text-muted)">Manage your personal information and login credentials.</p>
        </div>

        <div class="profile-container">
            <?php if($message): ?> <div class="alert alert-success"><?php echo $message; ?></div> <?php endif; ?>
            <?php if($error): ?> <div class="alert alert-error"><?php echo $error; ?></div> <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="existing_pic" value="<?php echo $admin['profile_pic']; ?>">
                
                <div class="profile-header">
                    <div class="profile-pic-wrapper">
                        <img src="<?php echo $admin['profile_pic'] ? '../' . $admin['profile_pic'] : 'https://ui-avatars.com/api/?name=' . $admin['username'] . '&background=1a56db&color=fff'; ?>" class="profile-pic" id="previewImg">
                        <label for="picInput" class="upload-btn">📷</label>
                        <input type="file" id="picInput" name="profile_pic" style="display:none" onchange="previewImage(this)">
                    </div>
                    <div>
                        <h2 style="margin-bottom: 0.3rem;"><?php echo htmlspecialchars($admin['username']); ?></h2>
                        <p style="color: var(--text-muted); font-size: 0.9rem;">Administrator</p>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($admin['username']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($admin['email']); ?>" placeholder="admin@example.com">
                    </div>
                    <div class="form-group" style="grid-column: span 2;">
                        <label>New Password (leave blank to keep current)</label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••">
                    </div>
                </div>

                <button type="submit" class="btn-save">Update Profile</button>
            </form>
        </div>
    </div>

    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('previewImg').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>
