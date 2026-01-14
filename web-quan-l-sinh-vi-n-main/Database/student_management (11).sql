-- 1. Khởi tạo Cơ sở dữ liệu
CREATE DATABASE IF NOT EXISTS `student_management` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `student_management`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- 2. Bảng `users`: Tài khoản đăng nhập
-- --------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) DEFAULT '123456',
  `role` enum('admin','teacher','student') DEFAULT 'student',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(1, 'admin@gmail.com', '123456', 'admin'),
(2, 'hoangcute@gmail.com', '123456', 'student'),
(3, 'admin', '123456', 'admin'),
(4, 'cak@gmail.com', '123456', 'admin'),
(5, 'studocu@123.com', '123456', 'student'),
(6, 'hnue1324@hnue.com', '123456', 'student'),
(7, 'hnue11@hnue.com', '123456', 'student'),
(8, 'hnue132@hnue.com', '123456', 'student');

-- --------------------------------------------------------
-- 3. Bảng `classes`: Danh sách lớp hành chính
-- --------------------------------------------------------
DROP TABLE IF EXISTS `classes`;
CREATE TABLE `classes` (
  `class_id` int NOT NULL AUTO_INCREMENT,
  `class_name` varchar(100) NOT NULL,
  `teacher_name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`class_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `classes` (`class_id`, `class_name`, `teacher_name`) VALUES
(1, 'Khoa CNTT - E1', 'Thầy Nguyễn Văn A'),
(3, 'Khoa CNTT - E2', 'Vũ Thái Hiền'),
(4, 'Khoa CNTT - E3', 'Giảng');

-- --------------------------------------------------------
-- 4. Bảng `student_info`: Thông tin chi tiết sinh viên
-- --------------------------------------------------------
DROP TABLE IF EXISTS `student_info`;
CREATE TABLE `student_info` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `class_id` int DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `gender` enum('Nam','Nữ','Khác') DEFAULT 'Nam',
  `phone` varchar(20) DEFAULT NULL,
  `address` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `fk_stu_user` (`user_id`),
  KEY `fk_stu_class` (`class_id`),
  CONSTRAINT `fk_stu_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_stu_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `student_info` (`id`, `user_id`, `class_id`, `name`, `email`, `gender`, `phone`, `address`) VALUES
(1, 2, 1, 'dang_ky_tk', 'hoangcute@gmail.com', 'Nam', '123456', 'jkkj'),
(2, 4, 1, 'nguyen', 'cak@gmail.com', 'Nam', '123123', '123123'),
(3, 5, NULL, 'Nguyễn Bá Lương', 'studocu@123.com', 'Nam', '000000', 'Vũ Tiên, Hải Dương'),
(4, 6, 1, 'dang_ky_tk', 'hnue1324@hnue.com', 'Nam', '1234556', 'Tân Lập'),
(5, 7, 1, 'nguyen', 'hnue11@hnue.com', 'Nam', '123456', 'Hà Tĩnh'),
(6, 8, 1, 'c2010l', 'hnue132@hnue.com', 'Nam', '1111', 'BNB BN');

-- --------------------------------------------------------
-- 5. Bảng `courses`: Danh mục học phần
-- --------------------------------------------------------
DROP TABLE IF EXISTS `courses`;
CREATE TABLE `courses` (
  `course_id` int NOT NULL AUTO_INCREMENT,
  `class_id` int NOT NULL,
  `course_name` varchar(255) NOT NULL,
  PRIMARY KEY (`course_id`),
  KEY `fk_course_class` (`class_id`),
  CONSTRAINT `fk_course_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `courses` (`course_id`, `class_id`, `course_name`) VALUES
(1, 1, 'Lập trình PHP'),
(3, 3, 'Công nghệ phần mềm'),
(4, 4, 'Khoa học dữ liệu');

-- --------------------------------------------------------
-- 6. Bảng `course_registrations`: Đăng ký học phần
-- --------------------------------------------------------
DROP TABLE IF EXISTS `course_registrations`;
CREATE TABLE `course_registrations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `course_id` int NOT NULL,
  `registered_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_registration` (`student_id`,`course_id`),
  KEY `fk_reg_course` (`course_id`),
  CONSTRAINT `fk_reg_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reg_student` FOREIGN KEY (`student_id`) REFERENCES `student_info` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `course_registrations` (`id`, `student_id`, `course_id`, `registered_at`) VALUES
(2, 1, 1, '2026-01-14 07:15:16'),
(3, 2, 1, '2026-01-14 07:55:32'),
(5, 3, 4, '2026-01-14 11:54:05'),
(6, 5, 4, '2026-01-14 12:20:44');

-- --------------------------------------------------------
-- 7. Bảng `grades`: Kết quả học tập
-- --------------------------------------------------------
DROP TABLE IF EXISTS `grades`;
CREATE TABLE `grades` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `course_id` int NOT NULL,
  `score` decimal(4,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_grade` (`student_id`,`course_id`),
  KEY `fk_grade_course` (`course_id`),
  CONSTRAINT `fk_grade_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_grade_student` FOREIGN KEY (`student_id`) REFERENCES `student_info` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `grades` (`id`, `student_id`, `course_id`, `score`) VALUES
(2, 1, 1, '9.00'),
(4, 2, 1, NULL),
(6, 3, 4, '6.00'),
(8, 5, 4, NULL);

-- --------------------------------------------------------
-- 8. Bảng `attendance`: Điểm danh
-- --------------------------------------------------------
DROP TABLE IF EXISTS `attendance`;
CREATE TABLE `attendance` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `date` date NOT NULL,
  `status` enum('present','absent') DEFAULT 'present',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_attendance` (`student_id`,`date`),
  CONSTRAINT `fk_att_student` FOREIGN KEY (`student_id`) REFERENCES `student_info` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `attendance` (`id`, `student_id`, `date`, `status`) VALUES
(1, 1, '2026-01-14', 'present'),
(2, 3, '2026-01-14', 'present');

COMMIT;