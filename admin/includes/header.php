<?php
require_once '../includes/functions.php';

// Kiểm tra đăng nhập admin
if (!isAdminLoggedIn()) {
    redirect('login.php');
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Quản trị' ?> - CRM System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <nav class="admin-sidebar">
            <div class="p-3">
                <a href="dashboard.php" class="d-flex align-items-center mb-4 text-white text-decoration-none justify-content-center gap-2">
                    <i class="fas fa-cubes"></i>
                    <span class="fw-bold fs-4">CRM Admin</span>
                </a>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>" 
                           href="dashboard.php">
                            <i class="fas fa-home"></i>
                            Tổng quan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= in_array(basename($_SERVER['PHP_SELF']), ['customers.php', 'customer-add.php', 'customer-edit.php']) ? 'active' : '' ?>" 
                           href="customers.php">
                            <i class="fas fa-users"></i>
                            Khách hàng
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'groups.php' ? 'active' : '' ?>" 
                           href="groups.php">
                            <i class="fas fa-layer-group"></i>
                            Nhóm khách hàng
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'transactions.php' ? 'active' : '' ?>" 
                           href="transactions.php">
                            <i class="fas fa-exchange-alt"></i>
                            Giao dịch
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'appointments.php' ? 'active' : '' ?>" 
                           href="appointments.php">
                            <i class="fas fa-calendar-check"></i>
                            Lịch hẹn
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="flex-grow-1">
            <!-- Header -->
            <header class="admin-header p-3 d-flex justify-content-between align-items-center">
                <div>
                    <button class="btn btn-outline-primary d-md-none" type="button" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h5 class="mb-0 d-inline-block ms-2"><?= $pageTitle ?? 'Quản trị' ?></h5>
                </div>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" 
                            data-bs-toggle="dropdown">
                        <i class="fas fa-user me-2"></i>
                        <?= htmlspecialchars($_SESSION['admin_username']) ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="../index.php" target="_blank">
                                <i class="fas fa-globe me-2"></i>
                                Xem website
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="?logout=1">
                                <i class="fas fa-sign-out-alt me-2"></i>
                                Đăng xuất
                            </a>
                        </li>
                    </ul>
                </div>
            </header>

            <!-- Content -->
            <main class="p-4">
                <?php
                // Xử lý đăng xuất
                if (isset($_GET['logout'])) {
                    session_destroy();
                    redirect('login.php');
                }
                ?>
