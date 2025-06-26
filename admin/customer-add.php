<?php
ob_start();

$pageTitle = 'Thêm Khách hàng';
require_once 'includes/header.php';

$error = '';
$success = '';

// Lấy danh sách nhóm khách hàng
$stmt = $pdo->query("SELECT * FROM customer_groups ORDER BY name");
$groups = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $password = $_POST['password'];
    $group_id = (int)$_POST['group_id'];
    $status = $_POST['status'];
    
    // Validation
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Vui lòng nhập đầy đủ thông tin bắt buộc!';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự!';
    } else {
        // Kiểm tra email đã tồn tại
        $stmt = $pdo->prepare("SELECT id FROM customers WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $error = 'Email này đã được sử dụng!';
        } else {
            // Xử lý upload avatar
            $avatar = 'default-avatar.png';
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
                $uploadedFile = uploadFile($_FILES['avatar'], '../uploads/avatars/');
                if ($uploadedFile) {
                    $avatar = $uploadedFile;
                }
            }
            
            // Thêm khách hàng mới
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                INSERT INTO customers (name, email, phone, address, password, group_id, avatar, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            if ($stmt->execute([$name, $email, $phone, $address, $hashedPassword, $group_id, $avatar, $status])) {
                redirect('customers.php');
            } else {
                $error = 'Có lỗi xảy ra, vui lòng thử lại!';
            }
        }
    }
}
?>

<?php if ($error): ?>
    <?= showAlert($error, 'danger') ?>
<?php endif; ?>

<?php if ($success): ?>
    <?= showAlert($success, 'success') ?>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-0">Thêm Khách hàng</h2>
        <p class="text-muted">Thêm khách hàng mới vào hệ thống</p>
    </div>
    <a href="customers.php" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>
        Quay lại
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-user-plus me-2"></i>
                    Thông tin khách hàng
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Họ tên *</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Số điện thoại</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Mật khẩu *</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="form-text">Tối thiểu 6 ký tự</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Địa chỉ</label>
                        <textarea class="form-control" id="address" name="address" rows="3"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="group_id" class="form-label">Nhóm khách hàng</label>
                            <select class="form-select" id="group_id" name="group_id" required>
                                <option value="">Chọn nhóm</option>
                                <?php foreach ($groups as $group): ?>
                                <option value="<?= $group['id'] ?>" 
                                        <?= (isset($_POST['group_id']) && $_POST['group_id'] == $group['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($group['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Trạng thái</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active" <?= (isset($_POST['status']) && $_POST['status'] == 'active') ? 'selected' : 'selected' ?>>
                                    Hoạt động
                                </option>
                                <option value="inactive" <?= (isset($_POST['status']) && $_POST['status'] == 'inactive') ? 'selected' : '' ?>>
                                    Không hoạt động
                                </option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="avatar" class="form-label">Ảnh đại diện</label>
                        <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
                        <div class="form-text">Chấp nhận file: JPG, JPEG, PNG, GIF. Tối đa 2MB.</div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            Lưu khách hàng
                        </button>
                        <a href="customers.php" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>
                            Hủy bỏ
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Hướng dẫn
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6 class="alert-heading">Lưu ý:</h6>
                    <ul class="mb-0 small">
                        <li>Các trường có dấu (*) là bắt buộc</li>
                        <li>Email phải là duy nhất trong hệ thống</li>
                        <li>Mật khẩu tối thiểu 6 ký tự</li>
                        <li>Ảnh đại diện không bắt buộc</li>
                        <li>Khách hàng mới mặc định ở trạng thái hoạt động</li>
                    </ul>
                </div>
                
                <div class="mt-3">
                    <h6>Nhóm khách hàng:</h6>
                    <?php foreach ($groups as $group): ?>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-<?= $group['name'] == 'VIP' ? 'warning' : 'secondary' ?>">
                            <?= htmlspecialchars($group['name']) ?>
                        </span>
                        <small class="text-muted"><?= htmlspecialchars($group['description']) ?></small>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
