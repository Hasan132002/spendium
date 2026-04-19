# Spendium — Design Document

Family Finance Management Web Portal

Version 1.2.0
Last updated: 19 April 2026

---

## 1. Executive Summary

Spendium is a multi-role family finance management web portal. A single household — the "family" — is organised around one or two heads (a father or a mother) who create the family account and invite the remaining members. Each member logs in to the same web portal and is presented with a tailored view derived from their role and explicitly granted permissions.

The portal tracks monthly budgets, per-member expense allocations, fund requests, external loans with repayments and member contributions, individual savings with end-of-month rollover into savings, family and personal goals with progress tracking, and a lightweight social feed where members can share posts, comment, react, and follow one another.

The primary delivery target is the web portal. An Android API layer exists in the codebase but is not the current focus.

---

## 2. System Overview

### 2.1 Problem Domain

A typical family distributes financial responsibility across multiple people. One person issues an overall monthly budget. Others contribute to expenses, request additional funds for unplanned costs, track shared loans, save toward common goals, and need visibility into what has been spent and what remains. Doing this across WhatsApp messages, notebooks, and mental arithmetic is error-prone. Spendium replaces that ad-hoc coordination with a single source of truth.

### 2.2 Core Concepts

**Family.** A logical group of users identified by a single `families` row. It has exactly one head, stored in `families.father_id`. The column name is historical; a mother self-registering also becomes the head and occupies this column.

**Member.** A row in `family_members` linking a user to a family with a domain role of father, mother, or child, and a status of pending, accepted, or rejected. Only accepted members are treated as part of the family by application queries.

**Head.** The member who created the family. The head has full family-wide visibility and can invite, remove, and modify other members.

**Invitation.** A pending request for someone to join the family, identified by a one-time token sent over email. Invitations carry an optional set of per-member family permissions chosen by the head at invite time.

### 2.3 Technology Stack

| Layer | Technology |
|-------|------------|
| Language / Framework | PHP 8.2, Laravel 12 |
| Database | MySQL 8.x |
| Frontend | Blade templates, Tailwind CSS v4, Alpine.js, Flowbite |
| Charting / UI | ApexCharts, FullCalendar, Dropzone |
| Authorization | spatie/laravel-permission (roles and permissions) |
| Web auth | Laravel UI (session-based) |
| API auth | tymon/jwt-auth (parked; not actively developed) |
| Modularity | nwidart/laravel-modules |
| Monitoring | Laravel Pulse, in-app Action Logs |
| Build tooling | Vite 6 |

---

## 3. Architecture

### 3.1 Application Layers

The application follows a standard Laravel layered structure. HTTP requests are handled by controllers under `app/Http/Controllers/Backend` for admin views and `app/Http/Controllers/API` for JSON endpoints. Business logic is organised into service classes under `app/Services`. Models under `app/Models` map to database tables and encapsulate relationships and cast rules.

The admin view layer is composed of Blade templates under `resources/views/backend/pages` and `resources/views/dashboard`, sharing a common layout under `resources/views/backend/layouts`.

### 3.2 Permission Enforcement

Authorization is enforced at three levels. First, the `auth` middleware on admin route groups prevents unauthenticated access. Second, each controller method calls `checkAuthorization(auth()->user(), ['permission.name'])` from the `AuthorizationChecker` trait to guard the action. Third, Blade templates use `@can('permission.name')` or the equivalent `$user->can(...)` directive to hide navigation items and action buttons the user cannot use.

### 3.3 Extensibility

The codebase integrates a WordPress-style hook system via `tormjens/eventy`, exposed through the `ld_do_action()` and `ld_apply_filters()` helpers defined in `app/Helper/hooks.php`. Modules under `Modules/` can register filters to mutate behaviour without editing core files. The module system is powered by `nwidart/laravel-modules` and is controlled from the admin panel at `/admin/modules` with state persisted in `modules_statuses.json`.

---

## 4. User Roles and Access Control

Spendium distinguishes between two orthogonal role concepts.

### 4.1 System Roles (Spatie)

System roles are defined in `app/Services/RolesService.php` and seeded by `RolePermissionSeeder`. They control the set of permissions a user has available.

| Role | Typical Assignee | Scope |
|------|------------------|-------|
| Superadmin | System operator | Full access across all features and all families |
| Family Head | User who self-registers as father or mother | Full family management and family-level financial operations |
| Family Member | User who accepts an invitation | Personal screens by default; additional family permissions granted per invitation |
| Admin / Editor / Subscriber | Legacy tier from the underlying dashboard template | Retained for backwards compatibility; random assignment to factory users during seeding |

### 4.2 Domain Roles (Family Role)

The `family_members.role` column stores the domain role (father, mother, or child) used by business logic — for example, to decide whether a member can create family budgets or must instead submit fund requests.

| Role | Business Semantics |
|------|--------------------|
| father | Family head; owns the `father_id` slot on the family |
| mother | Second head or regular member depending on registration path |
| child | Dependent member; can only join through an invitation |

### 4.3 Per-Member Permission Overrides

When a head invites a member, the invitation form lets the head tick any subset of `family.*` permissions that the invited member should receive on acceptance. These are stored as JSON on the invitation row and applied to the new user via `syncPermissions()` when the invitation is accepted. The head can subsequently amend these permissions through the Edit Member screen.

---

## 5. Authentication and Registration

### 5.1 Login

The login form is served at `/admin/login`. It accepts either an email address or a username, along with a password. On success the user is redirected to `/admin`, the main dashboard. In the local environment the form is pre-filled with `superadmin@example.com` / `12345678` to speed up development.

### 5.2 Self-Registration

The public registration form at `/register` requires the user's name, email, chosen role (restricted to father or mother), family name, and password with confirmation. Submission opens a database transaction that creates the user record, creates the corresponding family with `father_id` set to the newly created user, creates an accepted `family_members` row, and assigns the Spatie `Family Head` role. The user is then redirected to `/admin`.

Registering as a child is not permitted from the public form. Children can only join a family through an invitation issued by an existing head.

### 5.3 Invitation and Acceptance

An invitation is a record in `family_member_invitations` carrying the target email, the chosen family role, a 64-character token, an optional set of family permissions, the inviter's user id, and a 7-day expiry. Sending an invitation dispatches the `FamilyInvitation` mailable with a link of the form `/family/invite/{token}`.

The acceptance URL is public. Opening it validates that the token exists, has not yet been accepted, and has not expired. If the invitation email already corresponds to a registered user, the acceptance form asks the user to confirm their existing password; on success the invitation is linked to the existing account. Otherwise the form requests a name and password to create a new account.

On submission the system creates a `family_members` row with status accepted, assigns the Spatie `Family Member` role, applies the invitation's permission set, stamps `accepted_at`, authenticates the user, and redirects to `/admin`.

### 5.4 Password Reset

The application uses Laravel's standard password reset flow, wired at `/admin/password/reset`. A reset link is sent by email and the user can set a new password through the standard form.

---

## 6. Functional Modules

Each subsection below describes one module of the application: its purpose, who can access it, the primary screens, representative scenarios, and the files that implement it.

### 6.1 Family Management

The Family Management module allows a head to view and curate their family's membership.

Access is gated by the `family.member.invite`, `family.member.edit`, and `family.member.remove` permissions. A head holds all three by virtue of the `Family Head` role.

The landing screen at `/admin/family/members` presents two tables: the active members with their role and status, and the invitations with their state (pending, accepted, or expired).

From this screen the head can invite a new member through `/admin/family/members/invite`. The form collects the invitee's name, email, intended family role, and a checklist of `family.*` permissions. Submission persists the invitation and emails the token link.

The head can also edit a member at `/admin/family/members/{id}/edit`, change their role between mother and child, and toggle permissions. Removing a member from `/admin/family/members` deletes the `family_members` row but preserves the underlying user account, allowing the user to be re-invited or to join a different family later. Pending invitations can be resent, which regenerates the token and resets the expiry, or revoked, which deletes the row and invalidates the link.

The implementation lives in `app/Http/Controllers/Backend/FamilyMemberController.php`, `app/Http/Controllers/Auth/FamilyInviteController.php`, `app/Models/FamilyMemberInvitation.php`, `app/Mail/FamilyInvitation.php`, and the Blade templates under `resources/views/backend/pages/family`.

### 6.2 Categories

Categories classify expenses, budgets, and fund requests. Two kinds are supported: default categories with a null `user_id` and `family_id`, available to all users, and custom categories owned by a particular user within a family.

The default set seeded by `CategorySeeder` includes Groceries, Utilities, Rent, Transport, Education, Medical, Entertainment, and Savings. Every family also receives three family-scoped customs (Eid Shopping, Birthday Fund, Emergency) to demonstrate the pattern.

All authenticated users can view categories at `/admin/categories/all`. The admin panel presents default and custom categories in separate tables. The API endpoint at `GET /api/categories` mirrors this, returning the same data under the keys `default` and `custom`.

Implementation: `app/Models/Category.php`, `App\Http\Controllers\Backend\AllAppController::Categories`, `App\Http\Controllers\API\CategoryController`, and view `resources/views/dashboard/categories.blade.php`.

### 6.3 Budgets

The Budgets module is the financial spine of the application. It supports two budget types: a single monthly family budget acting as the overall pool, and any number of assigned per-member budgets carved out of the pool.

The `budgets` table stores `family_id`, `user_id` (null for family-level), `category_id`, `amount` (the current remaining balance), `initial_amount`, `type` (either `family` or `assigned`), and `month` in `YYYY-MM` format. Every monetary change is logged in `budget_transactions` with an action of add or deduct and a source such as `top_up`, `assign_to_member`, `assigned`, `expense`, `fund_request`, `manual_saving`, or `rollover`.

The Family Budget screen at `/admin/budget/family` is visible to the head and shows the pool for each month. Creating a family budget emits a matching `top_up` transaction of the initial amount.

The Assigned Budgets screen at `/admin/budget/assigned` lists per-member sub-budgets. When the head assigns an amount to a member, the system deducts the amount from the pool (recording a `deduct` transaction with source `assign_to_member`), creates the member budget with the assigned amount and category, and records a complementary `add` transaction on the member budget with source `assigned`.

Implementation: `app/Models/Budget.php`, `app/Models/BudgetTransaction.php`, `App\Http\Controllers\Backend\AllAppController::familyBudget` and `assignedBudgets`, and `App\Http\Controllers\API\BudgetController`.

### 6.4 Expenses

Members record expenses against their assigned budgets.

The member-facing screen at `/admin/expenses/my` lists the user's own expenses. The head can view all expenses across the family at `/admin/expenses/family`, provided the head holds the `family.expense.view` permission.

When an expense is logged, the system verifies that the target budget belongs to the user (or, for family-level budgets, that the user is a member of the family) and that the requested amount does not exceed the remaining balance. On success the budget's remaining amount is decremented, the expense row is inserted with `approved` initially false, and a matching `deduct` budget transaction with source `expense` is recorded.

The head can approve expenses, flipping the `approved` flag. Approval is restricted to members of the head's own family.

Implementation: `app/Models/Expense.php`, `App\Http\Controllers\Backend\AllAppController::MyExpenses` and `FamilyExpenses`, `App\Http\Controllers\API\ExpenseController`.

### 6.5 Fund Requests

Fund requests let a non-head member request additional funds when their assigned budget is insufficient.

The requester chooses a category, specifies an amount and an optional note, and submits. The request is stored with status pending and is visible to the requester at `/admin/fund-request/my` and to the head at `/admin/fund-request/funds/all`.

The head can approve or decline. Approval with an optional amount override performs the following transaction atomically: deducts the approved amount from the main family budget for the matching category, updates the request to status approved with the final amount, creates a new assigned budget for the requester, creates an immediate pre-approved expense row, and records the corresponding `deduct` and `add` budget transactions. Declining simply sets the request status to rejected.

Implementation: `app/Models/FundRequest.php`, `App\Http\Controllers\Backend\AllAppController::MyRequests` and `FamilyRequests`, `App\Http\Controllers\API\FundRequestController`.

### 6.6 Loan Management

The Loan Management module tracks external loans taken by the family and distributes repayment responsibility across members.

Loan categories, stored in `loan_categories`, come in two flavours: globally available categories (with null `user_id` and `family_id`) such as Home, Vehicle, Business, Education, Medical, and Personal, and family-specific categories such as Family Internal. Categories are visible at `/admin/loan-categories`.

A loan, stored in `loans`, carries the lending family, its category, the lender's name, the principal amount, the current remaining amount, a status (pending, partially_paid, or paid), a due date, and a purpose string. Loans are listed at `/admin/loans` and each loan has a detail view at `/admin/loans/{id}` showing repayments and contributions.

Repayments are recorded in `loan_repayments` against a specific loan with an amount, date, and note, and shown at `/admin/loan-repayments/loan/{loan_id}`.

Member contributions toward repaying a shared loan are captured in `loan_contributions`. A member posts a contribution with an amount and a note; the head reviews it and moves the status from pending to approved or rejected. The member-facing contributions screen lives at `/admin/loan-contributions/my`.

Implementation: `app/Models/Loan.php`, `LoanCategory.php`, `LoanRepayment.php`, `LoanContribution.php`, `App\Http\Controllers\Backend\AllAppController` (`LoanCategories`, `Loans`, `Loan`, `LoanRepayments`, `MyContributions`), and the corresponding API controllers.

### 6.7 Savings

Each user has exactly one savings account represented by a row in `savings` with a cumulative `total` balance. Every change to that total is journaled in `savings_transactions` with a type of add, deduct, or transfer_to_goal.

The My Savings screen at `/admin/savings/my` shows the current balance and the transaction history. A standalone history view is available at `/admin/savings/history`.

End-of-month rollover, visible at `/admin/savings/end-of-month`, computes each unused amount for the current user's budgets in the current month. For every budget, it subtracts the sum of `deduct` transactions from the budget amount; if the remainder is positive it is added to the running total. When the total exceeds zero the system credits the user's savings account with that total and records an add transaction with source `rollover`.

Implementation: `app/Models/Saving.php`, `app/Models/SavingsTransaction.php`, `App\Http\Controllers\Backend\AllAppController::Savings`, `SavingsHistory`, `endOfMonthRolloverView`, and `App\Http\Controllers\API\SavingsController`.

### 6.8 Goals

The Goals module lets the family and its individual members save toward identified targets.

Goals come in two types: family goals, visible to the whole family, and personal goals, specific to a single user. Each goal records a target amount and a saved amount, along with a status such as active or completed.

Members contribute to goals through `goal_contributions`. The effective amount collected toward a goal is computed at read time as the sum of its contributions. The progress percentage is calculated as `collected_amount / target_amount * 100`, rounded to two decimal places.

The main screens are `/admin/goals/family` for family-wide goals, `/admin/goals/personal` for the user's own goals (or, for a head, all members' personal goals), and `/admin/goals/{id}/progress` for the progress breakdown of a specific goal.

Transferring savings into a goal deducts from the user's savings balance, creates a `goal_contributions` row, and records a matching `savings_transactions` entry with type deduct and source goal.

Implementation: `app/Models/Goal.php`, `app/Models/GoalContribution.php`, `App\Http\Controllers\Backend\AllAppController::FamilyGoals`, `MyGoals`, `GoalProgress`, and `App\Http\Controllers\API\GoalController`.

### 6.9 Social Layer

The social layer gives the family and wider user base a lightweight feed where members can document financial milestones, share tips, and celebrate goal achievements.

Posts are stored in `posts` with a title, description, and optional photo. Comments in `comments` belong to a post and a user. Reactions are polymorphic: a `reactions` row carries `reactable_id` and `reactable_type` and can point at either a post or a comment. A unique composite key prevents the same user from reacting more than once to the same item. Reactions support like, love, and wow.

The feed screens are `/admin/posts` for the global feed, `/admin/my-posts` for the current user, `/admin/posts/{id}` for a single post and its comments, and `/admin/posts/{id}/comments` for a flat comments list. For each post the controller precomputes a comment count, a like count, and a reaction count per comment.

The follow graph is stored in `follows` with a `follower_id` and `following_id` pair constrained to be unique. Screens include `/admin/users/{id}/followers`, `/admin/users/{id}/followings`, and `/admin/users/{id}/profile-stats`, the latter summarising post count, follower count, following count, and upload count.

Implementation: `app/Http/Controllers/PostController.php` (shared by web and API), `CommentController.php`, `ReactionController.php`, `FollowController.php`, and the Blade templates under `resources/views/dashboard`.

### 6.10 Profile

Users can edit their own profile at `/profile/edit` and view a dashboard-integrated version at `/admin/auth/profile`. Editable fields include name, email, username, password, and avatar. In demo mode, modifying the superadmin account is blocked at the controller level as a safety measure.

### 6.11 User and Role Administration

User administration is available to the superadmin only. The screens at `/admin/users`, `/admin/users/create`, and `/admin/users/{id}/edit` provide standard CRUD over user accounts, including the assignment of Spatie roles through a checkbox list. A login-as feature at `/admin/users/{id}/login-as` lets the superadmin impersonate any other user for support purposes; `POST /admin/users/switch-back` returns to the original session.

Role administration lives at `/admin/roles`, `/admin/roles/create`, and `/admin/roles/{id}/edit`. Custom roles can be created from the union of all defined permissions. Out of the box, the application ships with the roles Superadmin, Admin, Editor, Subscriber, Family Head, and Family Member, each with a predefined permission set documented in `RolesService::createPredefinedRoles`.

### 6.12 Settings, Translations, and Modules

General application settings such as the display name, currency symbol, and sidebar colour tokens are editable at `/admin/settings` and persisted to the `settings` table as key/value pairs.

Multi-language support is managed at `/admin/translations`. The underlying JSON files live under `resources/lang` and can be edited inline. End users can switch language through `/locale/{lang}`.

The module manager at `/admin/modules` uses `nwidart/laravel-modules` to list, enable, disable, and upload self-contained modules placed under `Modules/`. The current state is reflected in `modules_statuses.json` at the project root; the default distribution enables UserAvatar and leaves Crm disabled.

### 6.13 Monitoring

The Action Logs view at `/action-log` lists every administrative event captured through the `HasActionLogTrait`. Each row records the actor, the event type, a human-readable title, and a JSON payload describing the entity that changed.

Laravel Pulse is mounted at `/admin/pulse` and opens in a new tab. It surfaces request latency, slow queries, exceptions, and active sessions, and is the recommended first stop when investigating performance issues.

### 6.14 AI Assistant

The AI assistant at `/admin/ai/chat` is an optional, experimental feature. It expects an external FastAPI service running at `http://127.0.0.1:8001` that performs retrieval-augmented generation over indexed content. Chat history is persisted in `ai_chats` per user, and indexed embeddings are stored in `ai_embeddings`. The endpoint `POST /api/store-embedding` accepts new content to index. If the FastAPI service is not running, the chat UI will return an error without affecting other areas of the application.

---

## 7. Permission Matrix

The following matrix summarises which permissions each role holds out of the box. Entries marked optional indicate a permission that a head can grant to a specific family member at invitation time or through the Edit Member screen.

| Permission | Superadmin | Family Head | Family Member |
|------------|:----------:|:-----------:|:-------------:|
| dashboard.view | yes | yes | yes |
| user.create, view, edit, delete | yes | no | no |
| role.create, view, edit, delete | yes | no | no |
| settings.view, edit | yes | no | no |
| translations.view, edit | yes | no | no |
| family.budget.view, create, assign | yes | yes | optional |
| family.expense.view, approve | yes | yes | optional |
| family.fund_request.view, approve | yes | yes | optional |
| family.goal.view, manage | yes | yes | optional |
| family.loan.manage | yes | yes | optional |
| family.member.invite, remove, edit | yes | yes | optional |
| personal.expense.manage | yes | yes | yes |
| personal.fund_request.create | yes | yes | yes |
| personal.savings.manage | yes | yes | yes |
| personal.goal.manage | yes | yes | yes |
| personal.loan.contribute | yes | yes | yes |
| personal.post.manage | yes | yes | yes |
| personal.profile.view | yes | yes | yes |

---

## 8. Key Calculations

The following calculations govern how balances and progress values are derived. Where a value is stored as a column it represents the authoritative current state; transactional tables act as an immutable audit log.

Budget remaining balance is read directly from `budgets.amount`. This column is decremented whenever an expense is logged against the budget or when the budget is spent toward a fund request. The complete change history is available in `budget_transactions`.

Budget used amount for the purpose of end-of-month rollover is derived on the fly as the sum of amounts on `budget_transactions` rows with action `deduct` scoped to that budget.

Savings balance is read from `savings.total`. Each adjustment is persisted with a matching `savings_transactions` row.

Goal collected amount is computed at read time as the sum of `goal_contributions.amount` for that goal. The `goals.saved_amount` column is also maintained when contributing through the standard flow, but the authoritative display value is the sum of contributions.

Goal progress percentage is `collected_amount divided by target_amount, multiplied by 100`, rounded to two decimal places. When the target amount is zero the progress is reported as zero.

Loan remaining amount is stored in `loans.remaining_amount` and is updated explicitly by the loan workflow whenever a repayment is recorded.

Profile statistics are computed at request time. Post count is the number of rows in `posts` for the user, follower count is the count of `follows` rows with matching `following_id`, following count is the count of rows with matching `follower_id`, and upload count is derived from the number of posts carrying a photo.

---

## 9. Database Design

### 9.1 Authentication

The `users` table holds the primary identity with fields for name, email, username, password, domain role string (father, mother, child, or null for non-family users), and image. Spatie role and permission assignments are stored in the standard `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, and `role_has_permissions` tables.

### 9.2 Family

The `families` table carries the family's display name and the head's user id in `father_id`. The `family_members` table links users to families with a domain role (father, mother, or child) and a status enum (pending, accepted, or rejected). The `family_member_invitations` table records pending invitations with fields for the target email, role, a unique token, the JSON permission set to apply on acceptance, the inviter's user id, an expiry timestamp, and an acceptance timestamp.

### 9.3 Finance

Categories are stored in `categories` with optional `user_id` and `family_id`. Budgets are stored in `budgets` with a family id, optional user id, category id, current and initial amounts, type (family or assigned), and a `month` column in `YYYY-MM` format. The legacy `category` string column is retained as nullable for compatibility.

Budget changes are journalled in `budget_transactions` with a budget id, an optional actor user id, an action (add or deduct), an amount, a source string, and an optional source id pointing at the originating record.

Expenses are stored in `expenses` with a user id, a budget id, a category id, a title, an amount, an optional note, a date, and an approved flag. Fund requests follow a parallel structure in `fund_requests`.

### 9.4 Loans

The `loan_categories` table stores the category name with optional user and family scoping. The `loans` table records a family id, a category id, a lender, the amount, the current remaining amount, a status enum (pending, partially_paid, or paid), a due date, and a purpose string. Repayments live in `loan_repayments` with a loan id, amount, date, and note. Contributions live in `loan_contributions` with a loan id, user id, amount, note, and status.

### 9.5 Savings and Goals

The `savings` table stores one row per user with the current total balance. The `savings_transactions` table records every change with a type of add, deduct, or transfer_to_goal, an amount, and an optional note.

The `goals` table stores a family id, an optional user id, title, target amount, saved amount, type (family or personal), and status. Contributions to goals are stored in `goal_contributions`.

### 9.6 Social Layer

The `posts` table stores user-authored posts with a title, description, and optional photo path. The `comments` table stores comments linked to a post and a user. The `reactions` table stores polymorphic reactions using `reactable_id` and `reactable_type`, with a unique composite key preventing duplicate reactions. The `follows` table records follower / followed pairs with a unique constraint.

### 9.7 System

The `action_logs` table captures audit events with the actor id, an event type, a title, and a JSON payload. The `settings` table stores application-level key / value preferences. The `ai_chats` and `ai_embeddings` tables persist AI assistant state when the feature is used.

---

## 10. Routes Reference

The following tables list the routes exposed by the web portal. API routes are intentionally omitted from this document because the API layer is currently parked.

### 10.1 Public Routes

| Method | URL | Purpose |
|--------|-----|---------|
| GET | /register | Show the self-registration form |
| POST | /register | Submit a registration |
| GET | /admin/login | Show the login form |
| POST | /admin/login/submit | Submit credentials |
| GET | /family/invite/{token} | Show the invitation acceptance form |
| POST | /family/invite/{token} | Accept an invitation |
| GET | /locale/{lang} | Switch the active language |

### 10.2 Family Management

| Method | URL | Purpose |
|--------|-----|---------|
| GET | /admin/family/members | List members and invitations |
| GET | /admin/family/members/invite | Show the invitation form |
| POST | /admin/family/members/invite | Send an invitation |
| GET | /admin/family/members/{id}/edit | Show the edit form |
| PUT | /admin/family/members/{id} | Update role and permissions |
| DELETE | /admin/family/members/{id} | Remove a member |
| POST | /admin/family/invitations/{id}/resend | Resend an invitation |
| DELETE | /admin/family/invitations/{id} | Revoke an invitation |

### 10.3 Financial Modules

| Method | URL | Purpose |
|--------|-----|---------|
| GET | /admin/budget/family | Family budget overview |
| GET | /admin/budget/assigned | Assigned per-member budgets |
| GET | /admin/expenses/my | Current user's expenses |
| GET | /admin/expenses/family | Family-wide expenses |
| GET | /admin/categories/all | Categories (default and custom) |
| GET | /admin/fund-request/my | Current user's fund requests |
| GET | /admin/fund-request/funds/all | All family fund requests |
| GET | /admin/loan-categories | Loan categories |
| GET | /admin/loans | Loan list |
| GET | /admin/loans/{id} | Loan detail |
| GET | /admin/loan-repayments/loan/{id} | Repayments for a loan |
| GET | /admin/loan-contributions/my | Current user's contributions |
| GET | /admin/savings/my | Savings account |
| GET | /admin/savings/history | Savings transaction history |
| GET | /admin/savings/end-of-month | End-of-month rollover view |
| GET | /admin/goals/family | Family goals |
| GET | /admin/goals/personal | Personal goals |
| GET | /admin/goals/{id}/progress | Goal progress |

### 10.4 Social Layer

| Method | URL | Purpose |
|--------|-----|---------|
| GET | /admin/posts | Global feed |
| GET | /admin/my-posts | Current user's posts |
| GET | /admin/posts/{id} | Post detail and comments |
| GET | /admin/posts/{id}/comments | Comments list |
| GET | /admin/users/{id}/followers | Followers list |
| GET | /admin/users/{id}/followings | Following list |
| GET | /admin/users/{id}/profile-stats | Profile statistics |

### 10.5 System Administration

| Method | URL | Purpose |
|--------|-----|---------|
| GET | /admin/users | Users list |
| GET | /admin/roles | Roles list |
| GET | /admin/settings | Application settings |
| GET | /admin/translations | Language translations |
| GET | /admin/modules | Module management |
| GET | /action-log | Action log viewer |

---

## 11. End-to-End Scenarios

This section walks through representative workflows to illustrate how the modules fit together.

### 11.1 Creating a Family

A new user visits `/register`, supplies a name, an email, selects the role of father, provides a family name such as "Khan Family", and sets a password. On submission the system creates the user, the family with the user as head, an accepted `family_members` row, and assigns the Family Head Spatie role. The user is redirected to `/admin` and sees the full head view including the Family Management entry in the sidebar.

### 11.2 Inviting a Member

The head opens `/admin/family/members` and clicks Invite New Member. On the invitation form the head enters the invitee's email, selects role mother, and ticks `family.expense.view`, `family.fund_request.view`, and `family.goal.view`. Submitting the form persists the invitation, dispatches an email, and returns the head to the members list where the invitation appears as Pending.

The invitee opens the email, clicks the acceptance link, provides a name and password, and submits. A new user is created, a `family_members` row is added with status accepted, the Family Member Spatie role is assigned, and the three selected permissions are synced. The invitation is marked accepted. The new user is authenticated and redirected to `/admin` with a filtered navigation reflecting the granted permissions.

### 11.3 A Monthly Financial Cycle

On the first day of October the head creates a family budget of 50,000. On day two the head assigns 8,000 to the mother against the Groceries category; the pool decreases to 42,000 and the mother sees her 8,000 Groceries budget.

On day five the mother logs a 1,500 expense for weekly groceries. Her Groceries budget decreases to 6,500 and a corresponding budget transaction is recorded. On day twelve the mother submits a fund request for 2,000 for an unexpected medical purchase. The head approves the request, which triggers an atomic workflow: the family budget is reduced by 2,000, a new 2,000 Medical assigned budget is created for the mother, an approved expense is written, and supporting budget transactions are recorded.

On day fifteen the head creates the family goal "Hajj 2027" with a target of 500,000. Across the month family members contribute to the goal from their savings balances.

On day thirty-one the mother opens the end-of-month rollover view. The system identifies that 4,500 of her assigned budgets remain unused and credits her savings with that amount, recording the matching savings transaction.

### 11.4 Loan Lifecycle

The head records a 100,000 loan from HBL Bank with a due date one year later. As the loan ages, the head records repayments against it and the status transitions from pending to partially paid. Family members post contributions in `loan_contributions`; the head reviews each and marks them approved. When the remaining balance reaches zero the head sets the status to paid.

### 11.5 Promoting a Member

The head decides to elevate the mother to full family administrator. The head opens `/admin/family/members`, edits the member, and selects all `family.*` permissions. On save the Spatie permissions on the mother are synced. The next time she logs in, her sidebar reflects the new privileges.

---

## 12. Deployment and Setup

### 12.1 Fresh Installation

Install PHP dependencies with `composer install` and JavaScript dependencies with `npm install`. Copy `.env.example` to `.env` if needed and run `php artisan key:generate` to populate the application key. Configure the database connection in `.env` and ensure the target database exists.

Run `php artisan migrate:fresh --seed` to create the schema and populate it with the default demo data, including the superadmin account, the four seeded families, the complete financial history, and the social content.

Build the frontend with `npm run dev` during development or `npm run build` for production. Start the local server with `php artisan serve`. The application will be available at `http://127.0.0.1:8000`.

### 12.2 Development Mail

For development, setting `MAIL_MAILER=log` in `.env` routes invitation emails to `storage/logs/laravel.log` so the token URL can be copied directly from the log file without configuring an SMTP relay.

### 12.3 Re-Seeding

The role seeder uses `Role::create`, which raises a unique constraint violation if a role already exists. The cleanest way to refresh the full dataset is `php artisan migrate:fresh --seed`. Partial re-seeding against an existing database requires truncating the `roles` and `permissions` tables first.

### 12.4 Default Credentials

After seeding, the superadmin account is `superadmin@example.com` with password `12345678`. The mother of the Khan Family is `subscriber@example.com` with the same password. Both accounts are intended for local development only and should be replaced or removed before deployment.

---

## 13. Known Constraints

The `families.father_id` column is used regardless of whether the head is a father or a mother; the name is historical and has not been migrated. Renaming the column to `head_id` would clarify the schema but is deferred.

A number of tables evolved after their initial migrations — for example, `budgets.month`, `family_members.status`, `expenses.category_id`, and `fund_requests.category_id` were added in follow-up migrations rather than in the original `create` migration. Fresh installs handle this transparently; long-running development databases must run every migration in order.

The sidebar's Finance and Budget submenu does not yet gate individual items by permission. Members without head-level permissions will see the links but will be redirected with an error message if they open a page that requires permissions they do not hold. Adding `@can` guards to each `<li>` is a small polish task.

The head cannot yet transfer headship to another family member. The current flow blocks the head from removing themselves through the UI; a full transfer-ownership workflow is out of scope for the current release.

The Android API remains in the repository under `app/Http/Controllers/API` and `routes/api.php`. It is not actively maintained alongside the web portal, and the two surfaces may diverge over time.

---

## 14. File Organisation

The codebase follows the standard Laravel directory layout. The most relevant locations are listed below.

Application code lives under `app/Http/Controllers` with `Auth` for registration and invitation flows, `Backend` for authenticated admin views, and `API` for the parked JSON surface. Shared social controllers such as `PostController`, `CommentController`, `ReactionController`, and `FollowController` sit at the top level of `Controllers`.

Domain models reside in `app/Models`. Service objects such as `PermissionService`, `RolesService`, and `UserService` live in `app/Services`. Reusable cross-cutting behaviour is implemented in `app/Traits`, particularly `AuthorizationChecker` and `HasActionLogTrait`. Helper functions including the hook system are in `app/Helper`.

Database migrations are under `database/migrations`. Seeders are under `database/seeders`; `DatabaseSeeder` orchestrates them in dependency order, covering users, roles, settings, families, categories, budgets, expenses, fund requests, loan categories, loans, goals, savings, posts, follows, and action logs.

Views live under `resources/views`. Authentication views including `register.blade.php` and `family-invite.blade.php` are under `auth`. The invitation email template is at `emails/family_invitation.blade.php`. The admin shell and layouts live under `backend/layouts`; domain pages such as the family management screens, user management, and role management live under `backend/pages`. The dashboard and family-facing financial pages are under `dashboard`.

Routes are defined in three files. `routes/web.php` holds the admin group with the new `admin.family.*` routes and the public invitation acceptance route. `routes/auth.php` holds the standard authentication and password reset routes. `routes/api.php` defines the JWT-protected API routes for the parked mobile layer.

Configuration sits under `config`, with `permission.php` for Spatie, `jwt.php` for the API, `modules.php` for the module manager, and the usual Laravel defaults for app, auth, database, mail, cache, queue, and filesystem.

---

End of document.





Named users (password = 12345678 sab ke liye)
Superadmin

Email	Name	Role
superadmin@spendium.com	Super Admin	System
Family 1 — Khan Family (father-headed, 4 members)

Email	Name	Family role
father@spendium.com	Father Khan	father (head)
mother@spendium.com	Mother Khan	mother
child1@spendium.com	Ahmed Khan	child
child2@spendium.com	Sara Khan	child
Family 2 — Ali Family (3 members)

Email	Name	Family role
ali@spendium.com	Ali Ahmed	father (head)
fatima@spendium.com	Fatima Ali	mother
hasan@spendium.com	Hasan Ali	child
Family 3 — Siddiqui Family (single-mother household)

Email	Name	Family role
zara@spendium.com	Zara Siddiqui	mother (head)
ayesha@spendium.com	Ayesha Siddiqui	child
omar@spendium.com	Omar Siddiqui	child
Family 4 — Farhan Ali Family (large, 4 members, high earners)

Email	Name	Family role
farhanali@spendium.com	Farhan Ali	father (head)
nadia@spendium.com	Nadia Farhan	mother
zain@spendium.com	Zain Farhan	child
maryam@spendium.com	Maryam Farhan	child
Family 5 — Hassan Family (newer, 3 members)

Email	Name	Family role
bilal@spendium.com	Bilal Hassan	father (head)
sana@spendium.com	Sana Bilal	mother
adeel@spendium.com	Adeel Hassan	child
