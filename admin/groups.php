<?php
$pageTitle = 'Nhóm khách hàng';
require_once 'includes/header.php';

$error = '';
$success = '';

// Xử lý thêm nhóm mới
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_group'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    if (empty($name)) {
        $error = 'Vui lòng nhập tên nhóm!';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM customer_groups WHERE name = ?");
        $stmt->execute([$name]);
        if ($stmt->fetch()) {
            $error = 'Tên nhóm này đã tồn tại!';
        } else {
            $stmt = $pdo->prepare("INSERT INTO customer_groups (name, description) VALUES (?, ?)");
            if ($stmt->execute([$name, $description])) {
                $success = 'Thêm nhóm khách hàng thành công!';
            } else {
                $error = 'Có lỗi xảy ra, vui lòng thử lại!';
            }
        }
    }
}

// Xử lý sửa nhóm
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_group'])) {
    $id = (int)$_POST['group_id'];
    $name = trim($_POST['edit_name']);
    $description = trim($_POST['edit_description']);
    if (empty($name)) {
        $error = 'Vui lòng nhập tên nhóm!';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM customer_groups WHERE name = ? AND id != ?");
        $stmt->execute([$name, $id]);
        if ($stmt->fetch()) {
            $error = 'Tên nhóm này đã tồn tại!';
        } else {
            $stmt = $pdo->prepare("UPDATE customer_groups SET name = ?, description = ? WHERE id = ?");
            if ($stmt->execute([$name, $description, $id])) {
                $success = 'Cập nhật nhóm khách hàng thành công!';
            } else {
                $error = 'Có lỗi xảy ra, vui lòng thử lại!';
            }
        }
    }
}

// Xử lý xóa nhóm
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $groupId = (int)$_GET['delete'];
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM customers WHERE group_id = ?");
    $stmt->execute([$groupId]);
    $customerCount = $stmt->fetch()['count'];
    if ($customerCount > 0) {
        $error = "Không thể xóa nhóm này vì có $customerCount khách hàng đang sử dụng!";
    } else {
        $stmt = $pdo->prepare("DELETE FROM customer_groups WHERE id = ?");
        if ($stmt->execute([$groupId])) {
            $success = 'Xóa nhóm khách hàng thành công!';
        } else {
            $error = 'Có lỗi xảy ra khi xóa nhóm!';
        }
    }
}

// Lấy danh sách nhóm với số lượng khách hàng từ database
$stmt = $pdo->query("
    SELECT g.*, COUNT(c.id) as customer_count 
    FROM customer_groups g 
    LEFT JOIN customers c ON g.id = c.group_id 
    GROUP BY g.id 
    ORDER BY g.name
");
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
        <h2 class="fw-bold mb-0">Quản lý Nhóm khách hàng</h2>
        <p class="text-muted">Phân loại và quản lý nhóm khách hàng</p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addGroupModal">
        <i class="fas fa-plus me-2"></i>
        Thêm nhóm mới
    </button>
</div>

<!-- Modal Thêm nhóm -->
<div class="modal fade" id="addGroupModal" tabindex="-1" aria-labelledby="addGroupModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" action="groups.php">
      <input type="hidden" name="add_group" value="1">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addGroupModalLabel">Thêm nhóm khách hàng</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="groupName" class="form-label">Tên nhóm *</label>
            <input type="text" class="form-control" id="groupName" name="name" required>
          </div>
          <div class="mb-3">
            <label for="groupDesc" class="form-label">Mô tả</label>
            <textarea class="form-control" id="groupDesc" name="description" rows="2"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
          <button type="submit" class="btn btn-primary">Lưu</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal Sửa nhóm -->
<div class="modal fade" id="editGroupModal" tabindex="-1" aria-labelledby="editGroupModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" action="groups.php">
      <input type="hidden" name="edit_group" value="1">
      <input type="hidden" name="group_id" id="editGroupId">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editGroupModalLabel">Sửa nhóm khách hàng</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="editGroupName" class="form-label">Tên nhóm *</label>
            <input type="text" class="form-control" id="editGroupName" name="edit_name" required>
          </div>
          <div class="mb-3">
            <label for="editGroupDesc" class="form-label">Mô tả</label>
            <textarea class="form-control" id="editGroupDesc" name="edit_description" rows="2"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
          <button type="submit" class="btn btn-primary">Lưu</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal xác nhận xóa nhóm -->
<div class="modal fade" id="deleteGroupModal" tabindex="-1" aria-labelledby="deleteGroupModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteGroupModalLabel">Xác nhận xóa nhóm</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Bạn có chắc chắn muốn xóa nhóm <span id="deleteGroupName" class="fw-bold text-danger"></span>?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
        <a id="deleteGroupBtn" href="#" class="btn btn-danger">Xóa</a>
      </div>
    </div>
  </div>
</div>

<!-- Groups Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-layer-group me-2"></i>
            Danh sách nhóm khách hàng
        </h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($groups)): ?>
            <div class="text-center py-5">
                <i class="fas fa-layer-group fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Chưa có nhóm khách hàng nào</h5>
                <p class="text-muted">Hãy tạo nhóm đầu tiên</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên nhóm</th>
                            <th>Mô tả</th>
                            <th>Số khách hàng</th>
                            <th>Ngày tạo</th>
                            <th width="120">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($groups as $group): ?>
                        <tr>
                            <td class="fw-bold">#<?= $group['id'] ?></td>
                            <td>
                                <span class="badge bg-<?= $group['name'] == 'VIP' ? 'warning' : 'secondary' ?> fs-6">
                                    <i class="fas fa-<?= $group['name'] == 'VIP' ? 'crown' : 'users' ?> me-1"></i>
                                    <?= htmlspecialchars($group['name']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($group['description']) ?></td>
                            <td>
                                <span class="badge bg-info">
                                    <?= number_format($group['customer_count']) ?> khách hàng
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?= formatDate($group['created_at']) ?>
                                </small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary" title="Sửa" onclick="showEditGroupModal(<?= $group['id'] ?>, '<?= htmlspecialchars(addslashes($group['name'])) ?>', '<?= htmlspecialchars(addslashes($group['description'])) ?>')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" onclick="showDeleteGroupModal(<?= $group['id'] ?>, '<?= htmlspecialchars(addslashes($group['name'])) ?>')" title="Xóa">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function showDeleteGroupModal(id, name) {
    document.getElementById('deleteGroupName').textContent = name;
    document.getElementById('deleteGroupBtn').href = 'groups.php?delete=' + id;
    var modal = new bootstrap.Modal(document.getElementById('deleteGroupModal'));
    modal.show();
}
function showEditGroupModal(id, name, desc) {
    document.getElementById('editGroupId').value = id;
    document.getElementById('editGroupName').value = name;
    document.getElementById('editGroupDesc').value = desc;
    var modal = new bootstrap.Modal(document.getElementById('editGroupModal'));
    modal.show();
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php require_once 'includes/footer.php'; ?>
