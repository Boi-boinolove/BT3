<?php
$pageTitle = 'Lịch hẹn';
require_once 'includes/header.php';


// Lấy danh sách khách hàng để chọn
$customers = $pdo->query("SELECT id, name FROM customers")->fetchAll();

// Thêm lịch hẹn mới
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_appointment'])) {
    $customer_id = $_POST['customer_id'];
    $appointment_time = $_POST['appointment_time'];
    $note = $_POST['note'];
    $status = $_POST['status'];
    $stmt = $pdo->prepare("INSERT INTO appointments (customer_id, appointment_time, note, status) VALUES (?, ?, ?, ?)");
    $stmt->execute([$customer_id, $appointment_time, $note, $status]);
    echo '<div class="alert alert-success">Thêm lịch hẹn thành công!</div>';
}

// Lấy danh sách lịch hẹn
$appointments = $pdo->query("SELECT a.*, c.name as customer_name FROM appointments a JOIN customers c ON a.customer_id = c.id ORDER BY a.id DESC")->fetchAll();

// Sửa lịch hẹn
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_appointment'])) {
    $appointment_id = (int)$_POST['appointment_id'];
    $customer_id = $_POST['customer_id'];
    $appointment_time = $_POST['appointment_time'];
    $note = $_POST['note'];
    $status = $_POST['status'];
    $stmt = $pdo->prepare("UPDATE appointments SET customer_id = ?, appointment_time = ?, note = ?, status = ? WHERE id = ?");
    if ($stmt->execute([$customer_id, $appointment_time, $note, $status, $appointment_id])) {
        echo '<div class="alert alert-success">Cập nhật lịch hẹn thành công!</div>';
    } else {
        echo '<div class="alert alert-danger">Có lỗi khi cập nhật lịch hẹn!</div>';
    }
}

// Xóa lịch hẹn
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_appointment'])) {
    $appointment_id = (int)$_POST['appointment_id'];
    $stmt = $pdo->prepare("DELETE FROM appointments WHERE id = ?");
    if ($stmt->execute([$appointment_id])) {
        echo '<div class="alert alert-success">Xóa lịch hẹn thành công!</div>';
    } else {
        echo '<div class="alert alert-danger">Có lỗi khi xóa lịch hẹn!</div>';
    }
}
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Quản lý Lịch hẹn</h2>
            <div class="text-muted">Theo dõi và quản lý lịch hẹn khách hàng</div>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAppointmentModal"><i class="fas fa-plus"></i> Thêm lịch hẹn</button>
    </div>
    <!-- Modal Thêm lịch hẹn -->
    <div class="modal fade" id="addAppointmentModal" tabindex="-1" aria-labelledby="addAppointmentModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <form method="post" action="appointments.php">
          <input type="hidden" name="add_appointment" value="1">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="addAppointmentModalLabel">Thêm lịch hẹn</h5>
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
                <label for="appointment_time" class="form-label">Thời gian *</label>
                <input type="datetime-local" name="appointment_time" id="appointment_time" class="form-control" required>
              </div>
              <div class="mb-3">
                <label for="note" class="form-label">Ghi chú</label>
                <input type="text" name="note" id="note" class="form-control">
              </div>
              <div class="mb-3">
                <label for="status" class="form-label">Trạng thái</label>
                <select name="status" id="status" class="form-select">
                    <option value="pending">Chờ xác nhận</option>
                    <option value="confirmed">Đã xác nhận</option>
                    <option value="cancelled">Đã hủy</option>
                </select>
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
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-calendar-check me-2"></i>
                Danh sách lịch hẹn
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background: linear-gradient(90deg, #6a11cb 0%, #2575fc 100%); color: #fff;">
                        <tr>
                            <th>ID</th>
                            <th>Khách hàng</th>
                            <th>Thời gian</th>
                            <th>Ghi chú</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                            <th width="100">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($appointments as $row): ?>
                        <tr>
                            <td class="fw-bold">#<?= $row['id'] ?></td>
                            <td><span class="badge bg-info"><i class="fas fa-user"></i> <?= htmlspecialchars($row['customer_name']) ?></span></td>
                            <td><?= date('Y-m-d\TH:i', strtotime($row['appointment_time'])) ?></td>
                            <td><?= htmlspecialchars($row['note']) ?></td>
                            <td>
                                <span class="badge 
                                    <?php if($row['status']=='confirmed') echo 'bg-success';
                                          elseif($row['status']=='pending') echo 'bg-warning text-dark';
                                          else echo 'bg-secondary'; ?>">
                                    <?= $row['status']=='confirmed' ? 'Đã xác nhận' : ($row['status']=='pending' ? 'Chờ xác nhận' : 'Đã hủy') ?>
                                </span>
                            </td>
                            <td><small class="text-muted"><?= $row['created_at'] ?></small></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-outline-primary border-2 btn-edit-appointment"
                                        data-id="<?= $row['id'] ?>"
                                        data-customer_id="<?= $row['customer_id'] ?>"
                                        data-appointment_time="<?= date('Y-m-d\TH:i', strtotime($row['appointment_time'])) ?>"
                                        data-note="<?= htmlspecialchars($row['note']) ?>"
                                        data-status="<?= $row['status'] ?>"
                                        title="Sửa">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger border-2 btn-delete-appointment"
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
<!-- Modal Sửa lịch hẹn -->
<div class="modal fade" id="editAppointmentModal" tabindex="-1" aria-labelledby="editAppointmentModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" action="appointments.php">
      <input type="hidden" name="edit_appointment" value="1">
      <input type="hidden" name="appointment_id" id="edit_appointment_id">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editAppointmentModalLabel">Sửa lịch hẹn</h5>
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
            <label for="edit_appointment_time" class="form-label">Thời gian *</label>
            <input type="datetime-local" name="appointment_time" id="edit_appointment_time" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="edit_note" class="form-label">Ghi chú</label>
            <input type="text" name="note" id="edit_note" class="form-control">
          </div>
          <div class="mb-3">
            <label for="edit_status" class="form-label">Trạng thái</label>
            <select name="status" id="edit_status" class="form-select">
                <option value="pending">Chờ xác nhận</option>
                <option value="confirmed">Đã xác nhận</option>
                <option value="cancelled">Đã hủy</option>
            </select>
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
<!-- Modal Xóa lịch hẹn -->
<div class="modal fade" id="deleteAppointmentModal" tabindex="-1" aria-labelledby="deleteAppointmentModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" action="appointments.php">
      <input type="hidden" name="delete_appointment" value="1">
      <input type="hidden" name="appointment_id" id="delete_appointment_id">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteAppointmentModalLabel">Xác nhận xóa lịch hẹn</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
        </div>
        <div class="modal-body">
          <p>Bạn có chắc chắn muốn xóa lịch hẹn của khách hàng <span id="delete_appointment_name" class="fw-bold text-danger"></span>?</p>
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
setTimeout(function() {
  const editBtns = document.querySelectorAll('.btn-edit-appointment');
  editBtns.forEach(btn => {
      btn.addEventListener('click', function() {
          document.getElementById('edit_appointment_id').value = this.dataset.id;
          document.getElementById('edit_customer_id').value = this.dataset.customer_id;
          document.getElementById('edit_appointment_time').value = this.dataset.appointment_time;
          document.getElementById('edit_note').value = this.dataset.note;
          document.getElementById('edit_status').value = this.dataset.status;
          var modal = new bootstrap.Modal(document.getElementById('editAppointmentModal'));
          modal.show();
      });
  });
  // Xử lý nút Xóa
  const deleteBtns = document.querySelectorAll('.btn-delete-appointment');
  deleteBtns.forEach(btn => {
      btn.addEventListener('click', function() {
          document.getElementById('delete_appointment_id').value = this.dataset.id;
          document.getElementById('delete_appointment_name').textContent = this.dataset.name;
          var modal = new bootstrap.Modal(document.getElementById('deleteAppointmentModal'));
          modal.show();
      });
  });
}, 300);
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php require_once 'includes/footer.php'; ?> 