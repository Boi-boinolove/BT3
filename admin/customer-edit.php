<?php
ob_start();
$pageTitle = 'Sửa Khách hàng';
require_once 'includes/header.php';

$error = '';
$success = '';

// Lấy ID khách hàng
$customerId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($customerId <= 0) {
    redirect('customers.php');
}

// Lấy thông tin khách hàng
$stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$customerId]);
$customer = $stmt->fetch();

if (!$customer) {
    redirect('customers.php');
}

// Lấy danh sách nhóm khách hàng
$stmt = $pdo->query("SELECT * FROM customer_groups ORDER BY name");
$groups = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $group_id = (int)$_POST['group_id'];
    $status = $_POST['status'];
    
    // Validation
    if (empty($name) || empty($email)) {
        $error = 'Vui lòng nhập đầy đủ thông tin bắt buộc!';
    } else {
        // Kiểm tra email đã tồn tại (trừ email hiện tại)
        $stmt = $pdo->prepare("SELECT id FROM customers WHERE email = ? AND id != ?");
        $stmt->execute([$email, $customerId]);
        
        if ($stmt->fetch()) {
            $error = 'Email này đã được sử dụng bởi khách hàng khác!';
        } else {
            // Xử lý upload avatar
            $avatar = $customer['avatar'];
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
                $uploadedFile = uploadFile($_FILES['avatar'], '../uploads/avatars/');
                if ($uploadedFile) {
                    // Xóa avatar cũ (nếu không phải default)
                    if ($customer['avatar'] != 'default-avatar.png' && file_exists('../uploads/avatars/' . $customer['avatar'])) {
                        unlink('../uploads/avatars/' . $customer['avatar']);
                    }
                    $avatar = $uploadedFile;
                }
            }
            
            // Cập nhật thông tin khách hàng
            $stmt = $pdo->prepare("
                UPDATE customers 
                SET name = ?, email = ?, phone = ?, address = ?, group_id = ?, avatar = ?, status = ?
                WHERE id = ?
            ");
            
            if ($stmt->execute([$name, $email, $phone, $address, $group_id, $avatar, $status, $customerId])) {
                redirect('customers.php');
                exit;
            } else {
                $error = 'Có lỗi xảy ra, vui lòng thử lại!';
            }
        }
    }
    
    // Xử lý đổi mật khẩu
    if (isset($_POST['change_password']) && !empty($_POST['new_password'])) {
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        if (strlen($newPassword) < 6) {
            $error = 'Mật khẩu mới phải có ít nhất 6 ký tự!';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'Mật khẩu xác nhận không khớp!';
        } else {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE customers SET password = ? WHERE id = ?");
            
            if ($stmt->execute([$hashedPassword, $customerId])) {
                $success = 'Đổi mật khẩu thành công!';
            } else {
                $error = 'Có lỗi xảy ra khi đổi mật khẩu!';
            }
        }
    }
}

// Lấy thống kê khách hàng
$stmt = $pdo->prepare("
    SELECT 
        COUNT(t.id) as transaction_count,
        COALESCE(SUM(CASE WHEN t.type = 'income' THEN t.amount ELSE 0 END), 0) as total_spent,
        COUNT(r.id) as review_count,
        COALESCE(AVG(r.rating), 0) as avg_rating
    FROM customers c
    LEFT JOIN transactions t ON c.id = t.customer_id
    LEFT JOIN reviews r ON c.id = r.customer_id AND r.status = 'approved'
    WHERE c.id = ?
    GROUP BY c.id
");
$stmt->execute([$customerId]);
$stats = $stmt->fetch();
?>

<?php if ($error): ?>
    <?= showAlert($error, 'danger') ?>
<?php endif; ?>

<?php if ($success): ?>
    <?= showAlert($success, 'success') ?>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-0">Sửa Khách hàng</h2>
        <p class="text-muted">Cập nhật thông tin khách hàng: <?= htmlspecialchars($customer['name']) ?></p>
    </div>
    <a href="customers.php" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>
        Quay lại
    </a>
</div>

<div class="row">
    <!-- Customer Info -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-user-edit me-2"></i>
                    Thông tin cơ bản
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Họ tên *</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?= htmlspecialchars($customer['name']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($customer['email']) ?>" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Số điện thoại</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?= htmlspecialchars($customer['phone']) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="group_id" class="form-label">Nhóm khách hàng</label>
                            <select class="form-select" id="group_id" name="group_id" required>
                                <?php foreach ($groups as $group): ?>
                                <option value="<?= $group['id'] ?>" 
                                        <?= $customer['group_id'] == $group['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($group['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="address" class="form-label">Địa chỉ</label>
                            <textarea class="form-control" id="address" name="address" rows="3"><?= htmlspecialchars($customer['address']) ?></textarea>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="status" class="form-label">Trạng thái</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active" <?= $customer['status'] == 'active' ? 'selected' : '' ?>>
                                    Hoạt động
                                </option>
                                <option value="inactive" <?= $customer['status'] == 'inactive' ? 'selected' : '' ?>>
                                    Không hoạt động
                                </option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="avatar" class="form-label">Ảnh đại diện</label>
                            <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
                            <div class="form-text">Để trống nếu không muốn thay đổi</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ảnh hiện tại</label>
                            <div>
                                <img src="../uploads/avatars/<?= htmlspecialchars($customer['avatar']) ?>" 
                                     alt="Avatar" class="avatar-lg"
                                     onerror="this.src='../assets/img/default-avatar.png'">
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>
                        Cập nhật thông tin
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
