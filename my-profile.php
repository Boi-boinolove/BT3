<?php
require_once 'includes/functions.php';

// Kiểm tra đăng nhập
if (!isCustomerLoggedIn()) {
    redirect('login.php');
}

$error = '';
$success = '';

// Lấy thông tin khách hàng
$stmt = $pdo->prepare("
    SELECT c.*, g.name as group_name 
    FROM customers c 
    LEFT JOIN customer_groups g ON c.group_id = g.id 
    WHERE c.id = ?
");
$stmt->execute([$_SESSION['customer_id']]);
$customer = $stmt->fetch();

// Lấy lịch sử giao dịch
$stmt = $pdo->prepare("
    SELECT * FROM transactions 
    WHERE customer_id = ? 
    ORDER BY created_at DESC 
    LIMIT 10
");
$stmt->execute([$_SESSION['customer_id']]);
$transactions = $stmt->fetchAll();

// Lấy lịch hẹn của khách hàng
$stmt = $pdo->prepare("SELECT * FROM appointments WHERE customer_id = ? ORDER BY appointment_time DESC");
$stmt->execute([$_SESSION['customer_id']]);
$appointments = $stmt->fetchAll();

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    if (empty($name)) {
        $error = 'Vui lòng nhập họ tên!';
    } else {
        $stmt = $pdo->prepare("UPDATE customers SET name = ?, phone = ?, address = ? WHERE id = ?");
        if ($stmt->execute([$name, $phone, $address, $_SESSION['customer_id']])) {
            $success = 'Cập nhật thông tin thành công!';
            $_SESSION['customer_name'] = $name;
            // Refresh customer data
            $stmt = $pdo->prepare("
                SELECT c.*, g.name as group_name 
                FROM customers c 
                LEFT JOIN customer_groups g ON c.group_id = g.id 
                WHERE c.id = ?
            ");
            $stmt->execute([$_SESSION['customer_id']]);
            $customer = $stmt->fetch();
        } else {
            $error = 'Có lỗi xảy ra, vui lòng thử lại!';
        }
    }
}

// Xử lý gửi đánh giá
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);
    
    if ($rating < 1 || $rating > 5) {
        $error = 'Vui lòng chọn số sao từ 1 đến 5!';
    } elseif (empty($comment)) {
        $error = 'Vui lòng nhập nội dung đánh giá!';
    } else {
        $stmt = $pdo->prepare("INSERT INTO reviews (customer_id, rating, comment) VALUES (?, ?, ?)");
        if ($stmt->execute([$_SESSION['customer_id'], $rating, $comment])) {
            $success = 'Gửi đánh giá thành công! Cảm ơn bạn đã phản hồi.';
        } else {
            $error = 'Có lỗi xảy ra, vui lòng thử lại!';
        }
    }
}

// Đăng xuất
if (isset($_GET['logout'])) {
    session_destroy();
    redirect('index.php');
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ cá nhân - CRM System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-users me-2"></i>
                CRM System
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    Xin chào, <?= htmlspecialchars($customer['name']) ?>
                </span>
                <a class="nav-link" href="?logout=1">
                    <i class="fas fa-sign-out-alt me-1"></i>
                    Đăng xuất
                </a>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <?php if ($error): ?>
            <?= showAlert($error, 'danger') ?>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <?= showAlert($success, 'success') ?>
        <?php endif; ?>

        <div class="row">
            <!-- Profile Info -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <img src="uploads/avatars/<?= htmlspecialchars($customer['avatar']) ?>" 
                             alt="Avatar" class="avatar-lg mb-3"
                             onerror="this.src='assets/img/default-avatar.png'">
                        <h5 class="card-title"><?= htmlspecialchars($customer['name']) ?></h5>
                        <p class="text-muted"><?= htmlspecialchars($customer['email']) ?></p>
                        <span class="badge bg-<?= $customer['group_name'] == 'VIP' ? 'warning' : 'secondary' ?> mb-3">
                            <i class="fas fa-<?= $customer['group_name'] == 'VIP' ? 'crown' : 'user' ?> me-1"></i>
                            <?= htmlspecialchars($customer['group_name']) ?>
                        </span>
                        <div class="text-muted small">
                            <i class="fas fa-calendar me-1"></i>
                            Tham gia: <?= formatDate($customer['created_at']) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Form -->
            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-user-edit me-2"></i>
                            Thông tin cá nhân
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Họ tên</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?= htmlspecialchars($customer['name']) ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" 
                                           value="<?= htmlspecialchars($customer['email']) ?>" readonly>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Số điện thoại</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?= htmlspecialchars($customer['phone']) ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="address" class="form-label">Địa chỉ</label>
                                    <input type="text" class="form-control" id="address" name="address" 
                                           value="<?= htmlspecialchars($customer['address']) ?>">
                                </div>
                            </div>
                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                Cập nhật thông tin
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Transaction History -->
            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-history me-2"></i>
                            Lịch sử giao dịch
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($transactions)): ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p>Chưa có giao dịch nào</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Ngày</th>
                                            <th>Mô tả</th>
                                            <th>Số tiền</th>
                                            <th>Loại</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($transactions as $transaction): ?>
                                        <tr>
                                            <td><?= formatDate($transaction['created_at']) ?></td>
                                            <td><?= htmlspecialchars($transaction['description']) ?></td>
                                            <td class="fw-bold text-<?= $transaction['type'] == 'income' ? 'success' : 'danger' ?>">
                                                <?= formatCurrency($transaction['amount']) ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $transaction['type'] == 'income' ? 'success' : 'danger' ?>">
                                                    <?= $transaction['type'] == 'income' ? 'Thu' : 'Chi' ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Appointment Table -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar-check me-2"></i>
                            Lịch hẹn của bạn
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($appointments)): ?>
                            <div class="text-center py-4 text-muted">Chưa có lịch hẹn nào</div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Thời gian</th>
                                        <th>Ghi chú</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($appointments as $a): ?>
                                    <tr>
                                        <td><?= date('d/m/Y H:i', strtotime($a['appointment_time'])) ?></td>
                                        <td><?= htmlspecialchars($a['note']) ?></td>
                                        <td>
                                            <span class="badge <?php if($a['status']=='confirmed') echo 'bg-success'; elseif($a['status']=='pending') echo 'bg-warning text-dark'; else echo 'bg-secondary'; ?>">
                                                <?= $a['status']=='confirmed' ? 'Đã xác nhận' : ($a['status']=='pending' ? 'Chờ xác nhận' : 'Đã hủy') ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
