
# FixFlow — System Design

FixFlow is a multi-company maintenance management application built with Laravel, MySQL, and Blade.

## 1. Application Architecture

```mermaid
flowchart TD
    A[Desktop or Mobile Browser] --> B[Laravel Web Routes]
    B --> C[Authentication Middleware]
    C --> D[Controllers]
    D --> E[Company Membership and Role Checks]
    E --> F[Business Logic]
    F --> G[Eloquent Models]
    G --> H[(MySQL Database)]
    D --> I[Blade Views]
    I --> A
```

The application uses server-rendered pages. Laravel handles request processing, validation, authorization, and database operations.

## 2. Multi-Company Data Model

```mermaid
flowchart TD
    A[Company] --> B[Company Membership]
    B --> C[User]
    A --> D[Property]
    D --> E[Unit]
    E --> F[Maintenance Request]
    F --> G[Maintenance Activity]
    F -. Assigned to .-> C
```

Each maintenance request belongs to a unit, which belongs to a property, which belongs to a company.

Access to company data is controlled using active company memberships and role checks.

## 3. Work Order Lifecycle

```mermaid
flowchart TD
    A[Open] --> B[Assigned]
    B --> C[In Progress]
    C --> D[Completed]
    D -->|Authorized reopening| A
    D -->|Authorized reopening| C
```

This diagram illustrates common workflow transitions. The backend determines which transitions are permitted for each role.

## 4. Role-Based Access

| Capability | Administrator | Manager | Technician |
|---|---|---|---|
| View authorized work orders | Yes | Yes | Assigned jobs |
| Assign technicians | Yes | Restricted | No |
| Manage scheduling | Yes | Yes | No |
| View analytics | Yes | Yes | No |
| Complete assigned jobs | Through management workflow | Through management workflow | Yes |
| Change priority | Yes | Yes | No |

Permissions are enforced on the server, not only through the interface.

## 5. Security Architecture

```mermaid
flowchart TD
    A[Incoming Request] --> B{Authenticated?}
    B -->|No| C[Redirect to Login]
    B -->|Yes| D{Active Company Membership?}
    D -->|No| E[403 Forbidden]
    D -->|Yes| F{Authorized for Resource and Action?}
    F -->|No| E
    F -->|Yes| G[Validate Input]
    G --> H[Execute Authorized Operation]
    H --> I[Return Response]
```

## 6. Engineering Priorities

- Enforce company data isolation.
- Prevent unauthorized work order modifications.
- Maintain reliable activity history.
- Validate scheduling and completion workflows.
- Cover important behavior with automated feature tests.
- Keep the interface usable on desktop and mobile devices.

## 7. Planned Improvements

Future enhancements may include a public demo deployment, CI/CD pipeline, tenant portal, file attachments, notifications, and optimized analytics queries.

These features are planned and should not be confused with currently implemented functionality.
