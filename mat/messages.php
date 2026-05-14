<?php
require_once 'config.php';
checkLogin();

// Update status if requested
if (isset($_GET['read'])) {
    $stmt = $pdo->prepare("UPDATE messages SET status = 'read' WHERE id = ?");
    $stmt->execute([$_GET['read']]);
    header("Location: messages");
    exit;
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: messages");
    exit;
}

$messages = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inquiries | Admin Panel</title>
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
        .header { margin-bottom: 2rem; }
        
        .msg-list { display: flex; flex-direction: column; gap: 1rem; }
        .msg-item { background: white; padding: 1.5rem; border-radius: 20px; border: 1px solid var(--gray-border); display: flex; justify-content: space-between; align-items: center; transition: all 0.2s; }
        .msg-item.unread { border-left: 5px solid var(--blue); }
        .msg-item:hover { transform: translateX(5px); border-color: var(--blue); }
        
        .msg-main { flex-grow: 1; }
        .msg-meta { font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.4rem; display: flex; gap: 15px; }
        .msg-subject { font-weight: 700; font-size: 1.1rem; margin-bottom: 0.5rem; }
        .msg-snippet { font-size: 0.9rem; color: var(--text-muted); line-height: 1.5; max-width: 600px; }
        
        .msg-actions { display: flex; gap: 10px; }
        .btn-action { padding: 8px 16px; border-radius: 10px; text-decoration: none; font-size: 0.8rem; font-weight: 700; transition: 0.2s; }
        .btn-view { background: rgba(26, 86, 219, 0.1); color: var(--blue); }
        .btn-delete { background: rgba(239, 68, 68, 0.1); color: #ef4444; }

        .modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; }
        .modal-content { background: white; padding: 3rem; border-radius: 30px; width: 100%; max-width: 700px; }
        .modal-body { line-height: 1.8; color: var(--dark); font-size: 1.05rem; }
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
            <li><a href="messages" class="active">Messages</a></li>
            <li><a href="profile">My Profile</a></li>
            <li><a href="logout" style="color:#ef4444">Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Client Inquiries</h1>
            <p style="color: var(--text-muted)">Manage messages from your contact form.</p>
        </div>

        <div class="msg-list">
            <?php foreach($messages as $m): ?>
                <div class="msg-item <?php echo $m['status'] == 'unread' ? 'unread' : ''; ?>">
                    <div class="msg-main">
                        <div class="msg-meta">
                            <span>From: <strong><?php echo htmlspecialchars($m['name']); ?></strong></span>
                            <span>Email: <?php echo htmlspecialchars($m['email']); ?></span>
                            <span>Date: <?php echo date('M d, Y H:i', strtotime($m['created_at'])); ?></span>
                        </div>
                        <div class="msg-subject"><?php echo htmlspecialchars($m['subject']); ?></div>
                        <div class="msg-snippet"><?php echo substr(htmlspecialchars($m['message']), 0, 150) . '...'; ?></div>
                    </div>
                    <div class="msg-actions">
                        <a href="javascript:void(0)" class="btn-action btn-view" onclick='viewMessage(<?php echo json_encode($m); ?>)'>Read Full</a>
                        <a href="?delete=<?php echo $m['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Delete message?')">Delete</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="modal" id="msgModal">
        <div class="modal-content">
            <h2 id="msgSubject" style="margin-bottom: 1rem;"></h2>
            <div id="msgFrom" style="font-weight: 600; color: var(--blue); margin-bottom: 2rem; border-bottom: 1px solid #eee; padding-bottom: 1rem;"></div>
            <div class="modal-body" id="msgBody"></div>
            <div style="margin-top: 3rem; display: flex; gap: 10px;">
                <a id="replyLink" href="#" class="btn-action btn-view" style="padding: 1rem 2rem;">Reply via Email</a>
                <button onclick="closeModal()" class="btn-action" style="padding: 1rem 2rem; background: #eee;">Close</button>
            </div>
        </div>
    </div>

    <script>
        function viewMessage(m) {
            document.getElementById('msgSubject').innerText = m.subject;
            document.getElementById('msgFrom').innerText = `From: ${m.name} (${m.email})`;
            document.getElementById('msgBody').innerText = m.message;
            document.getElementById('replyLink').href = `mailto:${m.email}?subject=Re: ${m.subject}`;
            document.getElementById('msgModal').style.display = 'flex';
            
            if (m.status === 'unread') {
                fetch(`messages?read=${m.id}`).then(() => {
                    // Update UI locally if needed or just let next reload handle it
                });
            }
        }
        function closeModal() { document.getElementById('msgModal').style.display = 'none'; }
    </script>
</body>
</html>
