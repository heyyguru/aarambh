<?php
/**
 * Admin Dashboard — Student Lead Management
 */
define('AARAMBH_INIT', true);
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth.php';

init_secure_session();
$admin_username = require_admin_auth();

// Logout (POST only with CSRF validation)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'logout') {
    if (validate_csrf_token($_POST['csrf_token'] ?? '')) {
        delete_secure_cookie('admin_access_token');
        delete_secure_cookie('admin_refresh_token');
        session_destroy();
        header('Location: https://aarambh.heyyguru.in');
        exit;
    }
}

$db = getDB();

// Handle call status update (with CSRF validation)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_call_status') {
        // Validate CSRF token
        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            exit('CSRF validation failed.');
        }

        $studentId = InputValidator::validateInt($_POST['student_id'] ?? 0);
        $callStatus = InputValidator::validateEnum($_POST['call_status'] ?? '', ['not_called', 'called', 'follow_up', 'converted', 'not_interested']);
        $notes = InputValidator::validateString($_POST['notes'] ?? '', 1000);
        
        if ($studentId > 0 && in_array($callStatus, ['not_called', 'called', 'follow_up', 'converted', 'not_interested'])) {
            $stmt = $db->prepare("UPDATE students SET call_status = ?, notes = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$callStatus, $notes, $studentId]);
        }
        header('Location: index.php?' . http_build_query($_GET));
        exit;
    }
}

// Filters
$statusFilter = InputValidator::validateEnum($_GET['status'] ?? 'all', ['all', 'lead', 'payment_initiated', 'paid', 'enrolled']) ?: 'all';
$callFilter = InputValidator::validateEnum($_GET['call_status'] ?? 'all', ['all', 'not_called', 'called', 'follow_up', 'converted', 'not_interested']) ?: 'all';
$search = InputValidator::validateString($_GET['search'] ?? '', 100);
$page = max(1, InputValidator::validateInt($_GET['page'] ?? 1) ?: 1);
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
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-logo">
                <h2><i data-lucide="bar-chart-2"></i> Aarambh Admin</h2>
                <button class="close-sidebar" id="closeSidebar"><i data-lucide="x"></i></button>
            </div>
            <nav class="sidebar-nav">
                <?php
                    $cStatus = $_GET['status'] ?? '';
                    $cCall = $_GET['call_status'] ?? '';
                ?>
                <a href="index.php" class="<?php echo ($cStatus === '' && $cCall === '') ? 'active' : ''; ?>"><i data-lucide="layout-dashboard"></i> Dashboard</a>
                <a href="visitors.php"><i data-lucide="users"></i> Visitors</a>
                <a href="index.php?status=lead" class="<?php echo ($cStatus === 'lead') ? 'active' : ''; ?>"><i data-lucide="target"></i> Leads</a>
                <a href="index.php?status=paid" class="<?php echo ($cStatus === 'paid') ? 'active' : ''; ?>"><i data-lucide="credit-card"></i> Paid Students</a>
                <a href="index.php?call_status=not_called" class="<?php echo ($cCall === 'not_called') ? 'active' : ''; ?>"><i data-lucide="phone-missed"></i> Not Called</a>
                <a href="index.php?call_status=follow_up" class="<?php echo ($cCall === 'follow_up') ? 'active' : ''; ?>"><i data-lucide="refresh-cw"></i> Follow Up</a>
                <a href="export.php"><i data-lucide="download"></i> Export CSV</a>
                <form method="POST" action="" style="margin:0;padding:0;">
                    <?php echo csrf_hidden_field(); ?>
                    <input type="hidden" name="action" value="logout">
                    <button type="submit" class="logout" style="background:none;border:none;cursor:pointer;width:100%;text-align:left;font:inherit;color:inherit;padding:inherit;"><i data-lucide="log-out"></i> Logout</button>
                </form>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-header">
                <div class="header-left">
                    <button class="mobile-toggle" id="mobileToggle">
                        <i data-lucide="menu"></i>
                    </button>
                    <h1>Dashboard</h1>
                </div>
                <span class="user-greeting">Welcome, <?php echo sanitize($admin_username); ?></span>
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
                    <button type="submit" class="btn btn-primary btn-sm"><i data-lucide="filter"></i> Filter</button>
                    <a href="index.php" class="btn btn-outline btn-sm"><i data-lucide="x-circle"></i> Reset</a>
                </form>
            </div>

            <!-- Students Table -->
            <div class="table-container">
                <div class="table-header">
                    <h3>Students (<?php echo $totalStudents; ?> total)</h3>
                    <a href="export.php?<?php echo http_build_query($_GET); ?>" class="btn btn-sm btn-outline"><i data-lucide="download"></i> Export CSV</a>
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
                                <th>IP Address</th>
                                <th>Source/UTM</th>
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
                                            <i data-lucide="phone" style="width:14px;height:14px;"></i> <?php echo $s['phone']; ?>
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
                                    <td><code style="font-size:0.8rem;color:#8890B0;"><?php echo sanitize($s['ip_address'] ?: '-'); ?></code></td>
                                    <td>
                                        <?php 
                                            $utmStr = sanitize($s['utm_source'] ?: '');
                                            if (!empty($s['utm_campaign'])) $utmStr .= ' / ' . sanitize($s['utm_campaign']);
                                            echo $utmStr ?: '-';
                                        ?>
                                    </td>
                                    <td><?php echo date('d M, H:i', strtotime($s['created_at'])); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline action-btn" onclick="openUpdateModal(<?php echo $s['id']; ?>, '<?php echo $s['call_status']; ?>', '<?php echo addslashes($s['notes'] ?? ''); ?>')">
                                            <i data-lucide="edit-3"></i> Update
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
                <?php echo csrf_hidden_field(); ?>
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
        // Initialize Lucide Icons
        lucide.createIcons();

        // Modal Logic
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

        // Mobile Sidebar Logic
        const mobileToggle = document.getElementById('mobileToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const closeSidebar = document.getElementById('closeSidebar');

        function toggleSidebar() {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('open');
        }

        mobileToggle.addEventListener('click', toggleSidebar);
        closeSidebar.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', toggleSidebar);
    </script>
</body>
</html>
