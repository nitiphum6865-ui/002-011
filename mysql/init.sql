SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- Create Database if not exists
DROP DATABASE IF EXISTS `kpt_architecture`;
CREATE DATABASE `kpt_architecture` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `kpt_architecture`;

-- Department Info Table
CREATE TABLE IF NOT EXISTS `department_info` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name_th` VARCHAR(255) NOT NULL,
  `name_en` VARCHAR(255) NOT NULL,
  `college_th` VARCHAR(255) NOT NULL,
  `college_en` VARCHAR(255) NOT NULL,
  `slogan` TEXT,
  `history` TEXT,
  `vision` TEXT,
  `mission` TEXT,
  `established_year` INT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `department_info` (`name_th`, `name_en`, `college_th`, `college_en`, `slogan`, `history`, `vision`, `mission`, `established_year`) VALUES
(
  'แผนกวิชาสถาปัตยกรรม',
  'Department of Architecture',
  'วิทยาลัยเทคนิคกาญจนาภิเษก',
  'Kanchanaphisek Technical College',
  'สร้างสรรค์พื้นที่ด้วยจินตนาการ ก่อร่างสร้างอนาคตด้วยสถาปัตยกรรม',
  'แผนกวิชาสถาปัตยกรรม มุ่งเน้นการจัดการเรียนการสอนเพื่อพัฒนาบุคลากรด้านการออกแบบสถาปัตยกรรม การเขียนแบบนวัตกรรมอาคาร และเทคโนโลยีก่อสร้างยุคใหม่',
  'เป็นผู้นำด้านการศึกษาและฝึกทักษะวิชาชีพสถาปัตยกรรมนวัตกรรม ตอบสนองความต้องการอุตสาหกรรมสร้างสรรค์และก่อสร้างอย่างยั่งยืน',
  '1. จัดการเรียนการสอนเน้นการปฏิบัติจริงในห้องปฏิบัติการและสตูดิโอออกแบบ\n2. ส่งเสริมทักษะเทคโนโลยีคอมพิวเตอร์ช่วยออกแบบ (BIM, CAD, 3D Rendering)\n3. ผลิตกำลังคนที่มีคุณธรรม จริยธรรม และจรรยาบรรณวิชาชีพ',
  2537
);

-- Courses Table
CREATE TABLE IF NOT EXISTS `courses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `level` VARCHAR(50) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `duration` VARCHAR(50) NOT NULL,
  `description` TEXT,
  `credits` INT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `courses` (`level`, `title`, `duration`, `description`, `credits`) VALUES
('ปวช.', 'ประกาศนียบัตรวิชาชีพ สาขาวิชาสถาปัตยกรรม', '3 ปี (หลักสูตรตามโครงสร้างสอศ.)', 'มุ่งเน้นพื้นฐานการเขียนแบบสถาปัตยกรรม การเขียนแบบก่อสร้าง การทำหุ่นจำลอง (Model) และเทคโนโลยีคอมพิวเตอร์เพื่อการออกแบบเบื้องต้น', 103),
('ปวส.', 'ประกาศนียบัตรวิชาชีพชั้นสูง สาขาวิชาเทคโนโลยีสถาปัตยกรรม', '2 ปี (หลักสูตรปรับปรุงใหม่)', 'เน้นการออกแบบสถาปัตยกรรมขั้นสูง การประยุกต์ใช้โปรแกรม Building Information Modeling (BIM) การบริหารงานก่อสร้าง และสถาปัตยกรรมเขียวเพื่อความยั่งยืน', 86);

-- Teachers Table
CREATE TABLE IF NOT EXISTS `teachers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `position` VARCHAR(255) NOT NULL,
  `degree` VARCHAR(255),
  `expertise` VARCHAR(255),
  `email` VARCHAR(255),
  `image` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `teachers` (`name`, `position`, `degree`, `expertise`, `email`, `image`) VALUES
('สถาปนิกวิทูร สุวรรณเดช', 'หัวหน้าแผนกวิชาสถาปัตยกรรม', 'สถ.ม. (สถาปัตยกรรมไทย), จุฬาลงกรณ์มหาวิทยาลัย', 'Tropical Architecture, Thai Vernacular Architecture', 'witoon@kpt.ac.th', 'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=400&auto=format&fit=crop&q=80'),
('อ.ณิชชา ธีรการต์', 'รองหัวหน้าแผนกวิชาสถาปัตยกรรม', 'สถ.บ. (สถาปัตยกรรมหลัก), ม.ศิลปากร', '3D Visualization, Parametric Design, BIM', 'nitcha@kpt.ac.th', 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=400&auto=format&fit=crop&q=80'),
('อ.ธนกฤต ชัยพัฒนา', 'ครูประจำแผนกวิชา', 'สถ.บ. (สถาปัตยกรรมภายใน), ม.เทคโนโลยีพระจอมเกล้าธนบุรี', 'Interior Architecture, Sustainable Construction', 'thanakrit@kpt.ac.th', 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=400&auto=format&fit=crop&q=80');

-- Portfolios Table
CREATE TABLE IF NOT EXISTS `portfolios` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `student_name` VARCHAR(255) NOT NULL,
  `level` VARCHAR(50) NOT NULL,
  `description` TEXT,
  `category` VARCHAR(100),
  `image_url` VARCHAR(500),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `portfolios` (`title`, `student_name`, `level`, `description`, `category`, `image_url`) VALUES
('Eco-Urban Community Center', 'นายพีรพัฒน์ วงศ์สวัสดิ์', 'ปวส.2', 'โครงการออกแบบศูนย์เรียนรู้ชุมชนด้วยวัสดุเป็นมิตรต่อสิ่งแวดล้อม และระบบระบายอากาศธรรมชาติ', 'การออกแบบสถาปัตยกรรมยั่งยืน', 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800&auto=format&fit=crop&q=80'),
('Modern Tropical Villa Pavilion', 'นางสาวอนันตยา เกิดผล', 'ปวส.1', 'การผสมผสานเส้นสายความร่วมสมัยกับบริบทเขตร้อนชื้น พร้อมการเรนเดอร์ภาพสามมิติด้วย Lumion 3D', '3D Visualization & Design', 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800&auto=format&fit=crop&q=80'),
('Heritage Revitalization Model', 'นายกิตติกร บุญมี', 'ปวช.3', 'แบบจำลองสถาปัตยกรรมย่านชุมชนเก่า อนุรักษ์คุณค่าประวัติศาสตร์โครงสร้างไม้เดิม', 'Physical Model Making', 'https://images.unsplash.com/photo-1513694203232-719a280e022f?w=800&auto=format&fit=crop&q=80');

-- News Table
CREATE TABLE IF NOT EXISTS `news` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT,
  `author` VARCHAR(100),
  `category` VARCHAR(100),
  `post_date` DATE,
  `image_url` VARCHAR(500),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `news` (`title`, `content`, `author`, `category`, `post_date`, `image_url`) VALUES
('นักศึกษาแผนกสถาปัตยกรรมคว้าเหรียญทอง การแข่งขันทักษะเขียนแบบสถาปัตยกรรมคอมพิวเตอร์', 'ขอแสดงความยินดีกับทีมตัวแทนนักศึกษาแผนกวิชาสถาปัตยกรรม KPT ที่ได้รางวัลชนะเลิศอันดับ 1 ในระดับภาค', 'ฝ่ายประชาสัมพันธ์', 'ผลงานความภาคภูมิใจ', '2026-07-28', 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=800&auto=format&fit=crop&q=80'),
('โครงการอบรมเชิงปฏิบัติการ BIM Architecture 2026', 'จัดอบรมการประยุกต์ใช้ Revit และ Archicad สำหรับการออกแบบโครงสร้างอาคารอัจฉริยะ ให้แก่นักศึกษาสาขาเทคโนโลยีสถาปัตยกรรม', 'หัวหน้างานวิชาการ', 'อบรมวิชาการ', '2026-08-01', 'https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=800&auto=format&fit=crop&q=80');

-- Contacts Table
CREATE TABLE IF NOT EXISTS `contacts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `subject` VARCHAR(255),
  `message` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `contacts` (`name`, `email`, `subject`, `message`) VALUES
('สมชาย ใจดี', 'somchai@email.com', 'สอบถามการสมัครเรียน ปวส. เทคโนโลยีสถาปัตยกรรม', 'สวัสดีครับ อยากสอบถามเกณฑ์การรับสมัครเทียบโอนสำหรับปีการศึกษาใหม่ครับ');

-- Users Table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `fullname` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `phone` VARCHAR(50),
  `bio` TEXT,
  `avatar` LONGTEXT,
  `role` VARCHAR(50) DEFAULT 'user',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`username`, `password`, `fullname`, `email`, `phone`, `bio`, `avatar`, `role`) VALUES
('admin', 'admin123', 'ผู้ดูแลระบบ สถาปัตย์ KPT', 'admin@kpt.ac.th', '081-234-5678', 'ผู้ดูแลระบบหลัก แผนกวิชาสถาปัตยกรรม', 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=400&auto=format&fit=crop&q=80', 'admin'),
('student1', '123456', 'นายพีรพัฒน์ วงศ์สวัสดิ์', 'peerapat@kpt.ac.th', '089-876-5432', 'นักศึกษา ปวส.2 สาขาวิชาเทคโนโลยีสถาปัตยกรรม', 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?w=400&auto=format&fit=crop&q=80', 'user');

