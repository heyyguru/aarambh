<?php
/**
 * Admin Dashboard — Student Lead Management
 */
define('AARAMBH_INIT', true);
require_once __DIR__ . '/../config.php';

session_name(ADMIN_SESSION_NAME);
session_start();

// Auth check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Session timeout
if (time() - ($_SESSION['admin_login_time'] ?? 0) > ADMIN_SESSION_TIMEOUT) {
    session_destroy();
    header('Location: login.php?expired=1');
    exit;
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$db = getDB();

// Handle call status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_call_status') {
        $studentId = intval($_POST['student_id'] ?? 0);
        $callStatus = sanitize($_POST['call_status'] ?? '');
        $notes = sanitize($_POST['notes'] ?? '');
        
        if ($studentId > 0 && in_array($callStatus, ['not_called', 'called', 'follow_up', 'converted', 'not_interested'])) {
            $stmt = $db->prepare("UPDATE students SET call_status = ?, notes = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$callStatus, $notes, $studentId]);
        }
        header('Location: index.php?' . http_build_query($_GET));
        exit;
    }
}

// Filters
$statusFilter = $_GET['status'] ?? 'all';
$callFilter = $_GET['call_status'] ?? 'all';
$search = sanitize($_GET['search'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 25;
$offset = ($page - 1) * $perPage;

// Build query
$where = [];
$params = [];

if ($statusFilter !== 'all') {
    $where[] = "status = ?";
    $params[] = $statusFilter;
}
if ($callFilter !== 'all') {
    $where[] = "call_status = ?";
    $params[] = $callFilter;
}
if (!empty($search)) {
    $where[] = "(name LIKE ? OR email LIKE ? OR phone LIKE ? OR city LIKE ?)";
    $searchTerm = "%{$search}%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total count
$countStmt = $db->prepare("SELECT COUNT(*) as total FROM students {$whereClause}");
$countStmt->execute($params);
$totalStudents = $countStmt->fetch()['total'];
$totalPages = ceil($totalStudents / $perPage);

// Get students
$stmt = $db->prepare("SELECT * FROM students {$whereClause} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}");
$stmt->execute($params);
$students = $stmt->fetchAll();

// Stats
$statsStmt = $db->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'lead' THEN 1 ELSE 0 END) as leads,
    SUM(CASE WHEN status = 'payment_initiated' THEN 1 ELSE 0 END) as payment_initiated,
    SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid,
    SUM(CASE WHEN status = 'enrolled' THEN 1 ELSE 0 END) as enrolled,
    SUM(CASE WHEN call_status = 'not_called' THEN 1 ELSE 0 END) as not_called,
    SUM(CASE WHEN call_status = 'called' THEN 1 ELSE 0 END) as called,
    SUM(CASE WHEN call_status = 'follow_up' THEN 1 ELSE 0 END) as follow_up,
    SUM(CASE WHEN call_status = 'converted' THEN 1 ELSE 0 END) as converted,
    SUM(CASE WHEN email_sent = 1 THEN 1 ELSE 0 END) as emails_sent
    FROM students");
$stats = $statsStmt->fetch();

// Visit stats
$visitStmt = $db->query("SELECT COUNT(*) as total_visits, COUNT(DISTINCT ip_address) as unique_visits FROM page_visits");
$visitStats = $visitStmt->fetch();

// Today's stats
$todayStmt = $db->query("SELECT COUNT(*) as today_leads FROM students WHERE DATE(created_at) = CURDATE()");
$todayStats = $todayStmt->fetch();

$todayVisitStmt = $db->query("SELECT COUNT(*) as today_visits FROM page_visits WHERE DATE(visited_at) = CURDATE()");
$todayVisitStats = $todayVisitStmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — Aarambh by HeyyGuru</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-logo">
                <h2>📊 Aarambh Admin</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="index.php" class="active">📋 Dashboard</a>
                <a href="index.php?status=lead">🎯 Leads</a>
                <a href="index.php?status=paid">💳 Paid Students</a>
                <a href="index.php?call_status=not_called">📞 Not Called</a>
                <a href="index.php?call_status=follow_up">🔄 Follow Up</a>
                <a href="export.php">📥 Export CSV</a>
                <a href="?logout=1" class="logout">🚪 Logout</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-header">
                <h1>Dashboard</h1>
                <span>Welcome, <?php echo sanitize($_SESSION['admin_username']); ?></span>
            </header>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card blue">
                    <div class="stat-number"><?php echo $stats['total']; ?></div>
                    <div class="stat-label">Total Leads</div>
                </div>
                <div class="stat-card green">
                    <div class="stat-number"><?php echo $stats['paid'] + $stats['enrolled']; ?></div>
                    <div class="stat-label">Paid Students</div>
                </div>
                <div class="stat-card orange">
                    <div class="stat-number"><?php echo $todayStats['today_leads']; ?></div>
                    <div class="stat-label">Today's Leads</div>
                </div>
                <div class="stat-card purple">
                    <div class="stat-number"><?php echo $visitStats['unique_visits']; ?></div>
                    <div class="stat-label">Unique Visits</div>
                </div>
                <div class="stat-card red">
                    <div class="stat-number"><?php echo $stats['not_called']; ?></div>
                    <div class="stat-label">Not Called</div>
                </div>
                <div class="stat-card teal">
                    <div class="stat-number"><?php echo $stats['follow_up']; ?></div>
                    <div class="stat-label">Follow Up</div>
                </div>
                <div class="stat-card yellow">
                    <div class="stat-number"><?php echo $todayVisitStats['today_visits']; ?></div>
                    <div class="stat-label">Today's Visits</div>
                </div>
                <div class="stat-card pink">
                    <div class="stat-number"><?php echo $stats['total'] > 0 ? round(($stats['paid'] + $stats['enrolled']) / $stats['total'] * 100, 1) : 0; ?>%</div>
                    <div class="stat-label">Conversion Rate</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters-bar">
                <form method="GET" action="" class="filter-form">
                    <input type="text" name="search" placeholder="Search name, email, phone..." value="<?php echo htmlspecialchars($search); ?>" class="filter-input">
                    <select name="status" class="filter-select">
                        <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All Status</option>
                        <option value="lead" <?php echo $statusFilter === 'lead' ? 'selected' : ''; ?>>Lead</option>
                        <option value="payment_initiated" <?php echo $statusFilter === 'payment_initiated' ? 'selected' : ''; ?>>Payment Initiated</option>
                        <option value="paid" <?php echo $statusFilter === 'paid' ? 'selected' : ''; ?>>Paid</option>
                        <option value="enrolled" <?php echo $statusFilter === 'enrolled' ? 'selected' : ''; ?>>Enrolled</option>
                    </select>
                    <select name="call_status" class="filter-select">
                        <option value="all" <?php echo $callFilter === 'all' ? 'selected' : ''; ?>>All Call Status</option>
                        <option value="not_called" <?php echo $callFilter === 'not_called' ? 'selected' : ''; ?>>Not Called</option>
                        <option value="called" <?php echo $callFilter === 'called' ? 'selected' : ''; ?>>Called</option>
                        <option value="follow_up" <?php echo $callFilter === 'follow_up' ? 'selected' : ''; ?>>Follow Up</option>
                        <option value="converted" <?php echo $callFilter === 'converted' ? 'selected' : ''; ?>>Converted</option>
                        <option value="not_interested" <?php echo $callFilter === 'not_interested' ? 'selected' : ''; ?>>Not Interested</option>
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    <a href="index.php" class="btn btn-outline btn-sm">Reset</a>
                </form>
            </div>

            <!-- Students Table -->
            <div class="table-container">
                <div class="table-header">
                    <h3>Students (<?php echo $totalStudents; ?> total)</h3>
                    <a href="export.php?<?php echo http_build_query($_GET); ?>" class="btn btn-sm btn-outline">📥 Export CSV</a>
                </div>
                <div class="table-scroll">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Class</th>
                                <th>City</th>
                                <th>Status</th>
                                <th>Call Status</th>
                                <th>Source</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($students)): ?>
                                <tr><td colspan="11" style="text-align:center;padding:2rem;">No students found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($students as $s): ?>
                                <tr>
                                    <td><?php echo $s['id']; ?></td>
                                    <td><strong><?php echo sanitize($s['name']); ?></strong></td>
                                    <td>
                                        <a href="tel:+91<?php echo $s['phone']; ?>" class="phone-link">
                                            📞 <?php echo $s['phone']; ?>
                                        </a>
                                    </td>
                                    <td><a href="mailto:<?php echo $s['email']; ?>"><?php echo sanitize($s['email']); ?></a></td>
                                    <td><?php echo sanitize($s['student_class']); ?></td>
                                    <td><?php echo sanitize($s['city'] ?: '-'); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $s['status']; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $s['status'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-call-<?php echo $s['call_status']; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $s['call_status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo sanitize($s['utm_source'] ?: '-'); ?></td>
                                    <td><?php echo date('d M, H:i', strtotime($s['created_at'])); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline" onclick="openUpdateModal(<?php echo $s['id']; ?>, '<?php echo $s['call_status']; ?>', '<?php echo addslashes($s['notes'] ?? ''); ?>')">
                                            ✏️ Update
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                           class="page-btn <?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Update Modal -->
    <div class="modal-overlay" id="updateModal" style="display:none;">
        <div class="modal-card">
            <h3>Update Call Status</h3>
            <form method="POST" action="">
                <input type="hidden" name="action" value="update_call_status">
                <input type="hidden" name="student_id" id="modal-student-id">
                <div class="form-group">
                    <label>Call Status</label>
                    <select name="call_status" id="modal-call-status" class="form-control">
                        <option value="not_called">Not Called</option>
                        <option value="called">Called</option>
                        <option value="follow_up">Follow Up</option>
                        <option value="converted">Converted</option>
                        <option value="not_interested">Not Interested</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Notes</label>
                    <textarea name="notes" id="modal-notes" class="form-control" rows="3" placeholder="Add notes about the call..."></textarea>
                </div>
                <div style="display:flex;gap:0.5rem;margin-top:1rem;">
                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
                    <button type="button" class="btn btn-outline btn-sm" onclick="closeModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openUpdateModal(id, callStatus, notes) {
            document.getElementById('modal-student-id').value = id;
            document.getElementById('modal-call-status').value = callStatus;
            document.getElementById('modal-notes').value = notes;
            document.getElementById('updateModal').style.display = 'flex';
        }
        function closeModal() {
            document.getElementById('updateModal').style.display = 'none';
        }
        document.getElementById('updateModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
    </script>
</body>
</html>
