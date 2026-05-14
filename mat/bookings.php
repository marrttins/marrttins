<?php
require_once 'config.php';
checkLogin();

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: bookings");
    exit;
}

$bookings = $pdo->query("SELECT * FROM bookings ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Bookings | Admin Panel</title>
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
        
        .table-container { background: white; border-radius: 24px; border: 1px solid var(--gray-border); padding: 2rem; overflow-x: auto; }
        .table { width: 100%; border-collapse: collapse; min-width: 800px; }
        .table th { text-align: left; padding: 1.2rem; color: var(--text-muted); font-size: 0.85rem; border-bottom: 1px solid var(--gray-border); }
        .table td { padding: 1.2rem; border-bottom: 1px solid var(--gray-border); font-size: 0.95rem; }
        .status-pill { padding: 4px 12px; border-radius: 50px; font-size: 0.75rem; font-weight: 700; background: #fef3c7; color: #d97706; }
        
        .btn-action { padding: 6px 12px; border-radius: 8px; text-decoration: none; font-size: 0.8rem; font-weight: 700; background: #eee; color: #333; margin-right: 5px; }
        .btn-view { background: rgba(26, 86, 219, 0.1); color: var(--blue); }
        .btn-delete { background: rgba(239, 68, 68, 0.1); color: #ef4444; }

        .modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; }
        .modal-content { background: white; padding: 3rem; border-radius: 30px; width: 100%; max-width: 600px; }
        .booking-info { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem; padding-bottom: 1.5rem; border-bottom: 1px solid #eee; }
        .info-label { font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 4px; }
        .info-value { font-weight: 700; color: var(--dark); }
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
            <li><a href="bookings" class="active">Bookings</a></li>
            <li><a href="messages">Messages</a></li>
            <li><a href="profile">My Profile</a></li>
            <li><a href="logout" style="color:#ef4444">Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Project Bookings</h1>
            <p style="color: var(--text-muted)">Qualified leads from your booking wizard.</p>
        </div>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Service</th>
                        <th>Timeline</th>
                        <th>Budget</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bookings)): ?>
                        <tr><td colspan="6" style="text-align:center; padding: 3rem; color: var(--text-muted)">No bookings yet.</td></tr>
                    <?php else: ?>
                        <?php foreach($bookings as $b): ?>
                            <tr>
                                <td>
                                    <div style="font-weight:700"><?php echo htmlspecialchars($b['name']); ?></div>
                                    <div style="font-size:0.8rem; color:var(--text-muted)"><?php echo $b['email']; ?></div>
                                </td>
                                <td><span class="status-pill" style="background:rgba(26,86,219,0.1); color:var(--blue)"><?php echo $b['service_type']; ?></span></td>
                                <td><?php echo $b['timeline']; ?></td>
                                <td><strong><?php echo $b['budget']; ?></strong></td>
                                <td><?php echo date('M d, Y', strtotime($b['created_at'])); ?></td>
                                <td>
                                    <a href="javascript:void(0)" class="btn-action btn-view" onclick='viewBooking(<?php echo json_encode($b); ?>)'>Details</a>
                                    <a href="?delete=<?php echo $b['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Delete booking?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal" id="bookingModal">
        <div class="modal-content">
            <h2 style="margin-bottom: 2rem;">Booking Details</h2>
            <div class="booking-info">
                <div><div class="info-label">Service</div><div class="info-value" id="bService"></div></div>
                <div><div class="info-label">Timeline</div><div class="info-value" id="bTimeline"></div></div>
                <div><div class="info-label">Budget Range</div><div class="info-value" id="bBudget"></div></div>
                <div><div class="info-label">Contact</div><div class="info-value" id="bContact"></div></div>
            </div>
            <div class="info-label">Project Details</div>
            <div id="bDetails" style="line-height: 1.6; margin-top: 0.5rem; background: var(--gray-light); padding: 1.5rem; border-radius: 15px;"></div>
            
            <div style="margin-top: 2.5rem; display: flex; gap: 10px;">
                <button onclick="closeModal()" class="btn-action" style="padding: 1rem 2rem; background: var(--blue); color:white; flex-grow:1; border:none; cursor:pointer; border-radius:12px; font-weight:700;">Close View</button>
            </div>
        </div>
    </div>

    <script>
        function viewBooking(b) {
            document.getElementById('bService').innerText = b.service_type;
            document.getElementById('bTimeline').innerText = b.timeline;
            document.getElementById('bBudget').innerText = b.budget;
            document.getElementById('bContact').innerText = b.email;
            document.getElementById('bDetails').innerText = b.details;
            document.getElementById('bookingModal').style.display = 'flex';
        }
        function closeModal() { document.getElementById('bookingModal').style.display = 'none'; }
    </script>
</body>
</html>
