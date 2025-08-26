# GOHR HR Management System - Database Schema

## 📋 Table of Contents
1. [Core Tables](#core-tables)
2. [Employee Management](#employee-management)
3. [Attendance Management](#attendance-management)
4. [Leave Management](#leave-management)
5. [Payroll Management](#payroll-management)
6. [Performance Management](#performance-management)
7. [Document Management](#document-management)
8. [Workflow Management](#workflow-management)
9. [System Tables](#system-tables)
10. [Indexes and Optimization](#indexes-and-optimization)

## 🏗️ Core Tables

### Users Table
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    employee_id VARCHAR(50) UNIQUE NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other'),
    marital_status ENUM('single', 'married', 'divorced', 'widowed'),
    nationality VARCHAR(100),
    designation VARCHAR(100),
    department_id BIGINT UNSIGNED,
    manager_id BIGINT UNSIGNED,
    joining_date DATE,
    status ENUM('active', 'inactive', 'terminated') DEFAULT 'active',
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255),
    remember_token VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_employee_id (employee_id),
    INDEX idx_email (email),
    INDEX idx_department (department_id),
    INDEX idx_manager (manager_id),
    INDEX idx_status (status)
);
```

### Organizations Table
```sql
CREATE TABLE organizations (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    legal_name VARCHAR(255),
    registration_number VARCHAR(100),
    tax_id VARCHAR(100),
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    country VARCHAR(100) DEFAULT 'India',
    postal_code VARCHAR(20),
    phone VARCHAR(20),
    email VARCHAR(255),
    website VARCHAR(255),
    logo_path VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Departments Table
```sql
CREATE TABLE departments (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    organization_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(50) UNIQUE,
    description TEXT,
    head_employee_id BIGINT UNSIGNED,
    parent_department_id BIGINT UNSIGNED,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (organization_id) REFERENCES organizations(id),
    FOREIGN KEY (head_employee_id) REFERENCES users(id),
    FOREIGN KEY (parent_department_id) REFERENCES departments(id)
);
```

## 👥 Employee Management

### Employee Profiles Table
```sql
CREATE TABLE employee_profiles (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    blood_group VARCHAR(10),
    emergency_contact_name VARCHAR(100),
    emergency_contact_phone VARCHAR(20),
    emergency_contact_relationship VARCHAR(50),
    current_address TEXT,
    permanent_address TEXT,
    emergency_address TEXT,
    pan_number VARCHAR(20),
    aadhaar_number VARCHAR(20),
    passport_number VARCHAR(50),
    driving_license VARCHAR(50),
    bank_account_number VARCHAR(50),
    bank_name VARCHAR(100),
    bank_branch VARCHAR(100),
    ifsc_code VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_pan (pan_number),
    INDEX idx_aadhaar (aadhaar_number)
);
```

### Family Members Table
```sql
CREATE TABLE family_members (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    employee_profile_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    relationship VARCHAR(50) NOT NULL,
    date_of_birth DATE,
    occupation VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(255),
    is_dependent BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (employee_profile_id) REFERENCES employee_profiles(id) ON DELETE CASCADE
);
```

## ⏰ Attendance Management

### Shifts Table
```sql
CREATE TABLE shifts (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    break_duration INT DEFAULT 0, -- in minutes
    overtime_threshold INT DEFAULT 8, -- in hours
    grace_period INT DEFAULT 15, -- in minutes
    is_night_shift BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    color_code VARCHAR(7), -- hex color
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Attendances Table
```sql
CREATE TABLE attendances (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    employee_id BIGINT UNSIGNED NOT NULL,
    date DATE NOT NULL,
    shift_id BIGINT UNSIGNED,
    clock_in DATETIME,
    clock_out DATETIME,
    break_start DATETIME,
    break_end DATETIME,
    total_hours DECIMAL(5,2),
    overtime_hours DECIMAL(5,2),
    status ENUM('present', 'absent', 'half-day', 'leave') DEFAULT 'present',
    location_data JSON,
    device_type ENUM('web', 'mobile', 'biometric'),
    ip_address VARCHAR(45),
    user_agent TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_employee_date (employee_id, date),
    FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (shift_id) REFERENCES shifts(id),
    INDEX idx_employee_date (employee_id, date),
    INDEX idx_status (status),
    INDEX idx_shift (shift_id)
);
```

### Holidays Table
```sql
CREATE TABLE holidays (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    organization_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    date DATE NOT NULL,
    type ENUM('national', 'regional', 'company', 'optional') DEFAULT 'national',
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (organization_id) REFERENCES organizations(id),
    UNIQUE KEY unique_org_date (organization_id, date)
);
```

## 🏖️ Leave Management

### Leave Types Table
```sql
CREATE TABLE leave_types (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    organization_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(50) UNIQUE,
    description TEXT,
    default_balance DECIMAL(5,2) DEFAULT 0,
    max_balance DECIMAL(5,2),
    is_paid BOOLEAN DEFAULT TRUE,
    is_active BOOLEAN DEFAULT TRUE,
    color_code VARCHAR(7),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (organization_id) REFERENCES organizations(id)
);
```

### Leaves Table
```sql
CREATE TABLE leaves (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    employee_id BIGINT UNSIGNED NOT NULL,
    leave_type_id BIGINT UNSIGNED NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_days DECIMAL(5,2) NOT NULL,
    reason TEXT,
    status ENUM('pending', 'approved', 'rejected', 'cancelled') DEFAULT 'pending',
    approved_by BIGINT UNSIGNED,
    approved_at TIMESTAMP NULL,
    rejection_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (leave_type_id) REFERENCES leave_types(id),
    FOREIGN KEY (approved_by) REFERENCES users(id),
    INDEX idx_employee (employee_id),
    INDEX idx_status (status),
    INDEX idx_dates (start_date, end_date)
);
```

### Leave Balances Table
```sql
CREATE TABLE leave_balances (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    employee_id BIGINT UNSIGNED NOT NULL,
    leave_type_id BIGINT UNSIGNED NOT NULL,
    year YEAR NOT NULL,
    initial_balance DECIMAL(5,2) DEFAULT 0,
    earned_balance DECIMAL(5,2) DEFAULT 0,
    used_balance DECIMAL(5,2) DEFAULT 0,
    current_balance DECIMAL(5,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_employee_type_year (employee_id, leave_type_id, year),
    FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (leave_type_id) REFERENCES leave_types(id)
);
```

## 💰 Payroll Management

### Salary Structures Table
```sql
CREATE TABLE salary_structures (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    organization_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (organization_id) REFERENCES organizations(id)
);
```

### Salary Components Table
```sql
CREATE TABLE salary_components (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    salary_structure_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    type ENUM('earning', 'deduction') NOT NULL,
    calculation_type ENUM('fixed', 'percentage', 'formula') DEFAULT 'fixed',
    value DECIMAL(10,2) DEFAULT 0,
    formula TEXT,
    is_taxable BOOLEAN DEFAULT FALSE,
    is_pf_applicable BOOLEAN DEFAULT FALSE,
    is_esi_applicable BOOLEAN DEFAULT FALSE,
    order_index INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (salary_structure_id) REFERENCES salary_structures(id)
);
```

### Payrolls Table
```sql
CREATE TABLE payrolls (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    employee_id BIGINT UNSIGNED NOT NULL,
    month TINYINT NOT NULL,
    year YEAR NOT NULL,
    basic_salary DECIMAL(12,2) NOT NULL,
    gross_salary DECIMAL(12,2) NOT NULL,
    net_salary DECIMAL(12,2) NOT NULL,
    total_earnings DECIMAL(12,2) DEFAULT 0,
    total_deductions DECIMAL(12,2) DEFAULT 0,
    tds_amount DECIMAL(12,2) DEFAULT 0,
    pf_amount DECIMAL(12,2) DEFAULT 0,
    esi_amount DECIMAL(12,2) DEFAULT 0,
    professional_tax DECIMAL(8,2) DEFAULT 0,
    working_days INT DEFAULT 0,
    paid_days INT DEFAULT 0,
    status ENUM('draft', 'approved', 'processed', 'paid') DEFAULT 'draft',
    processed_at TIMESTAMP NULL,
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_employee_month_year (employee_id, month, year),
    FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_month_year (month, year),
    INDEX idx_status (status)
);
```

### Payroll Details Table
```sql
CREATE TABLE payroll_details (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    payroll_id BIGINT UNSIGNED NOT NULL,
    component_name VARCHAR(100) NOT NULL,
    component_type ENUM('earning', 'deduction') NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    is_taxable BOOLEAN DEFAULT FALSE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (payroll_id) REFERENCES payrolls(id) ON DELETE CASCADE
);
```

## 🎯 Performance Management

### Performance Reviews Table
```sql
CREATE TABLE performance_reviews (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    employee_id BIGINT UNSIGNED NOT NULL,
    reviewer_id BIGINT UNSIGNED NOT NULL,
    review_period VARCHAR(50) NOT NULL, -- '2024-Q1', '2024-Annual'
    review_type ENUM('annual', 'quarterly', 'project', 'probation') DEFAULT 'annual',
    overall_rating DECIMAL(3,2),
    status ENUM('draft', 'in_progress', 'completed', 'archived') DEFAULT 'draft',
    review_date DATE,
    next_review_date DATE,
    self_assessment_completed BOOLEAN DEFAULT FALSE,
    manager_review_completed BOOLEAN DEFAULT FALSE,
    hr_review_completed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(id),
    INDEX idx_employee (employee_id),
    INDEX idx_reviewer (reviewer_id),
    INDEX idx_period (review_period)
);
```

### Performance Goals Table
```sql
CREATE TABLE performance_goals (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    performance_review_id BIGINT UNSIGNED NOT NULL,
    goal_title VARCHAR(255) NOT NULL,
    goal_description TEXT,
    target_date DATE,
    achievement_criteria TEXT,
    weightage DECIMAL(5,2) DEFAULT 0,
    status ENUM('not_started', 'in_progress', 'completed', 'overdue') DEFAULT 'not_started',
    achievement_percentage DECIMAL(5,2) DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (performance_review_id) REFERENCES performance_reviews(id) ON DELETE CASCADE
);
```

### Performance Feedback Table
```sql
CREATE TABLE performance_feedback (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    performance_review_id BIGINT UNSIGNED NOT NULL,
    feedback_provider_id BIGINT UNSIGNED NOT NULL,
    feedback_type ENUM('self', 'manager', 'peer', 'subordinate', 'customer') NOT NULL,
    feedback_text TEXT NOT NULL,
    rating DECIMAL(3,2),
    is_anonymous BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (performance_review_id) REFERENCES performance_reviews(id) ON DELETE CASCADE,
    FOREIGN KEY (feedback_provider_id) REFERENCES users(id)
);
```

## 📄 Document Management

### Employee Documents Table
```sql
CREATE TABLE employee_documents (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    employee_id BIGINT UNSIGNED NOT NULL,
    document_type VARCHAR(100) NOT NULL,
    document_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size BIGINT UNSIGNED,
    file_type VARCHAR(100),
    mime_type VARCHAR(100),
    version VARCHAR(20) DEFAULT '1.0',
    expiry_date DATE,
    is_verified BOOLEAN DEFAULT FALSE,
    verified_by BIGINT UNSIGNED,
    verified_at TIMESTAMP NULL,
    status ENUM('active', 'expired', 'archived') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(id),
    INDEX idx_employee (employee_id),
    INDEX idx_type (document_type),
    INDEX idx_status (status)
);
```

## 🔄 Workflow Management

### Workflows Table
```sql
CREATE TABLE workflows (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    workflow_type ENUM('leave_approval', 'expense_approval', 'recruitment', 'performance', 'custom') NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    approval_levels JSON NOT NULL,
    escalation_rules JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Workflow Instances Table
```sql
CREATE TABLE workflow_instances (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    workflow_id BIGINT UNSIGNED NOT NULL,
    initiator_id BIGINT UNSIGNED NOT NULL,
    current_level INT DEFAULT 1,
    status ENUM('pending', 'in_progress', 'approved', 'rejected', 'cancelled') DEFAULT 'pending',
    data JSON NOT NULL,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (workflow_id) REFERENCES workflows(id),
    FOREIGN KEY (initiator_id) REFERENCES users(id),
    INDEX idx_workflow (workflow_id),
    INDEX idx_status (status)
);
```

### Workflow Approvals Table
```sql
CREATE TABLE workflow_approvals (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    workflow_instance_id BIGINT UNSIGNED NOT NULL,
    approver_id BIGINT UNSIGNED NOT NULL,
    level INT NOT NULL,
    action ENUM('approve', 'reject', 'return') NOT NULL,
    comments TEXT,
    approved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (workflow_instance_id) REFERENCES workflow_instances(id) ON DELETE CASCADE,
    FOREIGN KEY (approver_id) REFERENCES users(id)
);
```

## 🔧 System Tables

### Roles Table
```sql
CREATE TABLE roles (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) UNIQUE NOT NULL,
    guard_name VARCHAR(100) DEFAULT 'web',
    display_name VARCHAR(100),
    description TEXT,
    is_system_role BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Permissions Table
```sql
CREATE TABLE permissions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) UNIQUE NOT NULL,
    guard_name VARCHAR(100) DEFAULT 'web',
    display_name VARCHAR(100),
    description TEXT,
    module VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Audit Logs Table
```sql
CREATE TABLE audit_logs (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED,
    action VARCHAR(100) NOT NULL,
    model_type VARCHAR(100),
    model_id BIGINT UNSIGNED,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_model (model_type, model_id),
    INDEX idx_created (created_at)
);
```

## 📊 Indexes and Optimization

### Performance Indexes
```sql
-- Composite indexes for common queries
CREATE INDEX idx_attendance_employee_date_status ON attendances(employee_id, date, status);
CREATE INDEX idx_leave_employee_status_dates ON leaves(employee_id, status, start_date, end_date);
CREATE INDEX idx_payroll_employee_status ON payrolls(employee_id, status);
CREATE INDEX idx_performance_employee_period ON performance_reviews(employee_id, review_period);

-- Full-text search indexes
CREATE FULLTEXT INDEX idx_users_search ON users(first_name, last_name, email, employee_id);
CREATE FULLTEXT INDEX idx_documents_search ON employee_documents(document_name, document_type);

-- Date-based indexes for reporting
CREATE INDEX idx_attendance_date ON attendances(date);
CREATE INDEX idx_leave_dates ON leaves(start_date, end_date);
CREATE INDEX idx_payroll_month_year ON payrolls(month, year);
```

### Partitioning Strategy
```sql
-- Partition attendance table by month for better performance
ALTER TABLE attendances PARTITION BY RANGE (YEAR(date) * 100 + MONTH(date)) (
    PARTITION p202401 VALUES LESS THAN (202402),
    PARTITION p202402 VALUES LESS THAN (202403),
    PARTITION p202403 VALUES LESS THAN (202404),
    -- Add more partitions as needed
    PARTITION p_future VALUES LESS THAN MAXVALUE
);
```

---

## 📝 Document Information

**Document Version**: 1.0  
**Last Updated**: January 2025  
**Author**: GOHR Development Team  
**Review Cycle**: Quarterly  
**Next Review**: April 2025  

---

*This database schema document provides the complete structure for the GOHR HR Management System database.* 