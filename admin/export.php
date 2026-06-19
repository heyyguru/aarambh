<?php
/**
 * Export CSV — Download student data as CSV
 */
define('AARAMBH_INIT', true);
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth.php';

require_admin_auth();

$db = getDB();

// Filters (same as dashboard)
$statusFilter = $_GET['status'] ?? 'all';
$callFilter = $_GET['call_status'] ?? 'all';
$search = sanitize($_GET['search'] ?? '');

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
    $where[] = "(name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $searchTerm = "%{$search}%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $db->prepare("SELECT * FROM students {$whereClause} ORDER BY created_at DESC");
$stmt->execute($params);
$students = $stmt->fetchAll();

// Set headers for CSV download
$filename = 'aarambh_students_' . date('Y-m-d_H-i') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// UTF-8 BOM for Excel
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Header row
fputcsv($output, [
    'ID', 'Name', 'Email', 'Phone', 'Class', 'City',
    'Status', 'Call Status', 'Notes', 'Payment ID',
    'UTM Source', 'UTM Medium', 'UTM Campaign',
    'Email Sent', 'Created At'
]);

// Data rows
foreach ($students as $s) {
    fputcsv($output, [
        $s['id'],
        $s['name'],
        $s['email'],
        $s['phone'],
        'Class ' . $s['student_class'],
        $s['city'] ?: '-',
        ucfirst(str_replace('_', ' ', $s['status'])),
        ucfirst(str_replace('_', ' ', $s['call_status'])),
        $s['notes'] ?: '-',
        $s['razorpay_payment_id'] ?: '-',
        $s['utm_source'] ?: '-',
        $s['utm_medium'] ?: '-',
        $s['utm_campaign'] ?: '-',
        $s['email_sent'] ? 'Yes' : 'No',
        $s['created_at']
    ]);
}

fclose($output);
exit;
