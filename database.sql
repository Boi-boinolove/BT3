-- Tạo database
CREATE DATABASE IF NOT EXISTS customer_management;
USE customer_management;

-- Bảng nhóm khách hàng
CREATE TABLE customer_groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng khách hàng
CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    address TEXT,
    password VARCHAR(255) NOT NULL,
    group_id INT,
    avatar VARCHAR(255) DEFAULT 'default-avatar.png',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES customer_groups(id) ON DELETE SET NULL
);

-- Bảng giao dịch
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    type ENUM('income', 'expense') DEFAULT 'income',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

-- Bảng đánh giá (KHÔNG DÙNG NỮA, đã thay bằng bảng lịch hẹn)
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    rating INT DEFAULT 0 CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    status ENUM('pending', 'approved', 'hidden') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

-- Bảng admin
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    role ENUM('admin', 'staff') DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng lịch hẹn
CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    appointment_time DATETIME NOT NULL,
    note TEXT,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

-- Dữ liệu mẫu
INSERT INTO customer_groups (name, description) VALUES
('VIP', 'Khách hàng VIP với ưu đãi đặc biệt'),
('Thường', 'Khách hàng thông thường'),
('Tiềm năng', 'Khách hàng tiềm năng cần chăm sóc');

INSERT INTO admins (username, password, email, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@example.com', 'admin');
-- Password: password

INSERT INTO customers (name, email, phone, address, password, group_id) VALUES
('Nguyễn Văn An', 'nguyenvanan@email.com', '0901234567', '123 Đường ABC, Quận 1, TP.HCM', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
('Trần Thị Bình', 'tranthibinh@email.com', '0912345678', '456 Đường DEF, Quận 2, TP.HCM', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2),
('Lê Văn Cường', 'levancuong@email.com', '0923456789', '789 Đường GHI, Quận 3, TP.HCM', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

INSERT INTO transactions (customer_id, amount, type, description) VALUES
(1, 1500000, 'income', 'Mua sản phẩm A'),
(1, 2000000, 'income', 'Mua sản phẩm B'),
(2, 500000, 'income', 'Mua sản phẩm C'),
(3, 3000000, 'income', 'Mua combo sản phẩm');

INSERT INTO reviews (customer_id, rating, comment, status) VALUES
(1, 5, 'Dịch vụ rất tốt, nhân viên nhiệt tình!', 'approved'),
(2, 4, 'Sản phẩm chất lượng, giao hàng nhanh.', 'approved'),
(3, 5, 'Rất hài lòng với dịch vụ của công ty.', 'pending');
