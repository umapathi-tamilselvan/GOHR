# GOHR HR Management System - API Documentation

## 📋 Table of Contents
1. [Authentication](#authentication)
2. [Employee Management](#employee-management)
3. [Attendance Management](#attendance-management)
4. [Leave Management](#leave-management)
5. [Payroll Management](#payroll-management)
6. [Performance Management](#performance-management)
7. [Document Management](#document-management)
8. [Workflow Management](#workflow-management)
9. [Reporting APIs](#reporting-apis)
10. [Error Handling](#error-handling)

## 🔐 Authentication

### Base URL
```
https://api.gohr.com/v1
```

### Authentication Headers
```http
Authorization: Bearer {access_token}
Accept: application/json
Content-Type: application/json
```

### Login
```http
POST /auth/login
```

**Request Body:**
```json
{
    "email": "employee@gohr.com",
    "password": "password123",
    "device_name": "Web Browser"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "employee_id": "EMP001",
            "first_name": "John",
            "last_name": "Doe",
            "email": "employee@gohr.com",
            "designation": "Software Developer",
            "department": "IT",
            "status": "active"
        },
        "token": "1|abc123...",
        "permissions": ["view_attendance", "apply_leave", "view_payslip"]
    },
    "message": "Login successful"
}
```

### Logout
```http
POST /auth/logout
```

**Response:**
```json
{
    "success": true,
    "message": "Logged out successfully"
}
```

### Refresh Token
```http
POST /auth/refresh
```

**Response:**
```json
{
    "success": true,
    "data": {
        "token": "2|def456...",
        "expires_at": "2025-02-26T10:00:00Z"
    }
}
```

## 👥 Employee Management

### Get Employees List
```http
GET /employees?page=1&per_page=20&search=john&department=IT&status=active
```

**Query Parameters:**
- `page`: Page number (default: 1)
- `per_page`: Items per page (default: 20, max: 100)
- `search`: Search in name, email, employee_id
- `department`: Filter by department
- `status`: Filter by status (active, inactive, terminated)
- `sort_by`: Sort field (name, email, joining_date, designation)
- `sort_order`: Sort order (asc, desc)

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "employee_id": "EMP001",
            "first_name": "John",
            "last_name": "Doe",
            "email": "john.doe@gohr.com",
            "phone": "+91-9876543210",
            "designation": "Software Developer",
            "department": "IT",
            "manager": {
                "id": 5,
                "name": "Jane Smith"
            },
            "joining_date": "2023-01-15",
            "status": "active"
        }
    ],
    "meta": {
        "current_page": 1,
        "per_page": 20,
        "total": 150,
        "last_page": 8
    }
}
```

### Get Employee Details
```http
GET /employees/{id}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "employee_id": "EMP001",
        "first_name": "John",
        "last_name": "Doe",
        "email": "john.doe@gohr.com",
        "phone": "+91-9876543210",
        "date_of_birth": "1990-05-15",
        "gender": "male",
        "marital_status": "married",
        "nationality": "Indian",
        "designation": "Software Developer",
        "department": {
            "id": 1,
            "name": "IT",
            "code": "IT"
        },
        "manager": {
            "id": 5,
            "name": "Jane Smith",
            "designation": "Team Lead"
        },
        "joining_date": "2023-01-15",
        "status": "active",
        "profile": {
            "blood_group": "O+",
            "emergency_contact": {
                "name": "Jane Doe",
                "phone": "+91-9876543211",
                "relationship": "Spouse"
            },
            "current_address": "123 Main Street, Bangalore",
            "permanent_address": "456 Old Street, Mumbai"
        },
        "documents": [
            {
                "id": 1,
                "type": "PAN Card",
                "name": "PAN Card",
                "status": "verified",
                "expiry_date": null
            }
        ]
    }
}
```

### Create Employee
```http
POST /employees
```

**Request Body:**
```json
{
    "employee_id": "EMP002",
    "first_name": "Jane",
    "last_name": "Smith",
    "email": "jane.smith@gohr.com",
    "phone": "+91-9876543212",
    "date_of_birth": "1988-08-20",
    "gender": "female",
    "designation": "HR Manager",
    "department_id": 2,
    "manager_id": 1,
    "joining_date": "2025-01-20",
    "password": "password123",
    "profile": {
        "blood_group": "A+",
        "emergency_contact_name": "John Smith",
        "emergency_contact_phone": "+91-9876543213",
        "emergency_contact_relationship": "Spouse"
    }
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 2,
        "employee_id": "EMP002",
        "first_name": "Jane",
        "last_name": "Smith",
        "email": "jane.smith@gohr.com",
        "status": "active",
        "message": "Employee created successfully"
    }
}
```

### Update Employee
```http
PUT /employees/{id}
```

**Request Body:**
```json
{
    "phone": "+91-9876543214",
    "designation": "Senior HR Manager",
    "profile": {
        "current_address": "789 New Street, Bangalore"
    }
}
```

### Delete Employee
```http
DELETE /employees/{id}
```

**Response:**
```json
{
    "success": true,
    "message": "Employee deleted successfully"
}
```

## ⏰ Attendance Management

### Clock In
```http
POST /attendance/clock-in
```

**Request Body:**
```json
{
    "location": {
        "latitude": 12.9716,
        "longitude": 77.5946,
        "address": "Bangalore, Karnataka, India"
    },
    "device_type": "mobile"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "employee_id": 1,
        "date": "2025-01-27",
        "clock_in": "2025-01-27T09:00:00Z",
        "status": "present",
        "message": "Clock in successful"
    }
}
```

### Clock Out
```http
POST /attendance/clock-out
```

**Request Body:**
```json
{
    "location": {
        "latitude": 12.9716,
        "longitude": 77.5946,
        "address": "Bangalore, Karnataka, India"
    }
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "clock_out": "2025-01-27T18:00:00Z",
        "total_hours": 8.0,
        "overtime_hours": 0.0,
        "message": "Clock out successful"
    }
}
```

### Get Attendance Report
```http
GET /attendance/report?start_date=2025-01-01&end_date=2025-01-31&employee_id=1&department=IT
```

**Query Parameters:**
- `start_date`: Start date (YYYY-MM-DD)
- `end_date`: End date (YYYY-MM-DD)
- `employee_id`: Filter by employee
- `department`: Filter by department
- `status`: Filter by status (present, absent, half-day, leave)

**Response:**
```json
{
    "success": true,
    "data": {
        "summary": {
            "total_days": 31,
            "present_days": 22,
            "absent_days": 2,
            "leave_days": 7,
            "total_hours": 176.0,
            "overtime_hours": 8.0
        },
        "attendance": [
            {
                "date": "2025-01-01",
                "status": "present",
                "clock_in": "2025-01-01T09:00:00Z",
                "clock_out": "2025-01-01T18:00:00Z",
                "total_hours": 8.0,
                "overtime_hours": 0.0
            }
        ]
    }
}
```

### Update Attendance
```http
PUT /attendance/{id}
```

**Request Body:**
```json
{
    "clock_in": "2025-01-27T09:15:00Z",
    "notes": "Late due to traffic"
}
```

## 🏖️ Leave Management

### Get Leave Types
```http
GET /leave-types
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Casual Leave",
            "code": "CL",
            "default_balance": 12.0,
            "max_balance": 15.0,
            "is_paid": true,
            "color_code": "#3B82F6"
        },
        {
            "id": 2,
            "name": "Sick Leave",
            "code": "SL",
            "default_balance": 15.0,
            "max_balance": 20.0,
            "is_paid": true,
            "color_code": "#EF4444"
        }
    ]
}
```

### Apply Leave
```http
POST /leaves
```

**Request Body:**
```json
{
    "leave_type_id": 1,
    "start_date": "2025-02-15",
    "end_date": "2025-02-16",
    "total_days": 2.0,
    "reason": "Personal work"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "leave_type": "Casual Leave",
        "start_date": "2025-02-15",
        "end_date": "2025-02-16",
        "total_days": 2.0,
        "status": "pending",
        "message": "Leave application submitted successfully"
    }
}
```

### Get Leave Balance
```http
GET /leave-balance
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "leave_type": "Casual Leave",
            "initial_balance": 12.0,
            "earned_balance": 1.0,
            "used_balance": 3.0,
            "current_balance": 10.0,
            "max_balance": 15.0
        },
        {
            "leave_type": "Sick Leave",
            "initial_balance": 15.0,
            "earned_balance": 1.25,
            "used_balance": 2.0,
            "current_balance": 14.25,
            "max_balance": 20.0
        }
    ]
}
```

### Approve/Reject Leave
```http
POST /leaves/{id}/approve
```

**Request Body:**
```json
{
    "action": "approve", // or "reject"
    "comments": "Approved"
}
```

## 💰 Payroll Management

### Get Payroll List
```http
GET /payroll?month=1&year=2025&status=processed
```

**Query Parameters:**
- `month`: Month (1-12)
- `year`: Year (YYYY)
- `status`: Filter by status (draft, approved, processed, paid)
- `employee_id`: Filter by employee
- `department`: Filter by department

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "employee": {
                "id": 1,
                "name": "John Doe",
                "employee_id": "EMP001"
            },
            "month": 1,
            "year": 2025,
            "basic_salary": 50000.00,
            "gross_salary": 65000.00,
            "net_salary": 58000.00,
            "status": "processed",
            "processed_at": "2025-01-31T18:00:00Z"
        }
    ]
}
```

### Process Payroll
```http
POST /payroll/process
```

**Request Body:**
```json
{
    "month": 1,
    "year": 2025,
    "employee_ids": [1, 2, 3] // Optional: specific employees
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "processed_count": 150,
        "total_amount": 9750000.00,
        "message": "Payroll processed successfully"
    }
}
```

### Get Payslip
```http
GET /payroll/{id}/payslip
```

**Response:**
```json
{
    "success": true,
    "data": {
        "employee": {
            "name": "John Doe",
            "employee_id": "EMP001",
            "designation": "Software Developer",
            "department": "IT"
        },
        "payroll_period": "January 2025",
        "earnings": [
            {
                "component": "Basic Salary",
                "amount": 50000.00,
                "is_taxable": true
            },
            {
                "component": "HRA",
                "amount": 20000.00,
                "is_taxable": false
            }
        ],
        "deductions": [
            {
                "component": "TDS",
                "amount": 5000.00
            },
            {
                "component": "PF",
                "amount": 6000.00
            }
        ],
        "summary": {
            "gross_salary": 65000.00,
            "total_deductions": 11000.00,
            "net_salary": 54000.00
        }
    }
}
```

## 🎯 Performance Management

### Get Performance Reviews
```http
GET /performance-reviews?employee_id=1&status=completed
```

**Query Parameters:**
- `employee_id`: Filter by employee
- `status`: Filter by status (draft, in_progress, completed, archived)
- `review_type`: Filter by type (annual, quarterly, project, probation)
- `review_period`: Filter by period

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "review_period": "2024-Annual",
            "review_type": "annual",
            "overall_rating": 4.2,
            "status": "completed",
            "review_date": "2024-12-31",
            "next_review_date": "2025-12-31",
            "reviewer": {
                "id": 5,
                "name": "Jane Smith"
            }
        }
    ]
}
```

### Create Performance Review
```http
POST /performance-reviews
```

**Request Body:**
```json
{
    "employee_id": 1,
    "review_period": "2025-Q1",
    "review_type": "quarterly",
    "overall_rating": 4.0,
    "goals": [
        {
            "goal_title": "Complete Project A",
            "goal_description": "Finish the development of Project A",
            "target_date": "2025-03-31",
            "weightage": 40.0
        }
    ]
}
```

### Submit Performance Feedback
```http
POST /performance-reviews/{id}/feedback
```

**Request Body:**
```json
{
    "feedback_type": "peer",
    "feedback_text": "Great team player and excellent technical skills",
    "rating": 4.5,
    "is_anonymous": false
}
```

## 📄 Document Management

### Upload Document
```http
POST /employees/{id}/documents
```

**Request Body (Multipart):**
```form-data
document_type: "PAN Card"
file: [file upload]
expiry_date: "2030-12-31"
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "document_type": "PAN Card",
        "document_name": "PAN Card",
        "file_path": "documents/1/pan_card.pdf",
        "file_size": 1024000,
        "status": "active",
        "message": "Document uploaded successfully"
    }
}
```

### Get Employee Documents
```http
GET /employees/{id}/documents
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "document_type": "PAN Card",
            "document_name": "PAN Card",
            "file_path": "documents/1/pan_card.pdf",
            "file_size": 1024000,
            "status": "verified",
            "verified_by": "Jane Smith",
            "verified_at": "2025-01-15T10:00:00Z"
        }
    ]
}
```

## 🔄 Workflow Management

### Get Workflows
```http
GET /workflows
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Leave Approval",
            "workflow_type": "leave_approval",
            "approval_levels": [
                {
                    "level": 1,
                    "role": "Manager",
                    "approval_type": "mandatory"
                },
                {
                    "level": 2,
                    "role": "HR",
                    "approval_type": "conditional"
                }
            ]
        }
    ]
}
```

### Initiate Workflow
```http
POST /workflows/{id}/initiate
```

**Request Body:**
```json
{
    "data": {
        "leave_id": 1,
        "employee_id": 1,
        "leave_type": "Casual Leave",
        "days": 2
    }
}
```

### Approve Workflow
```http
POST /workflow-instances/{id}/approve
```

**Request Body:**
```json
{
    "action": "approve",
    "comments": "Approved"
}
```

## 📊 Reporting APIs

### Get Dashboard Data
```http
GET /dashboard
```

**Response:**
```json
{
    "success": true,
    "data": {
        "employee_count": 150,
        "present_today": 142,
        "on_leave_today": 8,
        "pending_approvals": 12,
        "monthly_attendance": {
            "present": 22,
            "absent": 2,
            "leave": 7
        },
        "recent_activities": [
            {
                "type": "leave_approved",
                "message": "John Doe's leave approved",
                "timestamp": "2025-01-27T10:00:00Z"
            }
        ]
    }
}
```

### Generate Report
```http
POST /reports/generate
```

**Request Body:**
```json
{
    "report_type": "attendance",
    "parameters": {
        "start_date": "2025-01-01",
        "end_date": "2025-01-31",
        "department": "IT"
    },
    "format": "pdf" // pdf, excel, csv
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "report_id": "REP_001",
        "download_url": "https://api.gohr.com/reports/REP_001/download",
        "expires_at": "2025-02-27T10:00:00Z"
    }
}
```

## ❌ Error Handling

### Standard Error Response
```json
{
    "success": false,
    "error": {
        "code": "VALIDATION_ERROR",
        "message": "Validation failed",
        "details": {
            "email": ["The email field is required."],
            "phone": ["The phone field must be a valid phone number."]
        }
    }
}
```

### Common Error Codes
- `UNAUTHORIZED`: Authentication required
- `FORBIDDEN`: Insufficient permissions
- `NOT_FOUND`: Resource not found
- `VALIDATION_ERROR`: Request validation failed
- `RATE_LIMIT_EXCEEDED`: Too many requests
- `INTERNAL_ERROR`: Server error

### Rate Limiting
```http
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 999
X-RateLimit-Reset: 1643270400
```

### Pagination
```json
{
    "meta": {
        "current_page": 1,
        "per_page": 20,
        "total": 150,
        "last_page": 8,
        "from": 1,
        "to": 20
    }
}
```

---

## 📝 Document Information

**Document Version**: 1.0  
**Last Updated**: January 2025  
**Author**: GOHR Development Team  
**Review Cycle**: Quarterly  
**Next Review**: April 2025  

---

*This API documentation provides comprehensive details of all GOHR HR Management System endpoints.* 