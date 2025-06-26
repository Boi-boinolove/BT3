<?php
$pageTitle = '<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold">Tổng quan </h2>
        <p class="text-muted">Tổng quan hệ thống quản lý khách hàng</p>
    </div>
</div>';
require_once 'includes/header.php';

// Lấy thống kê
$stats = getDashboardStats($pdo);

// Lấy giao dịch gần đây
$stmt = $pdo->prepare("
    SELECT t.*, c.name as customer_name 
    FROM transactions t 
    JOIN customers c ON t.customer_id = c.id 
    ORDER BY t.created_at DESC 
    LIMIT 4
");
$stmt->execute();
$recentTransactions = $stmt->fetchAll();

// Lấy khách hàng gần đây
$stmt = $pdo->prepare("
    SELECT c.*, g.name as group_name
    FROM customers c
    LEFT JOIN customer_groups g ON c.group_id = g.id
    ORDER BY c.created_at DESC
    LIMIT 7
");
$stmt->execute();
$recentCustomers = $stmt->fetchAll();

// Lấy dữ liệu biểu đồ
$chartData = getChartData($pdo);
?>


<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card border-left-primary">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Tổng khách hàng
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= number_format($stats['total_customers']) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="stat-icon bg-primary text-white">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card border-left-success">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Tổng doanh thu
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= formatCurrency($stats['total_revenue']) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="stat-icon bg-success text-white">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card border-left-info">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Tổng giao dịch
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= number_format($stats['total_transactions']) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="stat-icon bg-info text-white">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card border-left-warning">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Lịch hẹn sắp tới
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= number_format($stats['pending_appointments']) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="stat-icon bg-warning text-white">
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Revenue Chart -->
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-chart-area me-2"></i>
                    Biểu đồ doanh thu (6 tháng gần nhất)
                </h6>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-list me-2"></i>
                    Giao dịch gần đây
                </h6>
            </div>
            <div class="card-body">
                <?php if (empty($recentTransactions)): ?>
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <p class="mb-0">Chưa có giao dịch</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($recentTransactions as $transaction): ?>
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            <div class="icon-circle bg-<?= $transaction['type'] == 'income' ? 'success' : 'danger' ?>">
                                <i class="fas fa-<?= $transaction['type'] == 'income' ? 'arrow-up' : 'arrow-down' ?> text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="small text-gray-500"><?= formatDate($transaction['created_at']) ?></div>
                            <div class="fw-bold"><?= htmlspecialchars($transaction['customer_name']) ?></div>
                            <div class="small"><?= htmlspecialchars($transaction['description']) ?></div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold text-<?= $transaction['type'] == 'income' ? 'success' : 'danger' ?>">
                                <?= formatCurrency($transaction['amount']) ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <div class="text-center">
                        <a href="transactions.php" class="btn btn-sm btn-primary">Xem tất cả</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Pending Reviews -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-info">
                    <i class="fas fa-users me-2"></i>
                    Khách hàng gần đây
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background: linear-gradient(90deg, #6a11cb 0%, #2575fc 100%); color: #fff;">
                            <tr>
                                <th>AVATAR</th>
                                <th>THÔNG TIN</th>
                                <th>NHÓM</th>
                                <th>GIAO DỊCH</th>
                                <th>TỔNG CHI TIÊU</th>
                                <th>TRẠNG THÁI</th>
                                <th>NGÀY THAM GIA</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentCustomers)): ?>
                                <tr><td colspan="7" class="text-center text-muted py-3">Chưa có khách hàng mới</td></tr>
                            <?php else: ?>
                                <?php foreach ($recentCustomers as $customer): ?>
                                <tr>
                                    <td>
                                        <img src="<?php
                                            $avatar = htmlspecialchars($customer['avatar']);
                                            if (empty($avatar) || $avatar == 'default-avatar.png') {
                                                echo '../assets/img/default-avatar.png';
                                            } else {
                                                echo '../uploads/avatars/' . $avatar;
                                            }
                                        ?>" alt="Avatar" class="rounded-circle" width="48" height="48">
                                    </td>
                                    <td>
                                        <div class="fw-bold mb-1"><?= htmlspecialchars($customer['name']) ?></div>
                                        <div class="text-muted small"><i class="fas fa-envelope me-1"></i> <?= htmlspecialchars($customer['email']) ?></div>
                                        <?php if (!empty($customer['phone'])): ?>
                                            <div class="text-muted small"><i class="fas fa-phone me-1"></i> <?= htmlspecialchars($customer['phone']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $customer['group_name'] == 'VIP' ? 'warning' : 'secondary' ?>">
                                            <i class="fas fa-<?= $customer['group_name'] == 'VIP' ? 'crown' : 'user' ?>"></i>
                                            <?= htmlspecialchars($customer['group_name']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php
                                            // Đếm số giao dịch
                                            $stmt = $pdo->prepare('SELECT COUNT(*) FROM transactions WHERE customer_id = ?');
                                            $stmt->execute([$customer['id']]);
                                            $count = $stmt->fetchColumn();
                                            echo number_format($count) . ' giao dịch';
                                            ?>
                                        </span>
                                    </td>
                                    <td class="fw-bold text-success">
                                        <?php
                                        // Tổng chi tiêu
                                        $stmt = $pdo->prepare('SELECT COALESCE(SUM(amount),0) FROM transactions WHERE customer_id = ? AND type = "income"');
                                        $stmt->execute([$customer['id']]);
                                        $total = $stmt->fetchColumn();
                                        echo formatCurrency($total);
                                        ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $customer['status'] == 'active' ? 'success' : 'danger' ?>">
                                            <?= $customer['status'] == 'active' ? 'Hoạt động' : 'Không hoạt động' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= formatDate($customer['created_at']) ?>
                                        </small>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Revenue Chart
const ctx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($chartData['months'] ?? []) ?>,
        datasets: [{
            label: 'Doanh thu (VNĐ)',
            data: <?= json_encode($chartData['revenue'] ?? []) ?>,
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return new Intl.NumberFormat('vi-VN').format(value) + ' VNĐ';
                    }
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Doanh thu: ' + new Intl.NumberFormat('vi-VN').format(context.parsed.y) + ' VNĐ';
                    }
                }
            }
        }
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
