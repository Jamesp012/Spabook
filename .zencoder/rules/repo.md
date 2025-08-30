---
description: Repository Information Overview
alwaysApply: true
---

# SpaBook Information

## Summary
SpaBook is a PHP-based spa booking and management system that allows users to schedule spa services, manage bookings, and handle therapist assignments. It features a dual-interface system with separate admin and user portals, real-time notifications, and integration with Supabase for database operations.

## Structure
- **admin/**: Admin-specific functionality and controllers
- **components/**: Reusable UI components like notification badges
- **config/**: Database connection and authentication configuration
- **controller/**: Business logic controllers for bookings, users, services, etc.
- **helper/**: Utility functions and input validation
- **model/**: Data models for database operations
- **utils/**: Utility functions for common operations
- **vendor/**: Frontend libraries (Bootstrap, jQuery, SweetAlert, FontAwesome)
- **views/**: UI templates for admin and user interfaces
  - **views/admin/**: Admin dashboard and management screens
  - **views/user/**: User booking and profile management screens
  - **views/modal/**: Reusable modal components

## Language & Runtime
**Language**: PHP
**Version**: 8.2.4
**Web Server**: Apache (XAMPP)
**Database**: PostgreSQL (via Supabase)

## Dependencies
**Main Dependencies**:
- Bootstrap 5 (Frontend framework)
- jQuery (JavaScript library)
- SweetAlert2 (Alert/modal library)
- FontAwesome (Icon library)
- Supabase JS Client (Database operations)
- Moment.js (Date/time handling)

## Database
**Type**: PostgreSQL (via Supabase)
**Connection**: REST API with custom PHP wrapper functions
**Main Tables**:
- users: User accounts and profiles
- booking: Main booking records
- booking_details: Detailed booking information
- services: Available spa services
- therapists: Therapist information
- notification: System notifications

## Authentication
**Unified Login System**: Single login page for all user types
**Providers**: 
- Supabase Auth (Users/Admins): Email/Password, Google OAuth
- Database Auth (Therapists): Email/Password with bcrypt hashing
**Auto-Detection**: System automatically determines user type and redirects appropriately

## Key Features
- User booking system with service selection
- Admin dashboard for booking management
- Therapist assignment and management
- Real-time notification system
- Service catalog management
- User profile management
- Payment processing integration

## Performance Optimizations
- Database indexing for common queries
- Connection pooling for Supabase
- Query optimization for complex operations
- Caching for expensive operations
- Asset optimization for images and scripts

## Main Entry Points
- **index.php**: Main application entry point (login page)
- **views/user_home_page.php**: User dashboard after login
- **views/admin_home_page.php**: Admin dashboard after login
- **views/therapist_home_page.php**: Therapist dashboard after login

## API Structure
- **controller/user_contr.php**: User management endpoints
- **controller/booking_contr.php**: Booking operations
- **controller/service_contr.php**: Service management
- **controller/therapist_contr.php**: Therapist management
- **controller/therapist_auth_contr.php**: Therapist authentication
- **controller/therapist_schedule_contr.php**: Therapist schedule and notes
- **controller/notification_contr.php**: Notification system

## File Structure Patterns
- **model/[entity]_model.php**: Database operations
- **controller/[entity]_contr.php**: Business logic and API endpoints
- **views/[user_type]/[user_type]_[page].php**: UI templates
- **views/modal/[user_type]_modal-[name].php**: Modal components