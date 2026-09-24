CREATE DATABASE portfolio_cms;

USE portfolio_cms;

CREATE TABLE IF NOT EXISTS my_basic_info (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50),
    birth_date DATE NOT NULL
);

CREATE TABLE IF NOT EXISTS my_contact_info (
    id INT PRIMARY KEY AUTO_INCREMENT,
    contact_name VARCHAR(50) NOT NULL,
    contact_type ENUM('email', 'phone_no', 'url') NOT NULL,
    contact_info VARCHAR(100) NOT NULL -- handles: url links, phone no., email -- 
);

CREATE TABLE IF NOT EXISTS my_experience (
    id INT PRIMARY KEY AUTO_INCREMENT,
    job_title VARCHAR(50) NOT NULL,
    company_name VARCHAR(50) NOT NULL
    description_1 VARCHAR(250) NOT NULL,
    description_2 VARCHAR(250) NOT NULL,
    description_3 VARCHAR(250) NOT NULL,
    date_start DATE NOT NULL,
    date_end DATE,
);

CREATE TABLE IF NOT EXISTS my_skills (
    id INT PRIMARY KEY AUTO_INCREMENT,
    experience_id INT,
    skill_name VARCHAR(50) NOT NULL,
    skill_category ENUM('technical', 'soft') NOT NULL;
);

CREATE TABLE IF NOT EXISTS my_education (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_name VARCHAR(100) NOT NULL,
    course VARCHAR(50),
    date_start DATE NOT NULL,
    date_end DATE,
);

CREATE TABLE IF NOT EXISTS my_projects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_name VARCHAR(50) NOT NULL,
    description VARCHAR(255) NOT NULL, 
    technologies VARCHAR(255) NOT NULL,
    url VARCHAR(255) NOT NULL,
    github_repo VARCHAR(255) NOT NULL,
    date_start DATE NOT NULL,
    date_end DATE NOT NULL,
);

CREATE TABLE IF NOT EXISTS my_certificates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(50) NOT NULL,
    descripton VARCHAR(255),
    cert_id VARCHAR(255),
    cert_url VARCHAR(255)
);