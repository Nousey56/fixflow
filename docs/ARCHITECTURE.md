# FixFlow — System Architecture

**Project:** FixFlow Enterprise  
**Application Type:** Multi-tenant property maintenance management platform  
**Backend:** PHP 8.5 / Laravel 13  
**Database:** MySQL  
**Frontend:** Laravel Blade, HTML, CSS, JavaScript, Vite  
**Architecture:** Server-rendered web application with a relational database

---

## 1. Project Overview

FixFlow is a web-based maintenance management platform designed to help property management companies organize, assign, schedule, and track maintenance work orders.

The application provides separate workflows for administrators, property managers, and maintenance technicians.

Its primary engineering focus is building a maintainable, secure application that supports multiple companies while keeping their operational data isolated.

### Core Capabilities

- Multi-company account membership and role-based access
- Property and unit management
- Maintenance work order creation and tracking
- Technician assignment and dispatch
- Maintenance scheduling and calendar views
- Technician workload monitoring
- Work order activity history
- Maintenance analytics and reporting
- Responsive desktop and mobile interfaces
- Automated feature testing

---

## 2. High-Level System Architecture

FixFlow uses a Laravel monolithic architecture.

The backend handles HTTP requests, authentication, authorization, business logic, and database operations. Blade templates render the user interface.

```text
                    USER
                     |
             Desktop / Mobile
                     |
                     v
               Web Browser
                     |
              HTTP Requests
                     |
                     v
            Laravel Application
                     |
          +----------+----------+
          |                     |
          v                     v
     Authentication        Web Routes
          |                     |
          +----------+----------+
                     |
                     v
                 Controllers
                     |
          +----------+----------+
          |                     |
          v                     v
    Authorization         Business Logic
          |                     |
          +----------+----------+
                     |
                     v
             Eloquent Models
                     |
                     v
                MySQL Database
                     |
                     v
              Blade Templates
                     |
                     v
                Web Browser
```

### Main Components

**Routes**

Routes define the available application endpoints and connect HTTP requests to controller actions.

Primary route definitions are maintained in `routes/web.php`.

**Controllers**

Controllers coordinate request validation, permission checks, database operations, and response generation.

Examples include:

- `MaintenanceRequestController`
- `PropertyController`
- `DispatchController`
- `WorkOrderAssignmentController`
- `WorkOrderSchedulingController`
- `TechnicianDashboardController`
- `MaintenanceAnalyticsController`

**Models**

Eloquent models represent application data and relationships.

Key models include:

- `User`
- `Company`
- `CompanyMembership`
- `Property`
- `Unit`
- `MaintenanceRequest`

**Views**

Laravel Blade templates generate the application's user interface.

Shared components, including the FixFlow navigation bar, provide consistent navigation and styling.

---

## 3. Multi-Tenant Architecture

FixFlow is designed to support multiple property management companies within one application.

Each company maintains its own properties, units, maintenance requests, and team memberships.

The application uses a shared-database, shared-schema multi-tenant design.

### Company Relationships

```text
Company
   |
   +---- CompanyMembership
   |          |
   |          +---- User
   |
   +---- Property
              |
              +---- Unit
                       |
                       +---- MaintenanceRequest
                                  |
                                  +---- Assigned Technician
                                  |
                                  +---- Activity History
```

A company owns properties.

Properties contain units.

Units contain maintenance requests.

Users belong to companies through company memberships.

### Tenant Isolation

Maintenance requests are scoped through their related units and properties to the appropriate company.

For example:

```php
MaintenanceRequest::query()
    ->whereHas('unit.property', function ($query) use ($companyIds) {
        $query->whereIn('company_id', $companyIds);
    });
```

This approach allows authorized users to work with data belonging to their companies without exposing unrelated companies' records.

Tenant isolation must be applied consistently to both read and write operations.

### Security Considerations

The system must prevent:

- Viewing another company's maintenance requests
- Editing another company's properties
- Assigning technicians from unrelated companies
- Scheduling maintenance work for unauthorized companies
- Including another company's data in analytics

These restrictions are covered by automated feature tests.

---

## 4. Role-Based Access Control

FixFlow uses company memberships to determine user permissions.

A membership associates a user with a company and includes a role and membership status.

### Supported Roles

| Role | Responsibilities |
|---|---|
| Company Administrator | Company administration, technician assignments, property and maintenance management |
| Manager | Property operations, maintenance tracking, scheduling, dispatch, and reporting |
| Technician | Viewing assigned work orders and completing authorized technician workflows |

Only active memberships should grant access to company resources.

### Authorization Strategy

Authorization is enforced in backend controllers rather than relying exclusively on hidden interface elements.

This is important because users can manually construct HTTP requests without interacting with the application interface.

For example, a technician should not be able to change a work order's priority by submitting a modified PATCH request.

Similarly, technicians should use the dedicated job workflow to start and complete assigned work.

### Defense in Depth

FixFlow combines:

1. Authentication checks
2. Active company membership verification
3. Role-based permissions
4. Resource ownership validation
5. Server-side input validation
6. Automated security tests

---

## 5. Work Order Management

Maintenance requests represent repair or service tasks associated with property units.

### Main Attributes

A maintenance request includes information such as:

- Title
- Description
- Property and unit association
- Priority
- Status
- Assigned technician
- Work notes
- Scheduling information
- Completion information
- Creation and modification timestamps

### Work Order Statuses

```text
OPEN
 |
 v
ASSIGNED
 |
 v
IN PROGRESS
 |
 v
COMPLETED
```

This diagram represents the typical progression of a work order, not every permitted transition.

Authorized managers or administrators may perform additional management actions, such as reopening work orders.

### Priority Levels

- Low
- Medium
- High
- Emergency

Priority information helps management teams identify and organize urgent work.

### Activity Tracking

FixFlow maintains activity records associated with maintenance requests.

These records support tracking changes such as:

- Status updates
- Priority changes
- Technician assignment changes
- Work notes

Activity history provides visibility into the progression of maintenance work.

---

## 6. Technician Assignment and Dispatch

Technician assignment connects maintenance requests to individual workers.

The system checks whether the selected technician has an active membership in the appropriate company.

This helps prevent assigning maintenance work to users who do not belong to the company responsible for the property.

### Assignment Workflow

```text
Administrator
      |
      v
Select Work Order
      |
      v
Select Technician
      |
      v
Validate Company Membership
      |
      v
Update Assignment
      |
      v
Record Activity
      |
      v
Technician Views Assigned Job
```

Dispatch and workload interfaces help management teams review maintenance assignments.

---

## 7. Scheduling and Calendar

FixFlow supports scheduling maintenance appointments.

Scheduling data includes:

- Scheduled start time
- Scheduled end time
- Scheduling notes

Scheduling operations validate the requested time range.

For example, the end time must occur after the start time.

The calendar provides a scheduling-focused view of maintenance work.

Scheduling authorization is enforced on the server to prevent unauthorized users from changing appointments.

---

## 8. Technician Workflow

Technicians interact with maintenance requests through their assigned job dashboard.

The technician workflow is separated from general management operations.

### Typical Workflow

```text
Assigned Work Order
        |
        v
   Technician
        |
        v
    Start Job
        |
        v
   In Progress
        |
        v
   Complete Job
        |
        v
 Completion Notes
        |
        v
     Completed
```

Completion notes are required when a work order transitions into the completed state.

The application records completion information to support maintenance history and accountability.

Technicians cannot use the general maintenance update endpoint to bypass dedicated workflow permissions.

---

## 9. Analytics and Reporting

FixFlow includes a maintenance analytics dashboard for authorized administrators and managers.

The dashboard summarizes operational maintenance data.

### Available Metrics

- Total maintenance requests
- Open work orders
- Assigned work orders
- In-progress work orders
- Completed work orders
- Completion rate
- Unassigned work orders
- Priority distribution
- Six-month maintenance trends
- Technician performance
- Property-level maintenance insights

### Analytics Data Flow

```text
Authenticated Manager
         |
         v
Analytics Controller
         |
         v
Active Company Memberships
         |
         v
Authorized Company IDs
         |
         v
Scoped Maintenance Requests
         |
         v
Metric Calculations
         |
         v
Analytics Dashboard
```

Analytics queries use authorized company IDs to avoid mixing data between unrelated companies.

### Current Design Trade-Off

The analytics implementation retrieves authorized maintenance requests and calculates summary metrics in application memory.

This simplifies the initial implementation and is appropriate for small demonstration datasets.

For larger deployments, potential improvements include:

- Database-level aggregation
- Indexed reporting queries
- Cached analytics summaries
- Background report generation
- Historical completion-time analysis

---

## 10. Database Design

FixFlow uses MySQL as its relational database.

Laravel migrations define the database schema and track changes over time.

### Primary Data Relationships

| Entity | Relationship |
|---|---|
| Company | Has many properties |
| Company | Has many memberships |
| User | Has many company memberships |
| Property | Belongs to a company |
| Unit | Belongs to a property |
| Maintenance Request | Belongs to a unit |
| Maintenance Request | May be assigned to a technician |
| Maintenance Request | Has activity history |

The relational structure supports organizing maintenance information by company, property, and unit.

### Database Integrity

The application combines relational database constraints with application-level validation and authorization.

Database transactions are used in workflows where related changes should be processed together.

---

## 11. Frontend Architecture

FixFlow uses server-rendered Laravel Blade templates.

The interface follows a minimal, Apple-inspired visual style.

### Design Characteristics

- Light backgrounds
- Rounded cards
- Blue primary actions
- Clear status indicators
- Responsive layouts
- Shared navigation
- Role-aware interface elements

### Mobile Support

The interface adapts to smaller screens.

On mobile devices:

- Navigation uses a collapsible menu
- Dashboard cards adjust to available width
- Forms use narrower layouts
- Work order content is arranged vertically

This allows administrators, managers, and technicians to access relevant workflows from desktop or mobile browsers.

---

## 12. Testing Strategy

FixFlow uses Laravel's automated testing framework.

The project includes feature tests for important application behavior.

### Tested Areas

- Authentication and access restrictions
- Property management
- Property archiving
- Work order visibility
- Work order assignments
- Scheduling permissions
- Technician workflow
- Maintenance analytics
- Company data isolation
- Role-based security restrictions

### Test Execution

```bash
php artisan test
```

At the time of this documentation, the project has successfully completed **103 automated tests with 306 assertions**.

Tests are especially important for verifying that company and role restrictions cannot be bypassed through direct HTTP requests.

---

## 13. Development Workflow

FixFlow is developed using:

- Visual Studio Code
- PHP and Laravel
- Composer
- MySQL
- Vite
- Git

### Typical Development Process

1. Identify a feature requirement.
2. Design the relevant data relationships.
3. Implement routes and controller logic.
4. Build or update Blade views.
5. Add authorization and validation.
6. Write feature tests.
7. Run the full test suite.
8. Review changes.
9. Commit the completed work to Git.

This workflow supports incremental development and helps reduce regressions.

---

## 14. Engineering Decisions and Trade-Offs

### Laravel Monolithic Architecture

**Decision:** Use Laravel for backend logic and server-rendered views.

**Reasoning:** A monolithic architecture reduces initial infrastructure complexity while providing routing, authentication, validation, database access, and testing capabilities.

### Shared-Database Multi-Tenancy

**Decision:** Store multiple companies' data in a shared database and distinguish ownership through relationships.

**Reasoning:** This supports a simpler initial deployment and development workflow.

**Trade-off:** Every company-scoped operation must enforce tenant isolation correctly.

### Dedicated Technician Workflow

**Decision:** Separate technician job actions from general management updates.

**Reasoning:** This creates clearer permission boundaries and reduces opportunities for unauthorized status changes.

### Automated Security Testing

**Decision:** Include tests for role restrictions and cross-company access.

**Reasoning:** Multi-tenant applications require verification that users cannot access or modify resources outside their permissions.

---

## 15. Future Improvements

Potential future development includes:

### Platform Features

- Tenant-facing maintenance request portal
- File and image attachments
- Email and in-app notifications
- Maintenance request comments
- Preventive maintenance scheduling
- Equipment and asset management
- Vendor and contractor management

### Engineering Improvements

- Public demo deployment
- Continuous integration with GitHub Actions
- Automated deployment pipeline
- Database query optimization
- Expanded authorization policies
- API endpoints for future mobile applications
- Audit logging improvements
- Performance and load testing

### Advanced Features

- AI-assisted maintenance request categorization
- Suggested technician assignments
- Predictive maintenance analytics
- Automated maintenance summaries

These are planned enhancements, not current production capabilities.

---

## 16. Portfolio Significance

FixFlow demonstrates experience with several practical software engineering concepts:

**Backend Engineering:** Laravel controllers, validation, relational models, database transactions, and business logic.

**Database Design:** MySQL schema design, migrations, entity relationships, and tenant-scoped queries.

**Application Security:** Authentication, role-based authorization, active membership checks, and company data isolation.

**Full-Stack Development:** Server-rendered interfaces, responsive layouts, forms, dashboards, and workflow integration.

**Software Quality:** Automated feature tests, regression testing, and Git-based version control.

**Product Thinking:** Designing software around real maintenance operations and the needs of property management teams.

---

## Project Status

**Status:** Active development — portfolio project

FixFlow is a working application under continued development. It is not presented as a production-certified or independently security-audited platform.

The next development priorities are deployment readiness, technical documentation, and additional portfolio-quality engineering improvements.