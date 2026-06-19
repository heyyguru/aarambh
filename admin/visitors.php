<?php
/**
 * Admin Dashboard — Visitors
 */
define('AARAMBH_INIT', true);
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth.php';

$admin_username = require_admin_auth();

$db = getDB();

$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 50;
$offset = ($page - 1) * $perPage;

$countStmt = $db->query("SELECT COUNT(*) as total FROM page_visits");
$totalVisits = $countStmt->fetch()['total'];
$totalPages = ceil($totalVisits / $perPage);

$stmt = $db->query("SELECT * FROM page_visits ORDER BY visited_at DESC LIMIT {$perPage} OFFSET {$offset}");
$visits = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visitors — Aarambh Admin</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="admin-layout">
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-logo">
                <h2><i data-lucide="bar-chart-2"></i> Aarambh Admin</h2>
                <button class="close-sidebar" id="closeSidebar"><i data-lucide="x"></i></button>
            </div>
            <nav class="sidebar-nav">
                <a href="index.php"><i data-lucide="layout-dashboard"></i> Dashboard</a>
                <a href="visitors.php" class="active"><i data-lucide="users"></i> Visitors</a>
                <a href="index.php?status=lead"><i data-lucide="target"></i> Leads</a>
                <a href="index.php?status=paid"><i data-lucide="credit-card"></i> Paid Students</a>
                <a href="index.php?call_status=not_called"><i data-lucide="phone-missed"></i> Not Called</a>
                <a href="index.php?call_status=follow_up"><i data-lucide="refresh-cw"></i> Follow Up</a>
                <a href="export.php"><i data-lucide="download"></i> Export CSV</a>
                <a href="?logout=1" class="logout"><i data-lucide="log-out"></i> Logout</a>
            </nav>
        </aside>

        <main class="admin-main">
            <header class="admin-header">
                <div class="header-left">
                    <button class="mobile-toggle" id="mobileToggle">
                        <i data-lucide="menu"></i>
                    </button>
                    <h1>Visitors Tracker</h1>
                </div>
                <span class="user-greeting">Welcome, <?php echo sanitize($admin_username); ?></span>
            </header>

            <div class="table-container">
                <div class="table-header">
                    <h3>Page Visits (<?php echo $totalVisits; ?> total)</h3>
                </div>
                <div class="table-scroll">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>IP Address</th>
                                <th>Page URL</th>
                                <th>Referrer</th>
                                <th>Source / Medium</th>
                                <th>Campaign</th>
                                <th>User Agent</th>
                                <th>Visited At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($visits)): ?>
                                <tr><td colspan="8" style="text-align:center;padding:2rem;">No visits found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($visits as $v): ?>
                                <tr>
                                    <td><?php echo $v['id']; ?></td>
                                    <td><code style="background:#f1f5f9;padding:0.2rem 0.4rem;border-radius:4px;color:#475569;"><?php echo sanitize($v['ip_address'] ?: '-'); ?></code></td>
                                    <td><?php echo sanitize($v['page_url'] ?: '/'); ?></td>
                                    <td style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?php echo sanitize($v['referrer'] ?: '-'); ?>">
                                        <?php echo sanitize($v['referrer'] ?: '-'); ?>
                                    </td>
                                    <td>
                                        <?php 
                                            $src = sanitize($v['utm_source'] ?: '-');
                                            $med = sanitize($v['utm_medium'] ?: '');
                                            echo $med ? "{$src} / {$med}" : $src;
                                        ?>
                                    </td>
                                    <td><?php echo sanitize($v['utm_campaign'] ?: '-'); ?></td>
                                    <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?php echo sanitize($v['user_agent'] ?: '-'); ?>">
                                        <?php echo sanitize($v['user_agent'] ?: '-'); ?>
                                    </td>
                                    <td><?php echo date('d M, H:i', strtotime($v['visited_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>" class="page-btn <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();

        // Mobile Sidebar Logic
        const mobileToggle = document.getElementById('mobileToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const closeSidebar = document.getElementById('closeSidebar');

        function toggleSidebar() {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('open');
        }

        if(mobileToggle) mobileToggle.addEventListener('click', toggleSidebar);
        if(closeSidebar) closeSidebar.addEventListener('click', toggleSidebar);
        if(overlay) overlay.addEventListener('click', toggleSidebar);
    </script>
</body>
</html>
