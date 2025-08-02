# GOHR - API Documentation

## Table of Contents
1. [API Overview](#api-overview)
2. [Authentication](#authentication)
3. [Response Format](#response-format)
4. [Error Handling](#error-handling)
5. [User Management API](#user-management-api)
6. [Attendance Management API](#attendance-management-api)
7. [Dashboard API](#dashboard-api)
8. [Audit Log API](#audit-log-api)
9. [Project Management API](#project-management-api)
10. [Leave Management API](#leave-management-api)
11. [Payroll Management API](#payroll-management-api)
12. [Employee Management API](#employee-management-api)

---

## API Overview

### Base URL
```
Production: https://api.gohr.com/v1
Development: http://localhost:8000/api/v1
```

### Content Type
All API requests and responses use JSON format with the following headers:
```
Content-Type: application/json
Accept: application/json
```

### Rate Limiting
- **Authenticated requests**: 1000 requests per hour
- **Unauthenticated requests**: 60 requests per hour
- **Bulk operations**: 100 requests per hour

---

## Authentication

### Bearer Token Authentication
All API endpoints require authentication using Bearer tokens.

```bash
# Request header
Authorization: Bearer {your-access-token}
```

### Obtaining Access Token

#### Login Endpoint
```http
POST /api/v1/auth/login
```

**Request Body:**
```json
{
    "email": "user@example.com",
    "password": "password123"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "user@example.com",
            "organization_id": 1,
            "roles": ["HR"]
        },
        "access_token": "1|abc123def456...",
        "token_type": "Bearer",
        "expires_in": 3600
    }
}
```

#### Logout Endpoint
```http
POST /api/v1/auth/logout
```

**Headers:**
```
Authorization: Bearer {access-token}
```

**Response:**
```json
{
    "success": true,
    "message": "Successfully logged out"
}
```

---

## Response Format

### Success Response
```json
{
    "success": true,
    "data": {
        // Response data here
    },
    "message": "Operation completed successfully",
    "meta": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 15,
        "total": 75
    }
}
```

### Error Response
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password must be at least 8 characters."]
    },
    "code": "VALIDATION_ERROR"
}
```

### Common HTTP Status Codes
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `429` - Too Many Requests
- `500` - Internal Server Error

---

## Error Handling

### Error Codes
```json
{
    "AUTHENTICATION_ERROR": "Invalid or expired token",
    "AUTHORIZATION_ERROR": "Insufficient permissions",
    "VALIDATION_ERROR": "Request validation failed",
    "NOT_FOUND_ERROR": "Resource not found",
    "ORGANIZATION_ERROR": "Organization access denied",
    "RATE_LIMIT_ERROR": "Rate limit exceeded",
    "INTERNAL_ERROR": "Internal server error"
}
```

### Error Response Example
```json
{
    "success": false,
    "message": "User not found",
    "code": "NOT_FOUND_ERROR",
    "errors": {
        "user_id": ["The specified user does not exist."]
    }
}
```

---

## User Management API

### Get All Users
```http
GET /api/v1/users
```

**Query Parameters:**
- `search` (string) - Search by name or email
- `role` (string) - Filter by role
- `organization_id` (integer) - Filter by organization
- `status` (string) - Filter by status
- `page` (integer) - Page number
- `per_page` (integer) - Items per page (max: 100)

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "organization_id": 1,
            "organization": {
                "id": 1,
                "name": "Acme Corp"
            },
            "roles": [
                {
                    "id": 2,
                    "name": "HR",
                    "display_name": "Human Resources"
                }
            ],
            "status": "active",
            "created_at": "2025-01-15T10:30:00Z",
            "updated_at": "2025-01-15T10:30:00Z"
        }
    ],
    "meta": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 15,
        "total": 75
    }
}
```

### Get User by ID
```http
GET /api/v1/users/{id}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "organization_id": 1,
        "organization": {
            "id": 1,
            "name": "Acme Corp"
        },
        "roles": [
            {
                "id": 2,
                "name": "HR",
                "display_name": "Human Resources"
            }
        ],
        "status": "active",
        "created_at": "2025-01-15T10:30:00Z",
        "updated_at": "2025-01-15T10:30:00Z"
    }
}
```

### Create User
```http
POST /api/v1/users
```

**Request Body:**
```json
{
    "name": "Jane Smith",
    "email": "jane@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "organization_id": 1,
    "role": "Employee"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 2,
        "name": "Jane Smith",
        "email": "jane@example.com",
        "organization_id": 1,
        "roles": [
            {
                "id": 4,
                "name": "Employee",
                "display_name": "Employee"
            }
        ],
        "status": "active",
        "created_at": "2025-01-15T11:00:00Z",
        "updated_at": "2025-01-15T11:00:00Z"
    },
    "message": "User created successfully"
}
```

### Update User
```http
PUT /api/v1/users/{id}
```

**Request Body:**
```json
{
    "name": "Jane Smith Updated",
    "email": "jane.updated@example.com",
    "role": "Manager"
}
```

### Delete User
```http
DELETE /api/v1/users/{id}
```

**Response:**
```json
{
    "success": true,
    "message": "User deleted successfully"
}
```

---

## Attendance Management API

### Get Today's Attendance
```http
GET /api/v1/attendance
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "user_id": 1,
        "date": "2025-01-15",
        "check_in": "2025-01-15T09:00:00Z",
        "check_out": "2025-01-15T17:00:00Z",
        "worked_minutes": 480,
        "status": "Full Day",
        "created_at": "2025-01-15T09:00:00Z",
        "updated_at": "2025-01-15T17:00:00Z"
    }
}
```

### Get All Attendance Records
```http
GET /api/v1/attendance/list
```

**Query Parameters:**
- `user_id` (integer) - Filter by user
- `date_from` (date) - Start date
- `date_to` (date) - End date
- `status` (string) - Filter by status
- `page` (integer) - Page number
- `per_page` (integer) - Items per page

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "user": {
                "id": 1,
                "name": "John Doe",
                "email": "john@example.com"
            },
            "date": "2025-01-15",
            "check_in": "2025-01-15T09:00:00Z",
            "check_out": "2025-01-15T17:00:00Z",
            "worked_minutes": 480,
            "status": "Full Day",
            "created_at": "2025-01-15T09:00:00Z",
            "updated_at": "2025-01-15T17:00:00Z"
        }
    ],
    "meta": {
        "current_page": 1,
        "last_page": 10,
        "per_page": 15,
        "total": 150
    }
}
```

### Check In
```http
POST /api/v1/attendance/check-in
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "user_id": 1,
        "date": "2025-01-15",
        "check_in": "2025-01-15T09:00:00Z",
        "check_out": null,
        "worked_minutes": 0,
        "status": "Incomplete",
        "created_at": "2025-01-15T09:00:00Z",
        "updated_at": "2025-01-15T09:00:00Z"
    },
    "message": "Check-in successful"
}
```

### Check Out
```http
POST /api/v1/attendance/check-out
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "user_id": 1,
        "date": "2025-01-15",
        "check_in": "2025-01-15T09:00:00Z",
        "check_out": "2025-01-15T17:00:00Z",
        "worked_minutes": 480,
        "status": "Full Day",
        "created_at": "2025-01-15T09:00:00Z",
        "updated_at": "2025-01-15T17:00:00Z"
    },
    "message": "Check-out successful"
}
```

### Manual Attendance Entry (HR/Manager Only)
```http
POST /api/v1/attendance/manual
```

**Request Body:**
```json
{
    "attendances": [
        {
            "user_id": 1,
            "date": "2025-01-15",
            "check_in": "2025-01-15T09:00:00Z",
            "check_out": "2025-01-15T17:00:00Z"
        },
        {
            "user_id": 2,
            "date": "2025-01-15",
            "check_in": "2025-01-15T08:30:00Z",
            "check_out": "2025-01-15T16:30:00Z"
        }
    ]
}
```

### Get Attendance Report
```http
GET /api/v1/attendance/report
```

**Query Parameters:**
- `date_from` (date) - Start date
- `date_to` (date) - End date
- `user_id` (integer) - Filter by user
- `organization_id` (integer) - Filter by organization

**Response:**
```json
{
    "success": true,
    "data": {
        "summary": {
            "total_days": 22,
            "present_days": 20,
            "absent_days": 2,
            "attendance_rate": 90.91,
            "total_hours": 160,
            "average_hours_per_day": 8.0
        },
        "daily_records": [
            {
                "date": "2025-01-15",
                "check_in": "09:00",
                "check_out": "17:00",
                "worked_hours": 8.0,
                "status": "Full Day"
            }
        ]
    }
}
```

---

## Dashboard API

### Get Dashboard Data
```http
GET /api/v1/dashboard
```

**Response (Super Admin):**
```json
{
    "success": true,
    "data": {
        "organizations_count": 5,
        "users_count": 150,
        "latest_users": [
            {
                "id": 1,
                "name": "John Doe",
                "email": "john@example.com",
                "organization": {
                    "id": 1,
                    "name": "Acme Corp"
                },
                "created_at": "2025-01-15T10:30:00Z"
            }
        ],
        "monthly_user_growth": [
            {
                "month": "2025-01",
                "count": 25
            }
        ],
        "recent_activities": [
            {
                "id": 1,
                "action": "user_created",
                "user": {
                    "id": 1,
                    "name": "John Doe"
                },
                "created_at": "2025-01-15T10:30:00Z"
            }
        ]
    }
}
```

**Response (HR):**
```json
{
    "success": true,
    "data": {
        "employee_count": 50,
        "today_present": 45,
        "today_absent": 5,
        "attendance_rate": 90.0,
        "recent_activities": [
            {
                "id": 1,
                "action": "attendance_created",
                "user": {
                    "id": 1,
                    "name": "John Doe"
                },
                "created_at": "2025-01-15T09:00:00Z"
            }
        ]
    }
}
```

**Response (Manager):**
```json
{
    "success": true,
    "data": {
        "team_member_count": 8,
        "team_attendance_today": 7,
        "team_absent_today": 1,
        "team_attendance_rate": 87.5,
        "recent_team_activities": [
            {
                "id": 1,
                "action": "attendance_created",
                "user": {
                    "id": 1,
                    "name": "John Doe"
                },
                "created_at": "2025-01-15T09:00:00Z"
            }
        ]
    }
}
```

**Response (Employee):**
```json
{
    "success": true,
    "data": {
        "monthly_attendance_summary": {
            "total_days": 22,
            "present_days": 20,
            "absent_days": 2,
            "attendance_rate": 90.91
        },
        "total_worked_hours": 160,
        "average_hours_per_day": 8.0,
        "recent_attendance": [
            {
                "date": "2025-01-15",
                "check_in": "09:00",
                "check_out": "17:00",
                "worked_hours": 8.0,
                "status": "Full Day"
            }
        ]
    }
}
```

---

## Audit Log API

### Get Audit Logs
```http
GET /api/v1/audit-logs
```

**Query Parameters:**
- `user_id` (integer) - Filter by user
- `action` (string) - Filter by action
- `auditable_type` (string) - Filter by model type
- `date_from` (date) - Start date
- `date_to` (date) - End date
- `page` (integer) - Page number
- `per_page` (integer) - Items per page

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "user": {
                "id": 1,
                "name": "John Doe",
                "email": "john@example.com"
            },
            "action": "user_created",
            "auditable_id": 2,
            "auditable_type": "App\\Models\\User",
            "old_values": null,
            "new_values": {
                "id": 2,
                "name": "Jane Smith",
                "email": "jane@example.com"
            },
            "created_at": "2025-01-15T10:30:00Z"
        }
    ],
    "meta": {
        "current_page": 1,
        "last_page": 20,
        "per_page": 15,
        "total": 300
    }
}
```

---

## Project Management API

### Get All Projects
```http
GET /api/v1/projects
```

**Query Parameters:**
- `search` (string) - Search by name or description
- `status` (string) - Filter by status
- `manager_id` (integer) - Filter by manager
- `organization_id` (integer) - Filter by organization
- `page` (integer) - Page number
- `per_page` (integer) - Items per page

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Website Redesign",
            "description": "Complete redesign of company website",
            "status": "active",
            "start_date": "2025-01-01",
            "end_date": "2025-03-31",
            "budget": 50000.00,
            "manager_id": 1,
            "manager": {
                "id": 1,
                "name": "John Doe",
                "email": "john@example.com"
            },
            "organization_id": 1,
            "progress": 65.5,
            "members_count": 8,
            "tasks_count": 25,
            "completed_tasks_count": 16,
            "created_at": "2025-01-01T00:00:00Z",
            "updated_at": "2025-01-15T10:30:00Z"
        }
    ],
    "meta": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 15,
        "total": 75
    }
}
```

### Get Project by ID
```http
GET /api/v1/projects/{id}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Website Redesign",
        "description": "Complete redesign of company website",
        "status": "active",
        "start_date": "2025-01-01",
        "end_date": "2025-03-31",
        "budget": 50000.00,
        "manager_id": 1,
        "manager": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com"
        },
        "organization_id": 1,
        "progress": 65.5,
        "members": [
            {
                "id": 1,
                "user_id": 1,
                "user": {
                    "id": 1,
                    "name": "John Doe",
                    "email": "john@example.com"
                },
                "role": "manager",
                "joined_date": "2025-01-01T00:00:00Z"
            }
        ],
        "tasks": [
            {
                "id": 1,
                "title": "Design Homepage",
                "description": "Create new homepage design",
                "status": "completed",
                "priority": "high",
                "assigned_to": 1,
                "due_date": "2025-01-15",
                "completed_at": "2025-01-14T17:00:00Z"
            }
        ],
        "created_at": "2025-01-01T00:00:00Z",
        "updated_at": "2025-01-15T10:30:00Z"
    }
}
```

### Create Project
```http
POST /api/v1/projects
```

**Request Body:**
```json
{
    "name": "Mobile App Development",
    "description": "Develop mobile application for iOS and Android",
    "start_date": "2025-02-01",
    "end_date": "2025-06-30",
    "budget": 75000.00,
    "manager_id": 1
}
```

### Update Project
```http
PUT /api/v1/projects/{id}
```

**Request Body:**
```json
{
    "name": "Mobile App Development - Updated",
    "status": "on_hold",
    "budget": 80000.00
}
```

### Delete Project
```http
DELETE /api/v1/projects/{id}
```

### Add Project Member
```http
POST /api/v1/projects/{id}/members
```

**Request Body:**
```json
{
    "user_id": 2,
    "role": "member"
}
```

### Remove Project Member
```http
DELETE /api/v1/projects/{id}/members/{member_id}
```

### Get Project Tasks
```http
GET /api/v1/projects/{id}/tasks
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "title": "Design Homepage",
            "description": "Create new homepage design",
            "status": "completed",
            "priority": "high",
            "assigned_to": 1,
            "assigned_user": {
                "id": 1,
                "name": "John Doe",
                "email": "john@example.com"
            },
            "due_date": "2025-01-15",
            "completed_at": "2025-01-14T17:00:00Z",
            "created_at": "2025-01-01T00:00:00Z",
            "updated_at": "2025-01-14T17:00:00Z"
        }
    ]
}
```

---

## Leave Management API

### Get All Leave Requests
```http
GET /api/v1/leaves
```

**Query Parameters:**
- `user_id` (integer) - Filter by user
- `leave_type_id` (integer) - Filter by leave type
- `status` (string) - Filter by status
- `date_from` (date) - Start date
- `date_to` (date) - End date
- `page` (integer) - Page number
- `per_page` (integer) - Items per page

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "user": {
                "id": 1,
                "name": "John Doe",
                "email": "john@example.com"
            },
            "leave_type_id": 1,
            "leave_type": {
                "id": 1,
                "name": "Annual Leave",
                "color": "#28a745"
            },
            "start_date": "2025-02-01",
            "end_date": "2025-02-05",
            "total_days": 5.0,
            "reason": "Family vacation",
            "status": "pending",
            "approved_by": null,
            "approved_at": null,
            "rejection_reason": null,
            "created_at": "2025-01-15T10:30:00Z",
            "updated_at": "2025-01-15T10:30:00Z"
        }
    ],
    "meta": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 15,
        "total": 75
    }
}
```

### Get Leave Request by ID
```http
GET /api/v1/leaves/{id}
```

### Create Leave Request
```http
POST /api/v1/leaves
```

**Request Body:**
```json
{
    "leave_type_id": 1,
    "start_date": "2025-02-01",
    "end_date": "2025-02-05",
    "reason": "Family vacation"
}
```

### Update Leave Request
```http
PUT /api/v1/leaves/{id}
```

**Request Body:**
```json
{
    "start_date": "2025-02-02",
    "end_date": "2025-02-06",
    "reason": "Updated vacation plans"
}
```

### Delete Leave Request
```http
DELETE /api/v1/leaves/{id}
```

### Approve Leave Request
```http
PATCH /api/v1/leaves/{id}/approve
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "status": "approved",
        "approved_by": 2,
        "approved_at": "2025-01-15T11:00:00Z"
    },
    "message": "Leave request approved successfully"
}
```

### Reject Leave Request
```http
PATCH /api/v1/leaves/{id}/reject
```

**Request Body:**
```json
{
    "rejection_reason": "Insufficient notice period"
}
```

### Get Leave Balance
```http
GET /api/v1/leaves/balance
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "leave_type_id": 1,
            "leave_type": {
                "id": 1,
                "name": "Annual Leave",
                "color": "#28a745"
            },
            "year": 2025,
            "total_days": 25.0,
            "used_days": 5.0,
            "remaining_days": 20.0
        }
    ]
}
```

### Get Leave Calendar
```http
GET /api/v1/leaves/calendar
```

**Query Parameters:**
- `year` (integer) - Year
- `month` (integer) - Month

**Response:**
```json
{
    "success": true,
    "data": {
        "year": 2025,
        "month": 2,
        "leaves": [
            {
                "date": "2025-02-01",
                "leaves": [
                    {
                        "id": 1,
                        "user_name": "John Doe",
                        "leave_type": "Annual Leave",
                        "color": "#28a745",
                        "status": "approved"
                    }
                ]
            }
        ]
    }
}
```

---

## Payroll Management API

### Get All Payrolls
```http
GET /api/v1/payrolls
```

**Query Parameters:**
- `user_id` (integer) - Filter by user
- `month` (integer) - Filter by month
- `year` (integer) - Filter by year
- `status` (string) - Filter by status
- `organization_id` (integer) - Filter by organization
- `page` (integer) - Page number
- `per_page` (integer) - Items per page

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "user": {
                "id": 1,
                "name": "John Doe",
                "email": "john@example.com"
            },
            "month": 1,
            "year": 2025,
            "basic_salary": 5000.00,
            "allowances": {
                "hra": 2000.00,
                "da": 1000.00,
                "ta": 500.00
            },
            "deductions": {
                "tax": 500.00,
                "insurance": 200.00
            },
            "net_salary": 6800.00,
            "working_days": 22,
            "attendance_days": 20,
            "overtime_hours": 8.0,
            "overtime_amount": 400.00,
            "status": "processed",
            "paid_at": "2025-01-31T17:00:00Z",
            "payment_method": "bank_transfer",
            "created_at": "2025-01-31T10:00:00Z",
            "updated_at": "2025-01-31T17:00:00Z"
        }
    ],
    "meta": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 15,
        "total": 75
    }
}
```

### Get Payroll by ID
```http
GET /api/v1/payrolls/{id}
```

### Generate Payroll
```http
POST /api/v1/payrolls/generate
```

**Request Body:**
```json
{
    "month": 1,
    "year": 2025,
    "organization_id": 1
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "generated_count": 50,
        "payrolls": [
            {
                "id": 1,
                "user_id": 1,
                "month": 1,
                "year": 2025,
                "net_salary": 6800.00,
                "status": "pending"
            }
        ]
    },
    "message": "Payroll generated successfully"
}
```

### Update Payroll
```http
PUT /api/v1/payrolls/{id}
```

**Request Body:**
```json
{
    "status": "paid",
    "payment_method": "bank_transfer"
}
```

### Delete Payroll
```http
DELETE /api/v1/payrolls/{id}
```

### Export Payroll PDF
```http
GET /api/v1/payrolls/{id}/export
```

**Response:** PDF file download

### Get Payroll Report
```http
GET /api/v1/payrolls/report
```

**Query Parameters:**
- `month` (integer) - Month
- `year` (integer) - Year
- `organization_id` (integer) - Organization

**Response:**
```json
{
    "success": true,
    "data": {
        "summary": {
            "total_payrolls": 50,
            "total_amount": 340000.00,
            "average_salary": 6800.00,
            "processed_count": 45,
            "pending_count": 5
        },
        "by_department": [
            {
                "department": "Engineering",
                "count": 20,
                "total_amount": 140000.00,
                "average_salary": 7000.00
            }
        ],
        "by_status": [
            {
                "status": "processed",
                "count": 45,
                "total_amount": 306000.00
            }
        ]
    }
}
```

### Get Salary Structures
```http
GET /api/v1/salary-structures
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "user": {
                "id": 1,
                "name": "John Doe",
                "email": "john@example.com"
            },
            "basic_salary": 5000.00,
            "hra": 2000.00,
            "da": 1000.00,
            "ta": 500.00,
            "medical_allowance": 300.00,
            "other_allowances": {
                "special_allowance": 200.00
            },
            "effective_from": "2025-01-01",
            "created_at": "2025-01-01T00:00:00Z",
            "updated_at": "2025-01-01T00:00:00Z"
        }
    ]
}
```

---

## Employee Management API

### Get All Employees
```http
GET /api/v1/employees
```

**Query Parameters:**
- `search` (string) - Search by name or employee ID
- `department` (string) - Filter by department
- `status` (string) - Filter by status
- `manager_id` (integer) - Filter by manager
- `organization_id` (integer) - Filter by organization
- `page` (integer) - Page number
- `per_page` (integer) - Items per page

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "user": {
                "id": 1,
                "name": "John Doe",
                "email": "john@example.com"
            },
            "employee_id": "EMP001",
            "department": "Engineering",
            "position": "Senior Developer",
            "hire_date": "2023-01-15",
            "termination_date": null,
            "status": "active",
            "salary": 75000.00,
            "manager_id": 2,
            "manager": {
                "id": 2,
                "name": "Jane Smith",
                "email": "jane@example.com"
            },
            "organization_id": 1,
            "profile": {
                "date_of_birth": "1990-05-15",
                "gender": "male",
                "phone": "+1234567890",
                "address": "123 Main St, City, State"
            },
            "created_at": "2023-01-15T00:00:00Z",
            "updated_at": "2025-01-15T10:30:00Z"
        }
    ],
    "meta": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 15,
        "total": 75
    }
}
```

### Get Employee by ID
```http
GET /api/v1/employees/{id}
```

### Create Employee
```http
POST /api/v1/employees
```

**Request Body:**
```json
{
    "user_id": 1,
    "employee_id": "EMP002",
    "department": "Marketing",
    "position": "Marketing Manager",
    "hire_date": "2025-02-01",
    "salary": 65000.00,
    "manager_id": 2,
    "profile": {
        "date_of_birth": "1985-08-20",
        "gender": "female",
        "phone": "+1234567891",
        "address": "456 Oak St, City, State"
    }
}
```

### Update Employee
```http
PUT /api/v1/employees/{id}
```

**Request Body:**
```json
{
    "department": "Senior Marketing Manager",
    "salary": 70000.00,
    "profile": {
        "phone": "+1234567892"
    }
}
```

### Delete Employee
```http
DELETE /api/v1/employees/{id}
```

### Get Employee Directory
```http
GET /api/v1/employees/directory
```

**Query Parameters:**
- `department` (string) - Filter by department
- `search` (string) - Search by name

**Response:**
```json
{
    "success": true,
    "data": {
        "departments": [
            {
                "name": "Engineering",
                "employees": [
                    {
                        "id": 1,
                        "name": "John Doe",
                        "position": "Senior Developer",
                        "email": "john@example.com",
                        "phone": "+1234567890",
                        "avatar": "https://example.com/avatar1.jpg"
                    }
                ]
            }
        ]
    }
}
```

### Upload Employee Document
```http
POST /api/v1/employees/{id}/documents
```

**Request Body (multipart/form-data):**
```
document: [file]
document_type: contract
title: Employment Contract
expiry_date: 2026-01-15
```

### Get Employee Documents
```http
GET /api/v1/employees/{id}/documents
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "document_type": "contract",
            "title": "Employment Contract",
            "file_path": "documents/contracts/emp001_contract.pdf",
            "file_size": 1024000,
            "mime_type": "application/pdf",
            "expiry_date": "2026-01-15",
            "status": "active",
            "uploaded_by": 2,
            "created_at": "2025-01-15T10:30:00Z"
        }
    ]
}
```

### Get Employee Report
```http
GET /api/v1/employees/report
```

**Query Parameters:**
- `department` (string) - Filter by department
- `status` (string) - Filter by status
- `hire_date_from` (date) - Filter by hire date from
- `hire_date_to` (date) - Filter by hire date to

**Response:**
```json
{
    "success": true,
    "data": {
        "summary": {
            "total_employees": 150,
            "active_employees": 145,
            "on_leave_employees": 3,
            "terminated_employees": 2
        },
        "by_department": [
            {
                "department": "Engineering",
                "count": 50,
                "percentage": 33.33
            }
        ],
        "by_status": [
            {
                "status": "active",
                "count": 145,
                "percentage": 96.67
            }
        ],
        "hiring_trend": [
            {
                "month": "2025-01",
                "hired": 5,
                "terminated": 1
            }
        ]
    }
}
```

---

## Webhook Endpoints

### Leave Request Webhook
```http
POST /webhooks/leave-request
```

**Headers:**
```
X-Webhook-Signature: {signature}
Content-Type: application/json
```

**Request Body:**
```json
{
    "event": "leave.request.created",
    "data": {
        "leave_id": 1,
        "user_id": 1,
        "user_name": "John Doe",
        "leave_type": "Annual Leave",
        "start_date": "2025-02-01",
        "end_date": "2025-02-05",
        "status": "pending"
    },
    "timestamp": "2025-01-15T10:30:00Z"
}
```

### Payroll Generated Webhook
```http
POST /webhooks/payroll-generated
```

**Request Body:**
```json
{
    "event": "payroll.generated",
    "data": {
        "payroll_id": 1,
        "user_id": 1,
        "user_name": "John Doe",
        "month": 1,
        "year": 2025,
        "net_salary": 6800.00,
        "status": "pending"
    },
    "timestamp": "2025-01-31T10:00:00Z"
}
```

---

## SDK Examples

### PHP SDK Example
```php
<?php

use Gohr\GohrClient;

$client = new GohrClient([
    'base_url' => 'https://api.gohr.com/v1',
    'access_token' => 'your-access-token'
]);

// Get all users
$users = $client->users()->all([
    'page' => 1,
    'per_page' => 15
]);

// Create a user
$user = $client->users()->create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => 'password123',
    'organization_id' => 1,
    'role' => 'Employee'
]);

// Get attendance for today
$attendance = $client->attendance()->today();

// Check in
$client->attendance()->checkIn();
```

### JavaScript SDK Example
```javascript
import { GohrClient } from '@gohr/sdk';

const client = new GohrClient({
    baseUrl: 'https://api.gohr.com/v1',
    accessToken: 'your-access-token'
});

// Get all projects
const projects = await client.projects.all({
    page: 1,
    perPage: 15
});

// Create a leave request
const leave = await client.leaves.create({
    leaveTypeId: 1,
    startDate: '2025-02-01',
    endDate: '2025-02-05',
    reason: 'Family vacation'
});

// Get dashboard data
const dashboard = await client.dashboard.get();
```

---

**API Version**: 1.0  
**Last Updated**: January 2025  
**Next Review**: February 2025  
**Status**: Active Development 