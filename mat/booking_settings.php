<?php
require_once 'config.php';
checkLogin();

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_service'])) {
        $stmt = $pdo->prepare("INSERT INTO services (category_id, name, min_price, max_price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_POST['category_id'], $_POST['name'], $_POST['min_price'], $_POST['max_price']]);
    } elseif (isset($_POST['update_service'])) {
        $stmt = $pdo->prepare("UPDATE services SET category_id = ?, name = ?, min_price = ?, max_price = ? WHERE id = ?");
        $stmt->execute([$_POST['category_id'], $_POST['name'], $_POST['min_price'], $_POST['max_price'], $_POST['id']]);
    } elseif (isset($_POST['delete_service'])) {
        $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
        $stmt->execute([$_POST['id']]);
    } elseif (isset($_POST['add_addon'])) {
        $stmt = $pdo->prepare("INSERT INTO addons (name, price_text, min_price) VALUES (?, ?, ?)");
        $stmt->execute([$_POST['name'], $_POST['price_text'], $_POST['min_price']]);
    } elseif (isset($_POST['update_addon'])) {
        $stmt = $pdo->prepare("UPDATE addons SET name = ?, price_text = ?, min_price = ? WHERE id = ?");
        $stmt->execute([$_POST['name'], $_POST['price_text'], $_POST['min_price'], $_POST['id']]);
    } elseif (isset($_POST['delete_addon'])) {
        $stmt = $pdo->prepare("DELETE FROM addons WHERE id = ?");
        $stmt->execute([$_POST['id']]);
    } elseif (isset($_POST['add_timeline'])) {
        $stmt = $pdo->prepare("INSERT INTO timelines (name, duration) VALUES (?, ?)");
        $stmt->execute([$_POST['name'], $_POST['duration']]);
    } elseif (isset($_POST['update_timeline'])) {
        $stmt = $pdo->prepare("UPDATE timelines SET name = ?, duration = ? WHERE id = ?");
        $stmt->execute([$_POST['name'], $_POST['duration'], $_POST['id']]);
    } elseif (isset($_POST['delete_timeline'])) {
        $stmt = $pdo->prepare("DELETE FROM timelines WHERE id = ?");
        $stmt->execute([$_POST['id']]);
    }
    header("Location: booking_settings");
    exit;
}

$categories = $pdo->query("SELECT * FROM service_categories ORDER BY id ASC")->fetchAll();
$services = $pdo->query("SELECT s.*, c.name as category_name FROM services s JOIN service_categories c ON s.category_id = c.id ORDER BY c.id, s.name")->fetchAll();
$addons = $pdo->query("SELECT * FROM addons ORDER BY name")->fetchAll();
$timelines = $pdo->query("SELECT * FROM timelines ORDER BY id")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Settings | Admin Panel</title>
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
        
        .section-card { background: white; border-radius: 24px; border: 1px solid var(--gray-border); padding: 2rem; margin-bottom: 2rem; }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .btn-add { background: var(--blue); color: white; padding: 0.6rem 1.2rem; border-radius: 10px; text-decoration: none; font-weight: 700; font-size: 0.85rem; border: none; cursor: pointer; }
        
        .table { width: 100%; border-collapse: collapse; }
        .table th { text-align: left; padding: 1rem; color: var(--text-muted); font-size: 0.8rem; border-bottom: 1px solid var(--gray-border); }
        .table td { padding: 1rem; border-bottom: 1px solid var(--gray-border); font-size: 0.9rem; }
        
        .btn-action { padding: 5px 10px; border-radius: 6px; text-decoration: none; font-size: 0.75rem; font-weight: 700; background: #eee; color: #333; margin-right: 5px; border: none; cursor: pointer; }
        .btn-edit { background: rgba(26, 86, 219, 0.1); color: var(--blue); }
        .btn-delete { background: rgba(239, 68, 68, 0.1); color: #ef4444; }

        .modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; }
        .modal-content { background: white; padding: 2.5rem; border-radius: 24px; width: 100%; max-width: 500px; }
        .form-group { margin-bottom: 1.2rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-size: 0.85rem; font-weight: 600; }
        .form-control { width: 100%; padding: 0.8rem; border: 1px solid var(--gray-border); border-radius: 10px; font-family: inherit; }
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
            <li><a href="bookings">Bookings</a></li>
            <li><a href="booking_settings" class="active">Booking Settings</a></li>
            <li><a href="messages">Messages</a></li>
            <li><a href="profile">My Profile</a></li>
            <li><a href="logout" style="color:#ef4444">Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div style="margin-bottom: 2.5rem;">
            <h1>Booking Settings</h1>
            <p style="color: var(--text-muted)">Manage service types, prices, and project options.</p>
        </div>

        <!-- Services Section -->
        <div class="section-card">
            <div class="section-header">
                <h2>Services & Pricing</h2>
                <button class="btn-add" onclick="openServiceModal()">+ Add Service</button>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Service Name</th>
                        <th>Min Price</th>
                        <th>Max Price</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($services as $s): ?>
                    <tr>
                        <td><span style="background: var(--gray-light); padding: 4px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 700;"><?php echo $s['category_name']; ?></span></td>
                        <td><strong><?php echo htmlspecialchars($s['name']); ?></strong></td>
                        <td>₦<?php echo number_format($s['min_price']); ?></td>
                        <td>₦<?php echo number_format($s['max_price']); ?></td>
                        <td>
                            <button class="btn-action btn-edit" onclick='editService(<?php echo json_encode($s); ?>)'>Edit</button>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this service?')">
                                <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
                                <button type="submit" name="delete_service" class="btn-action btn-delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Addons Section -->
        <div class="section-card">
            <div class="section-header">
                <h2>Add-on Services</h2>
                <button class="btn-add" onclick="openAddonModal()">+ Add Add-on</button>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Price Text</th>
                        <th>Min Price (Logic)</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($addons as $a): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($a['name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($a['price_text']); ?></td>
                        <td>₦<?php echo number_format($a['min_price']); ?></td>
                        <td>
                            <button class="btn-action btn-edit" onclick='editAddon(<?php echo json_encode($a); ?>)'>Edit</button>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this add-on?')">
                                <input type="hidden" name="id" value="<?php echo $a['id']; ?>">
                                <button type="submit" name="delete_addon" class="btn-action btn-delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Timelines Section -->
        <div class="section-card">
            <div class="section-header">
                <h2>Project Timelines</h2>
                <button class="btn-add" onclick="openTimelineModal()">+ Add Timeline</button>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Duration</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($timelines as $t): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($t['name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($t['duration']); ?></td>
                        <td>
                            <button class="btn-action btn-edit" onclick='editTimeline(<?php echo json_encode($t); ?>)'>Edit</button>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this timeline?')">
                                <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
                                <button type="submit" name="delete_timeline" class="btn-action btn-delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Service Modal -->
    <div class="modal" id="serviceModal">
        <div class="modal-content">
            <h2 id="sModalTitle">Add Service</h2>
            <form method="POST">
                <input type="hidden" name="id" id="sId">
                <div class="form-group">
                    <label>Category</label>
                    <select name="category_id" id="sCategory" class="form-control" required>
                        <?php foreach($categories as $c): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo $c['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Service Name</label>
                    <input type="text" name="name" id="sName" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Min Price (₦)</label>
                    <input type="number" name="min_price" id="sMin" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Max Price (₦)</label>
                    <input type="number" name="max_price" id="sMax" class="form-control" required>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="submit" name="add_service" id="sSubmitBtn" class="btn-add" style="flex:1">Save Service</button>
                    <button type="button" onclick="closeModal('serviceModal')" class="btn-action" style="flex:1; border-radius:10px;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Addon Modal -->
    <div class="modal" id="addonModal">
        <div class="modal-content">
            <h2 id="aModalTitle">Add Add-on</h2>
            <form method="POST">
                <input type="hidden" name="id" id="aId">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" id="aName" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Price Text (e.g. ₦70,000 - ₦200,000)</label>
                    <input type="text" name="price_text" id="aPriceText" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Min Price (Numerical, for calculation)</label>
                    <input type="number" name="min_price" id="aMin" class="form-control" required>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="submit" name="add_addon" id="aSubmitBtn" class="btn-add" style="flex:1">Save Add-on</button>
                    <button type="button" onclick="closeModal('addonModal')" class="btn-action" style="flex:1; border-radius:10px;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Timeline Modal -->
    <div class="modal" id="timelineModal">
        <div class="modal-content">
            <h2 id="tModalTitle">Add Timeline</h2>
            <form method="POST">
                <input type="hidden" name="id" id="tId">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" id="tName" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Duration (e.g. 2-3 weeks)</label>
                    <input type="text" name="duration" id="tDuration" class="form-control" required>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="submit" name="add_timeline" id="tSubmitBtn" class="btn-add" style="flex:1">Save Timeline</button>
                    <button type="button" onclick="closeModal('timelineModal')" class="btn-action" style="flex:1; border-radius:10px;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openServiceModal() {
            document.getElementById('sId').value = '';
            document.getElementById('sName').value = '';
            document.getElementById('sMin').value = '';
            document.getElementById('sMax').value = '';
            document.getElementById('sModalTitle').innerText = 'Add Service';
            document.getElementById('sSubmitBtn').name = 'add_service';
            document.getElementById('serviceModal').style.display = 'flex';
        }
        function editService(s) {
            document.getElementById('sId').value = s.id;
            document.getElementById('sCategory').value = s.category_id;
            document.getElementById('sName').value = s.name;
            document.getElementById('sMin').value = s.min_price;
            document.getElementById('sMax').value = s.max_price;
            document.getElementById('sModalTitle').innerText = 'Edit Service';
            document.getElementById('sSubmitBtn').name = 'update_service';
            document.getElementById('serviceModal').style.display = 'flex';
        }

        function openAddonModal() {
            document.getElementById('aId').value = '';
            document.getElementById('aName').value = '';
            document.getElementById('aPriceText').value = '';
            document.getElementById('aMin').value = '';
            document.getElementById('aModalTitle').innerText = 'Add Add-on';
            document.getElementById('aSubmitBtn').name = 'add_addon';
            document.getElementById('addonModal').style.display = 'flex';
        }
        function editAddon(a) {
            document.getElementById('aId').value = a.id;
            document.getElementById('aName').value = a.name;
            document.getElementById('aPriceText').value = a.price_text;
            document.getElementById('aMin').value = a.min_price;
            document.getElementById('aModalTitle').innerText = 'Edit Add-on';
            document.getElementById('aSubmitBtn').name = 'update_addon';
            document.getElementById('addonModal').style.display = 'flex';
        }

        function openTimelineModal() {
            document.getElementById('tId').value = '';
            document.getElementById('tName').value = '';
            document.getElementById('tDuration').value = '';
            document.getElementById('tModalTitle').innerText = 'Add Timeline';
            document.getElementById('tSubmitBtn').name = 'add_timeline';
            document.getElementById('timelineModal').style.display = 'flex';
        }
        function editTimeline(t) {
            document.getElementById('tId').value = t.id;
            document.getElementById('tName').value = t.name;
            document.getElementById('tDuration').value = t.duration;
            document.getElementById('tModalTitle').innerText = 'Edit Timeline';
            document.getElementById('tSubmitBtn').name = 'update_timeline';
            document.getElementById('timelineModal').style.display = 'flex';
        }

        function closeModal(id) { document.getElementById(id).style.display = 'none'; }
    </script>
</body>
</html>
