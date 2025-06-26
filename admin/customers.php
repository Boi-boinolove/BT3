<?php
$pageTitle = 'Khách hàng';
require_once 'includes/header.php';

$error = '';
$success = '';

// Xử lý xóa khách hàng
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $customerId = (int)$_GET['delete'];
    
    try {
        $pdo->beginTransaction();
        
        // Xóa các bảng liên quan trước
        $pdo->prepare("DELETE FROM reviews WHERE customer_id = ?")->execute([$customerId]);
        $pdo->prepare("DELETE FROM transactions WHERE customer_id = ?")->execute([$customerId]);
        $pdo->prepare("DELETE FROM customers WHERE id = ?")->execute([$customerId]);
        
        $pdo->commit();
        $success = 'Xóa khách hàng thành công!';
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = 'Có lỗi xảy ra khi xóa khách hàng!';
    }
}

// Phân trang và tìm kiếm
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$groupFilter = isset($_GET['group']) ? (int)$_GET['group'] : 0;

// Xây dựng điều kiện WHERE
$whereConditions = [];
$params = [];

if (!empty($search)) {
    $whereConditions[] = "(c.name LIKE ? OR c.email LIKE ? OR c.phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($groupFilter > 0) {
    $whereConditions[] = "c.group_id = ?";
    $params[] = $groupFilter;
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Lấy danh sách khách hàng
$sql = "
    SELECT c.*, g.name as group_name,
           COUNT(t.id) as transaction_count,
           COALESCE(SUM(t.amount), 0) as total_spent
    FROM customers c 
    LEFT JOIN customer_groups g ON c.group_id = g.id 
    LEFT JOIN transactions t ON c.id = t.customer_id AND t.type = 'income'
    $whereClause
    GROUP BY c.id
    ORDER BY c.created_at DESC 
    LIMIT $limit OFFSET " . (($page - 1) * $limit);

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$customers = $stmt->fetchAll();

// Đếm tổng số khách hàng
$countSql = "SELECT COUNT(DISTINCT c.id) as total FROM customers c LEFT JOIN customer_groups g ON c.group_id = g.id $whereClause";
$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$totalCustomers = $stmt->fetch()['total'];
$totalPages = ceil($totalCustomers / $limit);

// Lấy danh sách nhóm khách hàng
$stmt = $pdo->query("SELECT * FROM customer_groups ORDER BY name");
$groups = $stmt->fetchAll();
?>

<?php if ($error): ?>
    <?= showAlert($error, 'danger') ?>
<?php endif; ?>

<?php if ($success): ?>
    <?= showAlert($success, 'success') ?>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-0">Quản lý Khách hàng</h2>
        <p class="text-muted">Danh sách và quản lý thông tin khách hàng</p>
    </div>
    <a href="customer-add.php" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>
        Thêm khách hàng


        
    </a>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">Tìm kiếm</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="<?= htmlspecialchars($search) ?>" 
                       placeholder="Tên, email, số điện thoại...">
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="fas fa-search me-2"></i>
                        Tìm kiếm
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Customers Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-users me-2"></i>
            Danh sách khách hàng (<?= number_format($totalCustomers) ?> khách hàng)
        </h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($customers)): ?>
            <div class="text-center py-5">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Không có khách hàng nào</h5>
                <p class="text-muted">Hãy thêm khách hàng đầu tiên</p>
                <a href="customer-add.php" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    Thêm khách hàng
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Avatar</th>
                            <th>Thông tin</th>
                            <th>Nhóm</th>
                            <th>Giao dịch</th>
                            <th>Tổng chi tiêu</th>
                            <th>Trạng thái</th>
                            <th>Ngày tham gia</th>
                            <th width="120">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td>
                                <img src="<?php
                                    $avatar = htmlspecialchars($customer['avatar']);
                                    if (empty($avatar) || $avatar == 'default-avatar.png') {
                                        echo '../assets/img/default-avatar.png';
                                    } else {
                                        echo '../uploads/avatars/' . $avatar;
                                    }
                                ?>" alt="Avatar" class="avatar">
                            </td>
                            <td>
                                <div class="fw-bold"><?= htmlspecialchars($customer['name']) ?></div>
                                <div class="text-muted small"><?= htmlspecialchars($customer['email']) ?></div>
                                <?php if ($customer['phone']): ?>
                                <div class="text-muted small">
                                    <i class="fas fa-phone me-1"></i>
                                    <?= htmlspecialchars($customer['phone']) ?>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= $customer['group_name'] == 'VIP' ? 'warning' : 'secondary' ?>">
                                    <i class="fas fa-<?= $customer['group_name'] == 'VIP' ? 'crown' : 'user' ?> me-1"></i>
                                    <?= htmlspecialchars($customer['group_name']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-info">
                                    <?= number_format($customer['transaction_count']) ?> giao dịch
                                </span>
                            </td>
                            <td class="fw-bold text-success">
                                <?= formatCurrency($customer['total_spent']) ?>
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
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="customer-edit.php?id=<?= $customer['id'] ?>" 
                                       class="btn btn-outline-primary" title="Sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" 
                                            onclick="confirmDelete(<?= $customer['id'] ?>, '<?= htmlspecialchars($customer['name']) ?>')"
                                            title="Xóa">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="card-footer">
                <nav aria-label="Phân trang">
                    <ul class="pagination justify-content-center mb-0">
                        <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&group=<?= $groupFilter ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&group=<?= $groupFilter ?>">
                                <?= $i ?>
                            </a>
                        </li>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&group=<?= $groupFilter ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function confirmDelete(id, name) {
    if (confirm('Bạn có chắc chắn muốn xóa khách hàng "' + name + '"?\n\nLưu ý: Tất cả giao dịch và lịch hẹn của khách hàng này cũng sẽ bị xóa.')) {
        window.location.href = 'customers.php?delete=' + id;
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
