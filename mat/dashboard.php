<?php
require_once 'config.php';
checkLogin();

// Fetch counts
$project_count = $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
$message_count = $pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn();
$unread_messages = $pdo->query("SELECT COUNT(*) FROM messages WHERE status = 'unread'")->fetchColumn();
$booking_count = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$pending_bookings = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn();

// Recent messages
$recent_messages = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
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

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--gray-light);
            color: var(--dark);
            display: flex;
        }

        /* Sidebar */
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
        .logo span { color: var(--orange); }

        .nav-links { list-style: none; flex-grow: 1; }
        .nav-links li { margin-bottom: 0.5rem; }
        .nav-links a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.8rem 1.2rem;
            text-decoration: none;
            color: var(--text-muted);
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.2s;
        }
        .nav-links a:hover, .nav-links a.active {
            background: rgba(26, 86, 219, 0.05);
            color: var(--blue);
        }

        .logout-btn {
            margin-top: auto;
            color: #ef4444 !important;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            flex-grow: 1;
            padding: 3rem;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
        }

        .header h1 { font-family: 'Plus Jakarta Sans', sans-serif; font-size: 1.8rem; }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: var(--white);
            padding: 2rem;
            border-radius: 24px;
            border: 1px solid var(--gray-border);
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .stat-card h3 { color: var(--text-muted); font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; }
        .stat-card .value { font-size: 2.5rem; font-weight: 800; color: var(--dark); }
        .stat-card .trend { font-size: 0.85rem; font-weight: 600; }
        .trend.positive { color: #10b981; }

        /* Tables/Lists */
        .content-box {
            background: var(--white);
            padding: 2rem;
            border-radius: 24px;
            border: 1px solid var(--gray-border);
            margin-bottom: 2rem;
        }

        .box-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .box-header h2 { font-size: 1.2rem; font-family: 'Plus Jakarta Sans', sans-serif; }

        .table { width: 100%; border-collapse: collapse; }
        .table th { text-align: left; padding: 1rem; color: var(--text-muted); border-bottom: 1px solid var(--gray-border); font-size: 0.85rem; }
        .table td { padding: 1rem; border-bottom: 1px solid var(--gray-border); font-size: 0.9rem; }

        .badge {
            padding: 4px 10px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
        }
        .badge-unread { background: #fee2e2; color: #ef4444; }
        .badge-read { background: #dcfce7; color: #10b981; }

        .btn-sm {
            padding: 6px 12px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 600;
            background: var(--gray-light);
            color: var(--dark);
            border: 1px solid var(--gray-border);
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">Marr<span>things</span></div>
        <ul class="nav-links">
            <li><a href="dashboard" class="active">Dashboard</a></li>
            <li><a href="projects">Projects</a></li>
            <li><a href="partnerships">Partnerships</a></li>
            <li><a href="reviews">Reviews</a></li>
            <li><a href="messages">Messages (<?php echo $unread_messages; ?>)</a></li>
            <li><a href="bookings">Bookings (<?php echo $pending_bookings; ?>)</a></li>
            <li><a href="profile">My Profile</a></li>
            <li><a href="logout" class="logout-btn">Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Dashboard Overview</h1>
            <div class="user-info">
                Welcome, <strong><?php echo $_SESSION['admin_user']; ?></strong>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Projects</h3>
                <div class="value"><?php echo $project_count; ?></div>
                <div class="trend positive">Active on site</div>
            </div>
            <div class="stat-card">
                <h3>New Messages</h3>
                <div class="value"><?php echo $unread_messages; ?></div>
                <div class="trend">Unread inquiries</div>
            </div>
            <div class="stat-card">
                <h3>Pending Bookings</h3>
                <div class="value"><?php echo $pending_bookings; ?></div>
                <div class="trend">Ready for review</div>
            </div>
        </div>

        <div class="content-box">
            <div class="box-header">
                <h2>Recent Messages</h2>
                <a href="messages" class="btn-sm">View All</a>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent_messages)): ?>
                        <tr><td colspan="5" style="text-align:center; padding: 2rem; color: var(--text-muted);">No messages yet.</td></tr>
                    <?php else: ?>
                        <?php foreach($recent_messages as $msg): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($msg['name']); ?></td>
                                <td><?php echo htmlspecialchars($msg['subject']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $msg['status']; ?>">
                                        <?php echo ucfirst($msg['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($msg['created_at'])); ?></td>
                                <td><a href="messages?id=<?php echo $msg['id']; ?>" class="btn-sm">View</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
