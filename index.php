<?php
require_once 'includes/functions.php';

// Lấy danh sách khách hàng VIP để hiển thị
$stmt = $pdo->prepare("
    SELECT c.*, g.name as group_name 
    FROM customers c 
    LEFT JOIN customer_groups g ON c.group_id = g.id 
    WHERE c.status = 'active' AND g.name = 'VIP'
    LIMIT 6
");
$stmt->execute();
$vipCustomers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống Quản lý Khách hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navbar hiện đại -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold text-primary" href="index.php">
                <i class="fas fa-users"></i> CRM System
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center gap-2">
                    <li class="nav-item"><a class="nav-link" href="#features">Tính năng</a></li>
                    <li class="nav-item"><a class="nav-link" href="#vip">Khách hàng VIP</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin/login.php">Quản trị</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section kiểu Assemble -->
    <section class="hero-section py-5" style="background: linear-gradient(120deg, #3578e5 0%, #38c6f4 100%); min-height: 420px; color: #fff;">
        <div class="container">
            <div class="row justify-content-start align-items-center" style="min-height:340px;">
                <div class="col-lg-7 col-md-10 col-12 text-lg-start text-center">
                    <h1 class="display-5 mb-3" style="font-family:'Montserrat',sans-serif;font-weight:700;letter-spacing:0.5px;color:#fff;">Quản lý khách hàng<br>hiệu quả với CRM System</h1>
                    <p class="mb-4" style="color:rgba(255,255,255,0.92);font-family:'Montserrat',sans-serif;font-weight:400;font-size:1.1rem;">Giải pháp quản lý khách hàng toàn diện giúp doanh nghiệp của bạn tăng trưởng nhanh chóng và bền vững</p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="#features" class="btn btn-light btn-lg rounded-pill px-4 fw-bold shadow-sm">Khám phá ngay</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5" id="features">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-12">
                    <h2 class="fw-bold">Tính năng nổi bật</h2>
                    <p class="text-muted">Những tính năng giúp bạn quản lý khách hàng hiệu quả</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="feature-icon bg-primary text-white rounded-circle mx-auto mb-3" style="width:60px;height:60px;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                            <h5 class="card-title">Quản lý Khách hàng</h5>
                            <p class="card-text">Lưu trữ và quản lý thông tin khách hàng một cách có hệ thống</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="feature-icon bg-success text-white rounded-circle mx-auto mb-3" style="width:60px;height:60px;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-chart-line fa-2x"></i>
                            </div>
                            <h5 class="card-title">Thống kê Báo cáo</h5>
                            <p class="card-text">Theo dõi hiệu suất kinh doanh với các báo cáo chi tiết</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="feature-icon bg-warning text-white rounded-circle mx-auto mb-3" style="width:60px;height:60px;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-star fa-2x"></i>
                            </div>
                            <h5 class="card-title">Lịch hẹn Khách hàng</h5>
                            <p class="card-text">Thu thập và quản lý phản hồi từ khách hàng</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- VIP Customers Section -->
    <?php if (!empty($vipCustomers)): ?>
    <section class="py-5 bg-light" id="vip">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-12">
                    <h2 class="fw-bold">Khách hàng VIP</h2>
                    <p class="text-muted">Những khách hàng đặc biệt của chúng tôi</p>
                </div>
            </div>
            <div class="row g-4">
                <?php foreach ($vipCustomers as $customer): ?>
                <div class="col-md-4">
                    <div class="card text-center border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($customer['name']) ?></h5>
                            <p class="card-text text-muted"><?= htmlspecialchars($customer['email']) ?></p>
                            <span class="badge bg-warning text-dark">
                                <i class="fas fa-crown me-1"></i>
                                <?= htmlspecialchars($customer['group_name']) ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Section Đánh giá khách hàng -->
    <section class="py-5">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-12">
                    <h2 class="fw-bold">Khách hàng nói gì về chúng tôi</h2>
                    <p class="text-muted">Những đánh giá từ khách hàng đã sử dụng CRM System</p>
                </div>
            </div>
            <div class="row g-4 justify-content-center">
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm p-4">
                        <div class="mb-3"><i class="fas fa-quote-left fa-2x text-primary"></i></div>
                        <div class="mb-3 text-muted">"CRM System đã giúp chúng tôi quản lý khách hàng hiệu quả hơn rất nhiều. Giao diện dễ sử dụng và các tính năng đáp ứng đầy đủ nhu cầu của doanh nghiệp."</div>
                        <div class="d-flex align-items-center gap-3">
                    
                            <div>
                                <div class="fw-bold">Nguyễn Văn Anh</div>
                                <div class="text-muted small">Quản đốc sản xuất, Công ty XYZ</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm p-4">
                        <div class="mb-3"><i class="fas fa-quote-left fa-2x text-primary"></i></div>
                        <div class="mb-3 text-muted">"Chức năng phân nhóm khách hàng và báo cáo thống kê giúp chúng tôi hiểu khách hàng hơn và chăm sóc tốt hơn. Rất ấn tượng!"</div>
                        <div class="d-flex align-items-center gap-3">
                    
                            <div>
                                <div class="fw-bold">Trịnh Thị Bình</div>
                                <div class="text-muted small">Giám đốc marketing, Công ty ABC</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm p-4">
                        <div class="mb-3"><i class="fas fa-quote-left fa-2x text-primary"></i></div>
                        <div class="mb-3 text-muted">"Tính năng xuất/nhập dữ liệu từ file Excel rất tiện lợi, giúp chúng tôi đồng bộ dữ liệu dễ dàng và tiết kiệm thời gian. CRM System mà không mất nhiều thời gian!"</div>
                        <div class="d-flex align-items-center gap-3">
                    
                            <div>
                                <div class="fw-bold">Lê Văn Cang</div>
                                <div class="text-muted small">Trưởng phòng IT, Công ty DEF</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer đẹp -->
    <footer class="bg-dark text-white pt-5 pb-3 mt-5">
        <div class="container">
            <div class="row gy-4">
                <div class="col-md-3">
                    <div class="mb-2 fw-bold fs-5"><i class="fas fa-users me-2"></i>CRM System</div>
                    <div class="mb-3 small">Giải pháp quản lý khách hàng toàn diện giúp doanh nghiệp tăng trưởng nhanh chóng và bền vững.</div>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-white-50 fs-5"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white-50 fs-5"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white-50 fs-5"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="text-white-50 fs-5"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="fw-bold mb-2">Liên kết</div>
                    <ul class="list-unstyled small">
                        <li><a href="#features" class="text-white-50 text-decoration-none">Tính năng</a></li>
                        <li><a href="#vip" class="text-white-50 text-decoration-none">Khách hàng VIP</a></li>
                        <li><a href="#" class="text-white-50 text-decoration-none">Đánh giá</a></li>
                        <li><a href="admin/login.php" class="text-white-50 text-decoration-none">Dashboard</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <div class="fw-bold mb-2">Hỗ trợ</div>
                    <ul class="list-unstyled small">
                        <li><a href="#" class="text-white-50 text-decoration-none">Trung tâm hỗ trợ</a></li>
                        <li><a href="#" class="text-white-50 text-decoration-none">Hướng dẫn sử dụng</a></li>
                        <li><a href="#" class="text-white-50 text-decoration-none">Câu hỏi thường gặp</a></li>
                        <li><a href="#" class="text-white-50 text-decoration-none">Chính sách bảo mật</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <div class="fw-bold mb-2">Liên hệ</div>
                    <ul class="list-unstyled small">
                        <li><i class="fas fa-map-marker-alt me-2"></i>123 Đường ABC, Quận XYZ, Hà Nội</li>
                        <li><i class="fas fa-phone me-2"></i>+84 888 999 123</li>
                        <li><i class="fas fa-envelope me-2"></i>crm@example.com</li>
                    </ul>
                </div>
            </div>
            <div class="text-center mt-4 small text-white-50">&copy; 2024 CRM System. All rights reserved.</div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
