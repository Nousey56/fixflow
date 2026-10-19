# FixFlow — Multi-Tenant Maintenance Management Platform

**A full-stack property maintenance management application built with Laravel, PHP, and MySQL.**

FixFlow helps property management teams organize maintenance requests, coordinate technicians, schedule repairs, and monitor operational performance across multiple properties.

The application was developed as a software engineering portfolio project, with an emphasis on secure multi-tenant architecture, role-based authorization, database-driven workflows, automated testing, and responsive user interface design.

**Project status:** Active development — functional local application.

## Overview

Property management teams often need to coordinate repairs across different buildings, units, and technicians. Without a centralized workflow, maintenance requests can become difficult to prioritize, assign, track, and report on.

FixFlow brings these operations together in a single platform.

The application supports multiple property management companies, with company-specific access controls designed to prevent unauthorized access to another organization's information.

## Key Features

### Maintenance Management

- Create and manage maintenance work orders.
- Track work orders through open, assigned, in-progress, and completed statuses.
- Categorize requests by low, medium, high, and emergency priority.
- Search and filter work orders by relevant details.
- Record work notes and maintenance activity history.
- Require completion notes when completing repairs.

### Technician Workflow

- Dedicated technician dashboard.
- View assigned maintenance jobs.
- Start and complete assigned work.
- Track technician workload and assignments.
- Restrict unauthorized status, priority, and assignment changes.

### Property Management

- Organize properties and individual units.
- Associate maintenance requests with specific units.
- View property-level maintenance information.
- Manage property records and supported archive/restore workflows.

### Scheduling and Dispatch

- Schedule maintenance appointments.
- Reschedule existing work orders.
- Coordinate technician assignments.
- View scheduled work through a maintenance calendar.
- Monitor technician workload.

### Analytics and Reporting

- Maintenance request totals and status breakdowns.
- Work-order completion rates.
- Priority distribution.
- Six-month maintenance trends.
- Technician performance metrics.
- Property-level maintenance insights.

### Responsive Interface

- Clean, modern dashboard design.
- Mobile-responsive layouts.
- Collapsible navigation for smaller screens.
- Role-aware navigation and interface elements.

## Multi-Tenant Architecture

FixFlow is designed to support multiple property management companies within one application.

Each company can have its own properties, units, maintenance requests, and team memberships.

The core data relationships are:

```text
Company
  ├── Company Memberships
  │     └── Users and Roles
  │
  └── Properties
        └── Units
              └── Maintenance Requests
                    ├── Assigned Technician
                    └── Activity History
```

The application uses company memberships and authorization checks to control access to company-specific records.

A user can belong to more than one company, with permissions determined by their active memberships.

## Roles and Permissions

| Capability | Administrator | Manager | Technician |
|---|---|---|---|
| View authorized maintenance requests | Yes | Yes | Assigned jobs |
| Manage maintenance requests | Yes | Yes | Restricted |
| Change work-order priority | Yes | Yes | No |
| Assign technicians | Yes | Restricted | No |
| Schedule maintenance | Yes | Yes | No |
| View management analytics | Yes | Yes | No |
| Manage team memberships | Yes | Restricted | No |
| Start and complete assigned jobs | Workflow-dependent | Workflow-dependent | Yes |

*Specific permissions are enforced by backend authorization logic, not just by hiding interface controls.*

## Technology Stack

| Layer | Technology |
|---|---|
| Backend | PHP, Laravel |
| Database | MySQL |
| Frontend | Laravel Blade, HTML, CSS, JavaScript |
| ORM | Laravel Eloquent |
| Authentication | Laravel session-based authentication |
| Testing | PHPUnit / Laravel Feature Tests |
| Version Control | Git |
| Development Environment | macOS, Visual Studio Code |

## Security and Authorization

Security is a central part of FixFlow's design.

Implemented protections include:

- Authentication requirements for protected routes.
- Role-based access control using active company memberships.
- Company-scoped data access.
- Restrictions on cross-company work-order modifications.
- Technician-specific authorization rules.
- Server-side validation of maintenance updates.
- Restrictions on unauthorized technician assignments.
- Automated regression tests for access control and workflow permissions.

These controls help ensure that access restrictions remain enforced even when users submit requests directly to backend endpoints.

## Automated Testing

FixFlow includes automated feature tests covering core application behavior and security.

**Latest verified test suite:**

- **103 passing tests**
- **306 assertions**

Test coverage includes:

- Work-order creation and updates.
- Technician assignment and permissions.
- Maintenance scheduling.
- Dashboard filtering and pagination.
- Property management and archiving.
- Company data isolation.
- Technician workload reporting.
- Maintenance analytics.
- Authentication and authorization restrictions.

Run the complete test suite:

```bash
php artisan test
```

## Local Installation

### Prerequisites

- PHP compatible with the project's Laravel version
- Composer
- MySQL
- Node.js and npm
- Git

### 1. Clone the repository

```bash
git clone YOUR_REPOSITORY_URL
cd fixflow
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Install frontend dependencies

```bash
npm install
```

### 4. Configure the environment

```bash
cp .env.example .env
php artisan key:generate
```

Update `.env` with your local database credentials.

Example:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fixflow
DB_USERNAME=your_database_username
DB_PASSWORD=your_database_password
```

Create the corresponding MySQL database before running migrations.

### 5. Run database migrations

```bash
php artisan migrate
```

### 6. Build frontend assets

```bash
npm run build
```

### 7. Start the application

```bash
php artisan serve
```

Open the local address shown in your terminal.

**Note:** The project includes development and demonstration tooling. Use only fictional demonstration data, and review all environment-specific configuration before publishing or deploying.

## Screenshots

Application screenshots will be added as part of the portfolio presentation.

Planned screenshots include:

- Work Orders Dashboard
- Maintenance Request Details
- Dispatch Board
- Maintenance Calendar
- Technician Dashboard
- Analytics Dashboard
- Mobile Navigation

## Engineering Highlights

This project demonstrates practical software engineering skills in several areas.

**Backend development:** Laravel controllers, request validation, Eloquent relationships, database transactions, and REST-style application routes.

**Database design:** Relational data modeling across companies, properties, units, memberships, work orders, and maintenance activity.

**Application security:** Multi-tenant data isolation, role-based authorization, and protection against unauthorized workflow changes.

**Testing:** Automated feature tests that verify business rules, access restrictions, and regression-sensitive functionality.

**Frontend development:** Responsive layouts, reusable Blade components, management dashboards, and role-specific interfaces.

**Software development workflow:** Git version control, incremental feature development, debugging, and test-driven security improvements.

## Roadmap

Planned improvements include:

- Publicly accessible demonstration environment.
- Expanded reporting and analytics.
- Additional automated security tests.
- Continuous integration with GitHub Actions.
- Architecture and database documentation.
- Tenant-facing maintenance request portal.
- Improved notification workflows.
- Additional usability and accessibility improvements.

These are planned features and are not represented as completed functionality.

## Project Purpose

FixFlow was created to explore how a modern property maintenance platform can combine operational workflows, secure data access, and practical reporting.

It is intended both as a functional application and as a demonstration of full-stack software engineering skills.

The long-term goal is to continue developing FixFlow into a more comprehensive maintenance management solution.

## License

No