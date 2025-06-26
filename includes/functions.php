<?php
require_once 'db.php';

// Hàm kiểm tra đăng nhập admin
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

// Hàm kiểm tra đăng nhập customer
function isCustomerLoggedIn() {
    return isset($_SESSION['customer_id']) && !empty($_SESSION['customer_id']);
}

// Hàm redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Hàm hiển thị thông báo
function showAlert($message, $type = 'success') {
    return "<div class='alert alert-$type alert-dismissible fade show' role='alert'>
                $message
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
            </div>";
}

// Hàm format tiền tệ
function formatCurrency($amount) {
    return number_format($amount, 0, ',', '.') . ' VNĐ';
}

// Hàm format ngày tháng
function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}

// Hàm upload file
function uploadFile($file, $uploadDir = 'uploads/avatars/') {
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($fileExtension, $allowedTypes)) {
        return false;
    }
    
    $fileName = uniqid() . '.' . $fileExtension;
    $uploadPath = $uploadDir . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return $fileName;
    }
    
    return false;
}

// Hàm lấy thống kê dashboard
function getDashboardStats($pdo) {
    $stats = [];
    
    // Tổng số khách hàng
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM customers WHERE status = 'active'");
    $stats['total_customers'] = $stmt->fetch()['total'];
    
    // Tổng số giao dịch
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM transactions");
    $stats['total_transactions'] = $stmt->fetch()['total'];
    
    // Tổng doanh thu
    $stmt = $pdo->query("SELECT SUM(amount) as total FROM transactions WHERE type = 'income'");
    $stats['total_revenue'] = $stmt->fetch()['total'] ?? 0;
    
    // Lịch hẹn chờ xác nhận
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM appointments WHERE status = 'pending'");
    $stats['pending_appointments'] = $stmt->fetch()['total'];
    
    return $stats;
}

// Hàm lấy dữ liệu biểu đồ
function getChartData($pdo) {
    // Doanh thu theo tháng (6 tháng gần nhất)
    $stmt = $pdo->query("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            SUM(amount) as revenue
        FROM transactions 
        WHERE type = 'income' 
        AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month
    ");
    
    $chartData = [];
    while ($row = $stmt->fetch()) {
        $chartData['months'][] = $row['month'];
        $chartData['revenue'][] = $row['revenue'];
    }
    
    return $chartData;
}

// Hàm phân trang
function paginate($pdo, $table, $page = 1, $limit = 10, $conditions = '') {
    $offset = ($page - 1) * $limit;
    
    // Đếm tổng số bản ghi
    $countSql = "SELECT COUNT(*) as total FROM $table";
    if ($conditions) {
        $countSql .= " WHERE $conditions";
    }
    $stmt = $pdo->query($countSql);
    $totalRecords = $stmt->fetch()['total'];
    
    // Tính tổng số trang
    $totalPages = ceil($totalRecords / $limit);
    
    return [
        'total_records' => $totalRecords,
        'total_pages' => $totalPages,
        'current_page' => $page,
        'limit' => $limit,
        'offset' => $offset
    ];
}
?>
