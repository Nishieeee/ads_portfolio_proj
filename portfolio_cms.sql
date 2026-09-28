CREATE DATABASE IF NOT EXISTS portfolio_cms;

USE portfolio_cms;

-- 1. Site Global Settings (theme, title, availability)
CREATE TABLE IF NOT EXISTS site_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value VARCHAR(255) NOT NULL
);

-- 2. Dynamic Page Sections (hybrid structure controller)
CREATE TABLE IF NOT EXISTS page_sections (
    id INT PRIMARY KEY AUTO_INCREMENT,
    section_key VARCHAR(50) NOT NULL UNIQUE, -- 'about', 'experience', 'projects', 'skills', 'education', 'contact'
    nav_label VARCHAR(50) NOT NULL,
    kicker VARCHAR(100),
    title VARCHAR(100) NOT NULL,
    subtitle TEXT,
    is_visible TINYINT(1) DEFAULT 1,
    order_index INT DEFAULT 0
);

-- 3. Basic / Profile Info
CREATE TABLE IF NOT EXISTS my_basic_info (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50),
    birth_date DATE NOT NULL,
    role_title VARCHAR(100) NOT NULL,
    tagline TEXT NOT NULL,
    avatar_url VARCHAR(255),
    resume_url VARCHAR(255)
);

-- 4. Contact Channels
CREATE TABLE IF NOT EXISTS my_contact_info (
    id INT PRIMARY KEY AUTO_INCREMENT,
    contact_name VARCHAR(50) NOT NULL,
    contact_type ENUM('email', 'phone_no', 'url') NOT NULL,
    contact_info VARCHAR(100) NOT NULL
);

-- 5. Experience
CREATE TABLE IF NOT EXISTS my_experience (
    id INT PRIMARY KEY AUTO_INCREMENT,
    job_title VARCHAR(100) NOT NULL,
    company_name VARCHAR(100) NOT NULL,
    location VARCHAR(100),
    description_1 VARCHAR(255) NOT NULL,
    description_2 VARCHAR(255) NOT NULL,
    description_3 VARCHAR(255) NOT NULL,
    date_start DATE NOT NULL,
    date_end DATE,
    date_display VARCHAR(50) NOT NULL
);

-- 6. Skills
CREATE TABLE IF NOT EXISTS my_skills (
    id INT PRIMARY KEY AUTO_INCREMENT,
    skill_category ENUM('technical', 'soft') NOT NULL,
    category_label VARCHAR(100) NOT NULL,
    skills_list TEXT NOT NULL -- comma-separated or normalized rows
);

-- 7. Education
CREATE TABLE IF NOT EXISTS my_education (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_name VARCHAR(150) NOT NULL,
    course VARCHAR(150) NOT NULL,
    date_start DATE NOT NULL,
    date_end DATE,
    date_display VARCHAR(50) NOT NULL,
    location VARCHAR(100),
    focus_areas TEXT
);

-- 8. Projects
CREATE TABLE IF NOT EXISTS my_projects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_name VARCHAR(100) NOT NULL,
    subtitle VARCHAR(150),
    description TEXT NOT NULL,
    technologies VARCHAR(255) NOT NULL,
    url VARCHAR(255),
    github_repo VARCHAR(255),
    date_start DATE NOT NULL,
    date_end DATE,
    is_featured TINYINT(1) DEFAULT 0,
    badge VARCHAR(50)
);

-- 9. Certificates & Honors
CREATE TABLE IF NOT EXISTS my_certificates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(150) NOT NULL,
    issuer VARCHAR(150) NOT NULL,
    date_display VARCHAR(50),
    description TEXT,
    cert_id VARCHAR(100),
    cert_url VARCHAR(255)
);

-- 10. Contact Inquiries (incoming messages from contact form)
CREATE TABLE IF NOT EXISTS contact_inquiries (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sender_name VARCHAR(100) NOT NULL,
    sender_email VARCHAR(100) NOT NULL,
    subject VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 11. Admin Authentication Users
CREATE TABLE IF NOT EXISTS admin_users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    auth_token VARCHAR(255) DEFAULT NULL,
    token_expires_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Initial Seed Data for Admin Account (Default username: admin, password: adminpassword123)
INSERT INTO admin_users (username, password_hash) VALUES
('admin', '$2y$12$901IyQtCIPVmvZ8xdnUTFeeY3Rmwf8HDieVajOLcsvnOFnex75OAe')
ON DUPLICATE KEY UPDATE id = id;

-- Initial Seed Data for Site Settings
INSERT INTO site_settings (setting_key, setting_value) VALUES
('theme', 'monochrome'),
('site_title', 'Jhon Clein Pagarogan — Full-Stack Developer'),
('availability_badge', 'Available for Select Projects & Full-Time Roles')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- Initial Seed Data for Dynamic Page Sections
INSERT INTO page_sections (section_key, nav_label, kicker, title, subtitle, is_visible, order_index) VALUES
('about', 'About', 'Get to Know Me', 'About Me', '', 1, 1),
('experience', 'Experience', 'Career Path', 'Work Experience', '', 1, 2),
('projects', 'Projects', 'Featured Engineering', 'Key Projects', 'Real-world applications demonstrating high concurrency, robust architecture, and modern interfaces.', 1, 3),
('skills', 'Skills', 'Technical Stack', 'Skills & Technologies', 'Structured by domains, from backend systems and database engines to client interfaces.', 1, 4),
('education', 'Education', 'Academic & Achievements', 'Education & Certifications', '', 1, 5),
('contact', 'Contact', 'Initiate Collaboration', 'Get In Touch', 'Have a project in mind, interested in high-concurrency systems, or exploring full-time opportunities? Feel free to reach out.', 1, 6)
ON DUPLICATE KEY UPDATE order_index = VALUES(order_index);