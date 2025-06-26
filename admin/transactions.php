<?php
$pageTitle = 'Giao dịch';
require_once 'includes/header.php';

// Lấy danh sách khách hàng để chọn
$customers = $pdo->query("SELECT id, name FROM customers")->fetchAll();

// Thêm giao dịch mới
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_transaction'])) {
    $customer_id = $_POST['customer_id'];
    $amount = $_POST['amount'];
    $type = $_POST['type'];
    $description = $_POST['description'];
    $stmt = $pdo->prepare("INSERT INTO transactions (customer_id, amount, type, description) VALUES (?, ?, ?, ?)");
    $stmt->execute([$customer_id, $amount, $type, $description]);
    echo '<div class="alert alert-success">Thêm giao dịch thành công!</div>';
}

// Sửa giao dịch
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_transaction'])) {
    $transaction_id = (int)$_POST['transaction_id'];
    $customer_id = $_POST['customer_id'];
    $amount = $_POST['amount'];
    $type = $_POST['type'];
    $description = $_POST['description'];
    $stmt = $pdo->prepare("UPDATE transactions SET customer_id = ?, amount = ?, type = ?, description = ? WHERE id = ?");
    if ($stmt->execute([$customer_id, $amount, $type, $description, $transaction_id])) {
        echo '<div class="alert alert-success">Cập nhật giao dịch thành công!</div>';
    } else {
        echo '<div class="alert alert-danger">Có lỗi khi cập nhật giao dịch!</div>';
    }
}

// Xóa giao dịch
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_transaction'])) {
    $transaction_id = (int)$_POST['transaction_id'];
    $stmt = $pdo->prepare("DELETE FROM transactions WHERE id = ?");
    if ($stmt->execute([$transaction_id])) {
        echo '<div class="alert alert-success">Xóa giao dịch thành công!</div>';
    } else {
        echo '<div class="alert alert-danger">Có lỗi khi xóa giao dịch!</div>';
    }
}

// Lấy danh sách giao dịch
$transactions = $pdo->query("SELECT t.*, c.name as customer_name FROM transactions t JOIN customers c ON t.customer_id = c.id ORDER BY t.id DESC")->fetchAll();
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Quản lý Giao dịch</h2>
            <div class="text-muted">Theo dõi và quản lý các giao dịch khách hàng</div>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTransactionModal">
            <i class="fas fa-plus"></i> Thêm giao dịch
        </button>
    </div>
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-exchange-alt me-2"></i>
                Danh sách giao dịch
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background: linear-gradient(90deg, #6a11cb 0%, #2575fc 100%); color: #fff;">
                        <tr>
                            <th>ID</th>
                            <th>Khách hàng</th>
                            <th>Số tiền</th>
                            <th>Loại</th>
                            <th>Mô tả</th>
                            <th>Ngày tạo</th>
                            <th width="100">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($transactions as $row): ?>
                        <tr>
                            <td class="fw-bold">#<?= $row['id'] ?></td>
                            <td><span class="badge bg-info"><i class="fas fa-user"></i> <?= htmlspecialchars($row['customer_name']) ?></span></td>
                            <td><span class="badge <?= $row['type']=='income' ? 'bg-success' : 'bg-danger' ?> fs-6"><?= number_format($row['amount'],0,',','.') ?> đ</span></td>
                            <td>
                                <span class="badge <?= $row['type']=='income' ? 'bg-primary' : 'bg-warning text-dark' ?>">
                                    <i class="fas <?= $row['type']=='income' ? 'fa-arrow-down' : 'fa-arrow-up' ?> me-1"></i> <?= $row['type']=='income' ? 'Thu' : 'Chi' ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($row['description']) ?></td>
                            <td><small class="text-muted"><?= $row['created_at'] ?></small></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-outline-primary border-2 btn-edit-transaction" 
                                        data-id="<?= $row['id'] ?>" 
                                        data-customer_id="<?= $row['customer_id'] ?>" 
                                        data-amount="<?= $row['amount'] ?>" 
                                        data-type="<?= $row['type'] ?>" 
                                        data-description="<?= htmlspecialchars($row['description']) ?>"
                                        title="Sửa">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger border-2 btn-delete-transaction" 
                                        data-id="<?= $row['id'] ?>" 
                                        data-name="<?= htmlspecialchars($row['customer_name']) ?>"
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
        </div>
    </div>
</div>

<!-- Modal Thêm giao dịch -->
<div class="modal fade" id="addTransactionModal" tabindex="-1" aria-labelledby="addTransactionModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" action="transactions.php">
      <input type="hidden" name="add_transaction" value="1">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addTransactionModalLabel">Thêm giao dịch</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="customer_id" class="form-label">Khách hàng *</label>
            <select name="customer_id" id="customer_id" class="form-select" required>
                <option value="">Chọn khách hàng</option>
                <?php foreach($customers as $cus): ?>
                    <option value="<?= $cus['id'] ?>"><?= htmlspecialchars($cus['name']) ?></option>
                <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="amount" class="form-label">Số tiền *</label>
            <input type="number" step="0.01" name="amount" id="amount" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="type" class="form-label">Loại</label>
            <select name="type" id="type" class="form-select">
                <option value="income">Thu</option>
                <option value="expense">Chi</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="description" class="form-label">Mô tả</label>
            <input type="text" name="description" id="description" class="form-control">
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

<!-- Modal Sửa giao dịch -->
<div class="modal fade" id="editTransactionModal" tabindex="-1" aria-labelledby="editTransactionModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" action="transactions.php">
      <input type="hidden" name="edit_transaction" value="1">
      <input type="hidden" name="transaction_id" id="edit_transaction_id">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editTransactionModalLabel">Sửa giao dịch</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="edit_customer_id" class="form-label">Khách hàng *</label>
            <select name="customer_id" id="edit_customer_id" class="form-select" required>
                <option value="">Chọn khách hàng</option>
                <?php foreach($customers as $cus): ?>
                    <option value="<?= $cus['id'] ?>"><?= htmlspecialchars($cus['name']) ?></option>
                <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="edit_amount" class="form-label">Số tiền *</label>
            <input type="number" step="0.01" name="amount" id="edit_amount" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="edit_type" class="form-label">Loại</label>
            <select name="type" id="edit_type" class="form-select">
                <option value="income">Thu</option>
                <option value="expense">Chi</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="edit_description" class="form-label">Mô tả</label>
            <input type="text" name="description" id="edit_description" class="form-control">
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

<!-- Modal Xóa giao dịch -->
<div class="modal fade" id="deleteTransactionModal" tabindex="-1" aria-labelledby="deleteTransactionModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" action="transactions.php">
      <input type="hidden" name="delete_transaction" value="1">
      <input type="hidden" name="transaction_id" id="delete_transaction_id">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteTransactionModalLabel">Xác nhận xóa giao dịch</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
        </div>
        <div class="modal-body">
          <p>Bạn có chắc chắn muốn xóa giao dịch của khách hàng <span id="delete_transaction_name" class="fw-bold text-danger"></span>?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
          <button type="submit" class="btn btn-danger">Xóa</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
// Xử lý nút Sửa
const editBtns = document.querySelectorAll('.btn-edit-transaction');
editBtns.forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('edit_transaction_id').value = this.dataset.id;
        document.getElementById('edit_customer_id').value = this.dataset.customer_id;
        document.getElementById('edit_amount').value = this.dataset.amount;
        document.getElementById('edit_type').value = this.dataset.type;
        document.getElementById('edit_description').value = this.dataset.description;
        var modal = new bootstrap.Modal(document.getElementById('editTransactionModal'));
        modal.show();
    });
});
// Xử lý nút Xóa
const deleteBtns = document.querySelectorAll('.btn-delete-transaction');
deleteBtns.forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('delete_transaction_id').value = this.dataset.id;
        document.getElementById('delete_transaction_name').textContent = this.dataset.name;
        var modal = new bootstrap.Modal(document.getElementById('deleteTransactionModal'));
        modal.show();
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php require_once 'includes/footer.php'; ?>