@extends('backend.layouts.app')

@section('title', 'Access Management (RBAC)')

@push('styles')
<style>
    /* =========================================================
       PREMIUM ULTRA-MODERN ACCESS MANAGEMENT (SAAS UI / UX)
       ========================================================= */
    :root {
        --am-bg: #090d16;
        --am-card: #0f172a;
        --am-card-inner: #1e293b;
        --am-border: rgba(148, 163, 184, 0.12);
        --am-border-hover: rgba(99, 102, 241, 0.4);
        --am-text: #f8fafc;
        --am-muted: #94a3b8;
        --am-primary: #6366f1;
        --am-primary-glow: rgba(99, 102, 241, 0.25);
        --am-cyan: #38bdf8;
        --am-success: #10b981;
    }

    .am-wrapper {
        background: radial-gradient(circle at 10% 20%, rgba(99, 102, 241, 0.05) 0%, transparent 40%),
                    radial-gradient(circle at 90% 80%, rgba(56, 189, 248, 0.04) 0%, transparent 40%),
                    var(--am-bg);
        border: 1px solid var(--am-border);
        border-radius: 20px;
        padding: 2rem;
        color: var(--am-text);
        box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.5);
    }

    /* Page Header */
    .am-header-title {
        font-size: 1.65rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        color: #ffffff;
        display: flex;
        align-items: center;
        gap: 0.65rem;
        margin-bottom: 0.25rem;
    }
    .am-header-sub {
        color: var(--am-muted);
        font-size: 0.88rem;
        margin-bottom: 0;
    }

    /* Tabs Styling */
    .am-tabs {
        border-bottom: 1px solid var(--am-border);
        gap: 1.75rem;
    }
    .am-tabs .nav-link {
        color: var(--am-muted);
        background: transparent !important;
        border: none;
        border-bottom: 2.5px solid transparent;
        border-radius: 0;
        font-weight: 600;
        font-size: 0.94rem;
        padding: 0.75rem 0.25rem 1rem 0.25rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .am-tabs .nav-link:hover {
        color: #e2e8f0;
    }
    .am-tabs .nav-link.active {
        color: #a5b4fc !important;
        border-bottom-color: #6366f1 !important;
    }

    /* Top Action Buttons */
    .btn-sync-yellow {
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        color: #0f172a !important;
        font-weight: 700;
        border: none;
        border-radius: 10px;
        padding: 0.55rem 1.15rem;
        font-size: 0.86rem;
        box-shadow: 0 4px 14px rgba(245, 158, 11, 0.25);
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        transition: all 0.2s ease;
    }
    .btn-sync-yellow:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(245, 158, 11, 0.35);
    }

    .btn-add-primary {
        background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
        color: #ffffff !important;
        font-weight: 700;
        border: none;
        border-radius: 10px;
        padding: 0.55rem 1.25rem;
        font-size: 0.86rem;
        box-shadow: 0 4px 14px var(--am-primary-glow);
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        transition: all 0.2s ease;
    }
    .btn-add-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
    }

    /* Role Cards */
    .am-role-card {
        background: var(--am-card);
        border: 1px solid var(--am-border);
        border-radius: 16px;
        padding: 1.35rem 1.45rem;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .am-role-card:hover {
        border-color: var(--am-border-hover);
        transform: translateY(-3px);
        box-shadow: 0 12px 28px -8px rgba(0, 0, 0, 0.6);
    }
    .am-role-title {
        font-size: 1.18rem;
        font-weight: 700;
        color: #ffffff;
        letter-spacing: -0.01em;
    }
    .btn-manage-perms {
        background: rgba(99, 102, 241, 0.08);
        border: 1px solid rgba(99, 102, 241, 0.35);
        color: #a5b4fc !important;
        font-weight: 600;
        border-radius: 10px;
        padding: 0.5rem 1rem;
        width: 100%;
        font-size: 0.86rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        transition: all 0.2s ease;
    }
    .btn-manage-perms:hover {
        background: #6366f1;
        border-color: #6366f1;
        color: #ffffff !important;
        box-shadow: 0 4px 14px var(--am-primary-glow);
    }
    .badge-super-admin {
        background: rgba(16, 185, 129, 0.15);
        border: 1px solid rgba(16, 185, 129, 0.3);
        color: #34d399;
        font-weight: 700;
        font-size: 0.78rem;
        padding: 0.35rem 0.85rem;
        border-radius: 8px;
        letter-spacing: 0.3px;
    }
    .btn-kebab-menu {
        background: rgba(255, 255, 255, 0.06);
        color: #cbd5e1;
        border: 1px solid var(--am-border);
        border-radius: 8px;
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.15s ease;
    }
    .btn-kebab-menu:hover {
        background: rgba(255, 255, 255, 0.15);
        color: #ffffff;
    }

    /* Users Table */
    .am-table-box {
        background: var(--am-card);
        border: 1px solid var(--am-border);
        border-radius: 16px;
        overflow: hidden;
    }
    .am-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-bottom: 0;
    }
    .am-table thead th {
        background: #0b1120;
        color: #94a3b8;
        font-size: 0.74rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        padding: 1.05rem 1.35rem;
        border-bottom: 1px solid var(--am-border);
    }
    .am-table tbody td {
        background: var(--am-card);
        color: var(--am-text);
        padding: 1.1rem 1.35rem;
        border-bottom: 1px solid rgba(148, 163, 184, 0.08);
        vertical-align: middle;
        transition: background 0.15s ease;
    }
    .am-table tbody tr:hover td {
        background: #162035;
    }
    .am-table tbody tr:last-child td {
        border-bottom: none;
    }

    .user-name-text {
        font-weight: 700;
        font-size: 0.96rem;
        color: #ffffff;
    }
    .user-email-text {
        font-family: monospace;
        color: var(--am-muted);
        font-size: 0.86rem;
    }

    .pill-role-badge {
        background: rgba(56, 189, 248, 0.15);
        border: 1px solid rgba(56, 189, 248, 0.3);
        color: #38bdf8;
        font-weight: 700;
        padding: 0.3rem 0.95rem;
        border-radius: 8px;
        font-size: 0.8rem;
        display: inline-block;
    }
    .pill-branch-badge {
        background: rgba(148, 163, 184, 0.12);
        border: 1px solid rgba(148, 163, 184, 0.2);
        color: #cbd5e1;
        font-weight: 600;
        padding: 0.3rem 0.95rem;
        border-radius: 8px;
        font-size: 0.8rem;
        display: inline-block;
    }

    .btn-custom-access {
        background: rgba(99, 102, 241, 0.1);
        border: 1px solid rgba(99, 102, 241, 0.4);
        color: #a5b4fc !important;
        font-weight: 700;
        border-radius: 8px;
        padding: 0.4rem 0.95rem;
        font-size: 0.83rem;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        transition: all 0.15s ease;
    }
    .btn-custom-access:hover {
        background: #6366f1;
        color: #ffffff !important;
        border-color: #6366f1;
        box-shadow: 0 4px 14px var(--am-primary-glow);
    }
    .btn-table-icon {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid var(--am-border);
        color: #cbd5e1;
        border-radius: 8px;
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.15s ease;
    }
    .btn-table-icon:hover {
        background: rgba(255, 255, 255, 0.15);
        color: #ffffff;
    }
    .btn-table-icon.btn-del:hover {
        background: rgba(239, 68, 68, 0.2);
        border-color: rgba(239, 68, 68, 0.4);
        color: #f87171;
    }

    /* =========================================================
       MODALS: EXPANDED COMFORTABLE WIDTH & CHECKBOX STYLING
       ========================================================= */
    .am-modal-wide {
        max-width: 1050px !important;
        width: 95vw !important;
    }

    .am-modal-content {
        background: #0d1424;
        border: 1px solid rgba(148, 163, 184, 0.18);
        border-radius: 20px;
        color: #ffffff;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        max-height: 90vh;
    }
    .am-modal-content > form {
        display: flex;
        flex-direction: column;
        overflow: hidden;
        flex: 1 1 auto;
        min-height: 0;
    }
    .am-modal-header {
        background: #111a2e;
        border-bottom: 1px solid var(--am-border);
        padding: 1.25rem 1.75rem;
        flex-shrink: 0;
    }
    .am-modal-body {
        padding: 1.5rem 1.75rem;
        overflow-y: auto;
        flex: 1 1 auto;
        min-height: 0;
    }
    .am-modal-footer {
        background: #111a2e;
        border-top: 1px solid var(--am-border);
        padding: 1rem 1.75rem;
        flex-shrink: 0;
        position: sticky;
        bottom: 0;
        z-index: 10;
    }

    .am-perm-group-box {
        background: #121d33;
        border: 1px solid rgba(148, 163, 184, 0.14);
        border-radius: 14px;
        padding: 1.15rem;
        margin-bottom: 1rem;
    }
    .am-perm-group-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.85rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid rgba(148, 163, 184, 0.08);
    }
    .am-perm-group-title {
        color: #38bdf8;
        font-weight: 800;
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0;
    }

    .btn-group-toggle {
        background: rgba(56, 189, 248, 0.08);
        border: 1px solid rgba(56, 189, 248, 0.25);
        color: #7dd3fc;
        font-size: 0.72rem;
        font-weight: 700;
        padding: 0.2rem 0.65rem;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        transition: all 0.15s ease;
        cursor: pointer;
    }
    .btn-group-toggle:hover {
        background: rgba(56, 189, 248, 0.2);
        color: #ffffff;
        border-color: #38bdf8;
    }

    .am-perm-item {
        background: #182642;
        border: 1px solid rgba(148, 163, 184, 0.12);
        border-radius: 10px;
        padding: 0.65rem 0.85rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .am-perm-item:hover {
        background: #1e3054;
        border-color: rgba(99, 102, 241, 0.4);
    }
    .am-perm-item .form-check-input {
        cursor: pointer;
        margin-top: 0;
        width: 1.15rem;
        height: 1.15rem;
        background-color: #0b1120;
        border-color: #334155;
    }
    .am-perm-item .form-check-input:checked {
        background-color: #6366f1;
        border-color: #6366f1;
    }

    .am-perm-label {
        font-size: 0.86rem;
        font-weight: 600;
        color: #f1f5f9;
        margin-bottom: 0;
        cursor: pointer;
        user-select: none;
    }
    .am-perm-route {
        font-family: monospace;
        font-size: 0.72rem;
        color: #94a3b8;
        margin-left: 1.7rem;
    }

    /* Modal Form Controls */
    .am-input {
        background: #090e1c !important;
        border: 1px solid rgba(148, 163, 184, 0.2) !important;
        color: #ffffff !important;
        border-radius: 10px;
        padding: 0.55rem 0.95rem;
        font-size: 0.9rem;
    }
    .am-input:focus {
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2) !important;
    }
</style>
@endpush

@section('content')
<div class="am-wrapper">
    <!-- Header Title -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h2 class="am-header-title">
                <i class="fa-solid fa-user-shield text-primary" style="font-size: 1.4rem;"></i> Access Management
            </h2>
            <p class="am-header-sub">Role-Based Access Control, custom staff permissions, and route synchronization</p>
        </div>

        <!-- Action Controls -->
        <div class="d-flex gap-2.5 flex-wrap align-items-center">
            <button type="button" class="btn-sync-yellow" onclick="syncRoutePermissions()">
                <i class="fa-solid fa-arrows-rotate"></i> Sync Routes
            </button>
            <button type="button" class="btn-add-primary" data-bs-toggle="modal" data-bs-target="#createRoleModal">
                <i class="fa-solid fa-plus"></i> Add New Role
            </button>
            <button type="button" class="btn-add-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="fa-solid fa-user-plus"></i> Add New User
            </button>
        </div>
    </div>

    <!-- Navigation Tabs (Roles & Permissions / User Management) -->
    <ul class="nav am-tabs mb-4" id="accessTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="roles-tab" data-bs-toggle="tab" data-bs-target="#roles-tab-pane" type="button" role="tab" aria-controls="roles-tab-pane" aria-selected="true">
                <i class="fa-solid fa-shield-halved"></i> Roles & Permissions
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="users-tab" data-bs-toggle="tab" data-bs-target="#users-tab-pane" type="button" role="tab" aria-controls="users-tab-pane" aria-selected="false">
                <i class="fa-solid fa-users"></i> User Management
            </button>
        </li>
    </ul>

    <!-- Tab Contents -->
    <div class="tab-content" id="accessTabsContent">
        <!-- ========================================== -->
        <!-- TAB 1: Roles & Permissions Grid           -->
        <!-- ========================================== -->
        <div class="tab-pane fade show active" id="roles-tab-pane" role="tabpanel" aria-labelledby="roles-tab" tabindex="0">
            @if($roles->isEmpty())
                <div class="text-center py-5 border border-secondary border-opacity-25 rounded-4" style="background: var(--am-card);">
                    <i class="fa-solid fa-shield-cat fs-1 text-muted mb-2"></i>
                    <h5 class="fw-bold text-white">No Roles Created Yet</h5>
                    <p class="text-muted small mb-3">Click "+ Add New Role" to create your first custom role with permissions.</p>
                    <button type="button" class="btn-add-primary btn-sm px-4" data-bs-toggle="modal" data-bs-target="#createRoleModal">
                        <i class="fa-solid fa-plus me-1"></i> Add New Role
                    </button>
                </div>
            @else
                <div class="row g-4">
                    @foreach($roles as $role)
                        <div class="col-md-6 col-lg-4" id="roleCardCol{{ $role->id }}">
                            <div class="am-role-card">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h4 class="am-role-title mb-0">{{ $role->display_name }}</h4>
                                    
                                    @if($role->name === 'super_admin')
                                        <span class="badge-super-admin">Super Admin</span>
                                    @else
                                        <div class="dropdown">
                                            <button class="btn-kebab-menu" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Role Options">
                                                <i class="fa-solid fa-ellipsis-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow">
                                                <li>
                                                    <a class="dropdown-item small" href="javascript:void(0)" onclick="openRoleMatrixModal({{ $role->id }}, '{{ addslashes($role->display_name) }}')">
                                                        <i class="fa-solid fa-shield-halved me-2 text-primary"></i> Edit Permissions
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider border-secondary"></li>
                                                <li>
                                                    <a class="dropdown-item small text-danger" href="javascript:void(0)" onclick="deleteRole({{ $role->id }}, '{{ addslashes($role->display_name) }}')">
                                                        <i class="fa-solid fa-trash-can me-2"></i> Delete Role
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    @endif
                                </div>

                                <div>
                                    @if($role->name === 'super_admin')
                                        <div class="text-muted small font-monospace py-1.5"><i class="fa-solid fa-infinity me-1.5 text-success"></i>Full system bypass active</div>
                                    @else
                                        <button type="button" class="btn-manage-perms" onclick="openRoleMatrixModal({{ $role->id }}, '{{ addslashes($role->display_name) }}')">
                                            <i class="fa-solid fa-shield-halved"></i> Manage Permissions
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- ========================================== -->
        <!-- TAB 2: User Management Table               -->
        <!-- ========================================== -->
        <div class="tab-pane fade" id="users-tab-pane" role="tabpanel" aria-labelledby="users-tab" tabindex="0">
            <div class="d-flex justify-content-end mb-3">
                <button type="button" class="btn-add-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="fa-solid fa-user-plus"></i> Add New User
                </button>
            </div>

            <div class="am-table-box">
                <table class="am-table">
                    <thead>
                        <tr>
                            <th style="min-width: 170px;">NAME</th>
                            <th style="min-width: 230px;">EMAIL</th>
                            <th style="min-width: 150px;">ROLE</th>
                            <th style="min-width: 140px;">BRANCH</th>
                            <th style="min-width: 220px;" class="text-end">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr id="userRow{{ $user->id }}">
                                <td>
                                    <div class="user-name-text">{{ $user->name }}</div>
                                </td>
                                <td>
                                    <span class="user-email-text">{{ $user->email }}</span>
                                </td>
                                <td>
                                    @php
                                        $roleName = $userRoles[$user->id] ?? 'Custom';
                                        $directCount = count($userPermissions[$user->id] ?? []);
                                    @endphp
                                    <span class="pill-role-badge">{{ $roleName }}</span>
                                    @if($directCount > 0)
                                        <span class="badge bg-indigo-subtle text-info border border-info-subtle font-monospace ms-1" style="font-size: 10px;">+{{ $directCount }} perms</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="pill-branch-badge">{{ !empty($user->district) ? $user->district : 'Head Office' }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex align-items-center gap-2">
                                        <button type="button" class="btn-custom-access" onclick="openUserPermissionsModal({{ $user->id }}, '{{ addslashes($user->name) }}')">
                                            <i class="fa-solid fa-user-shield"></i> Custom Access
                                        </button>
                                        <button type="button" class="btn-table-icon" onclick="openEditUserModal({{ $user->id }})" title="Edit User">
                                            <i class="fa-solid fa-pen" style="font-size: 12px;"></i>
                                        </button>
                                        <button type="button" class="btn-table-icon btn-del" onclick="deleteUser({{ $user->id }}, '{{ addslashes($user->name) }}')" title="Delete User">
                                            <i class="fa-solid fa-trash-can" style="font-size: 12px;"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">No staff accounts found. Click "+ Add New User" above to create one.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL 1: Create New Role Modal                            -->
<!-- ======================================================== -->
<div class="modal fade" id="createRoleModal" tabindex="-1" aria-labelledby="createRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog am-modal-wide modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content am-modal-content">
            <div class="modal-header am-modal-header">
                <div>
                    <h5 class="modal-title fw-bold text-white mb-0" id="createRoleModalLabel">
                        <i class="fa-solid fa-shield-plus me-2 text-primary"></i>Create New Role
                    </h5>
                    <small class="text-muted">Define a new system role and assign baseline permissions.</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="createRoleForm" onsubmit="submitNewRole(event)">
                <div class="modal-body am-modal-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-light">Role Title <span class="text-danger">*</span></label>
                            <input type="text" name="display_name" id="newRoleDisplayName" class="form-control am-input" placeholder="e.g. Sales, Manager, Accountant" required oninput="autoGenerateRoleSlug(this.value)">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-light">Role Identifier / Slug <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="newRoleSlug" class="form-control am-input font-monospace" placeholder="e.g. sales, manager" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small text-light">Description (Optional)</label>
                            <input type="text" name="description" class="form-control am-input" placeholder="Brief summary of duties and access level">
                        </div>
                    </div>

                    <!-- Permissions Selection -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <label class="form-label fw-bold small text-light mb-0">Role Baseline Permissions</label>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="toggleAllNewRoleBtn" onclick="toggleAllNewRolePermsDynamic()">
                            <i class="fa-solid fa-check-double me-1"></i> <span id="toggleAllNewRoleText">Select All</span>
                        </button>
                    </div>

                    @foreach($permissions as $group => $perms)
                        @php $groupSlug = \Illuminate\Support\Str::slug($group); @endphp
                        <div class="am-perm-group-box" id="new_role_box_{{ $groupSlug }}">
                            <div class="am-perm-group-header">
                                <div class="am-perm-group-title">
                                    <i class="fa-solid fa-folder-open"></i> {{ $group }}
                                </div>
                                <button type="button" class="btn-group-toggle" onclick="toggleSpecificGroupBox('new_role_box_{{ $groupSlug }}')">
                                    <i class="fa-solid fa-check-double"></i> Check Group
                                </button>
                            </div>
                            <div class="row g-2">
                                @foreach($perms as $p)
                                    <div class="col-md-6">
                                        <div class="am-perm-item" onclick="toggleCheckboxDirectly('new_role_p_{{ $p->id }}', event)">
                                            <div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <input class="form-check-input new-role-perm-cb" type="checkbox" name="permissions[]" value="{{ $p->id }}" id="new_role_p_{{ $p->id }}" onclick="event.stopPropagation()">
                                                    <label class="am-perm-label" for="new_role_p_{{ $p->id }}">{{ $p->display_name }}</label>
                                                </div>
                                                <div class="am-perm-route">{{ $p->name }}</div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="modal-footer am-modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm px-3.5 rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-add-primary px-4" id="createRoleSubmitBtn">
                        <i class="fa-solid fa-check"></i> Save Role
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL 2: Manage Role Permissions Matrix Modal             -->
<!-- ======================================================== -->
<div class="modal fade" id="roleMatrixModal" tabindex="-1" aria-labelledby="roleMatrixModalLabel" aria-hidden="true">
    <div class="modal-dialog am-modal-wide modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content am-modal-content">
            <div class="modal-header am-modal-header">
                <div>
                    <h5 class="modal-title fw-bold text-white mb-0" id="roleMatrixModalLabel">
                        <i class="fa-solid fa-shield-halved me-2 text-primary"></i>Manage Permissions for <span id="roleModalTitle" class="text-info"></span>
                    </h5>
                    <small class="text-muted">Configure default permissions inherited by users assigned to this role.</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body am-modal-body">
                <input type="hidden" id="currentManagingRoleId" value="">
                <div class="d-flex justify-content-end gap-2 mb-3">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="toggleAllRoleMatrixBtn" onclick="toggleAllRoleMatrixDynamic()">
                        <i class="fa-solid fa-check-double me-1"></i> <span id="toggleAllRoleMatrixText">Select All</span>
                    </button>
                </div>

                <div id="roleMatrixContainer" class="d-flex flex-column">
                    @foreach($permissions as $group => $perms)
                        @php $groupSlug = \Illuminate\Support\Str::slug($group); @endphp
                        <div class="am-perm-group-box" id="role_matrix_box_{{ $groupSlug }}">
                            <div class="am-perm-group-header">
                                <div class="am-perm-group-title">
                                    <i class="fa-solid fa-layer-group"></i> {{ $group }}
                                </div>
                                <button type="button" class="btn-group-toggle" onclick="toggleSpecificGroupBox('role_matrix_box_{{ $groupSlug }}')">
                                    <i class="fa-solid fa-check-double"></i> Check Group
                                </button>
                            </div>
                            <div class="row g-2">
                                @foreach($perms as $p)
                                    <div class="col-md-6">
                                        <div class="am-perm-item" onclick="toggleCheckboxDirectly('rmp_{{ $p->id }}', event)">
                                            <div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <input class="form-check-input role-perm-modal-cb" type="checkbox" name="role_modal_permissions[]" value="{{ $p->id }}" id="rmp_{{ $p->id }}" onclick="event.stopPropagation()">
                                                    <label class="am-perm-label" for="rmp_{{ $p->id }}">
                                                        {{ $p->display_name }}
                                                    </label>
                                                </div>
                                                <div class="am-perm-route">{{ $p->name }}</div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="modal-footer am-modal-footer">
                <button type="button" class="btn btn-secondary btn-sm px-3.5 rounded-3" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn-add-primary px-4" id="saveRoleMatrixBtn" onclick="saveRoleMatrixFromModal()">
                    <i class="fa-solid fa-floppy-disk"></i> Save Permissions
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL 3: Individual User Custom Access Permissions Modal  -->
<!-- ======================================================== -->
<div class="modal fade" id="userPermissionsModal" tabindex="-1" aria-labelledby="userPermissionsModalLabel" aria-hidden="true">
    <div class="modal-dialog am-modal-wide modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content am-modal-content">
            <div class="modal-header am-modal-header">
                <div>
                    <h5 class="modal-title fw-bold text-white mb-0" id="userPermissionsModalLabel">
                        <i class="fa-solid fa-user-gear me-2 text-primary"></i>Custom Access: <span id="modalUserName" class="text-warning"></span>
                    </h5>
                    <small class="text-muted">Grant or revoke direct granular permissions point-by-point for this individual user.</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body am-modal-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 p-3 rounded-3 mb-3" style="background: #111a2e; border: 1px solid var(--am-border);">
                    <div>
                        <span class="d-block small text-muted">User ID: <b id="modalUserIdBadge" class="text-white"></b> | Email: <span id="modalUserEmail" class="font-monospace text-info"></span></span>
                    </div>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="toggleAllUserPermsBtn" onclick="toggleAllUserPermsOneButton()">
                            <i class="fa-solid fa-check-double me-1"></i> <span id="toggleAllUserPermsText">Toggle All</span>
                        </button>
                    </div>
                </div>

                <div id="userPermModalLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="mt-2 text-muted small">Loading permission matrix...</div>
                </div>

                <form id="userDirectPermissionsForm" style="display: none;">
                    <input type="hidden" id="currentManagingUserId" value="">
                    <div id="userPermModalGroups" class="d-flex flex-column">
                        <!-- Populated dynamically via JS -->
                    </div>
                </form>
            </div>

            <div class="modal-footer am-modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-secondary btn-sm px-3.5 rounded-3" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn-add-primary px-4" id="saveUserPermBtn" onclick="submitUserDirectPermissions()">
                    <i class="fa-solid fa-floppy-disk"></i> Save Custom Access
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL 4: Add New User Modal                               -->
<!-- ======================================================== -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog am-modal-wide modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content am-modal-content">
            <div class="modal-header am-modal-header">
                <div>
                    <h5 class="modal-title fw-bold text-white mb-0" id="addUserModalLabel">
                        <i class="fa-solid fa-user-plus me-2 text-primary"></i>Add New User
                    </h5>
                    <small class="text-muted">Create a new staff or admin user account.</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="addUserForm" onsubmit="submitNewUser(event)">
                <div class="modal-body am-modal-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-light">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control am-input" placeholder="e.g. Sales Man, Md Tanvir" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-light">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control am-input" placeholder="sales@zippybd.com" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-light">Branch / Location</label>
                            <input type="text" name="district" class="form-control am-input" placeholder="e.g. Mirpur 1, Agargaon, Head Office">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-light">Phone Number</label>
                            <input type="text" name="phone" class="form-control am-input" placeholder="017XXXXXXXX">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-light">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control am-input" placeholder="Minimum 6 characters" required minlength="6">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-light">Assign Role</label>
                            <select name="role_id" class="form-select am-input">
                                <option value="">— Custom (Direct Permissions Only) —</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Optional Direct Permissions on Creation -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <label class="form-label fw-bold small text-light mb-0">Initial Direct Permissions (Optional)</label>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="toggleAllNewUserBtn" onclick="toggleAllNewUserPermsDynamic()">
                            <i class="fa-solid fa-check-double me-1"></i> <span id="toggleAllNewUserText">Select All</span>
                        </button>
                    </div>

                    @foreach($permissions as $group => $perms)
                        @php $groupSlug = \Illuminate\Support\Str::slug($group); @endphp
                        <div class="am-perm-group-box" id="new_user_box_{{ $groupSlug }}">
                            <div class="am-perm-group-header">
                                <div class="am-perm-group-title">
                                    <i class="fa-solid fa-folder"></i> {{ $group }}
                                </div>
                                <button type="button" class="btn-group-toggle" onclick="toggleSpecificGroupBox('new_user_box_{{ $groupSlug }}')">
                                    <i class="fa-solid fa-check-double"></i> Check Group
                                </button>
                            </div>
                            <div class="row g-2">
                                @foreach($perms as $p)
                                    <div class="col-md-6">
                                        <div class="am-perm-item" onclick="toggleCheckboxDirectly('new_u_p_{{ $p->id }}', event)">
                                            <div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $p->id }}" id="new_u_p_{{ $p->id }}" onclick="event.stopPropagation()">
                                                    <label class="am-perm-label" for="new_u_p_{{ $p->id }}">{{ $p->display_name }}</label>
                                                </div>
                                                <div class="am-perm-route">{{ $p->name }}</div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="modal-footer am-modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm px-3.5 rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-add-primary px-4" id="addUserSubmitBtn">
                        <i class="fa-solid fa-check"></i> Create User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL 5: Edit User Details Modal                          -->
<!-- ======================================================== -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content am-modal-content">
            <div class="modal-header am-modal-header">
                <h5 class="modal-title fw-bold text-white mb-0" id="editUserModalLabel">
                    <i class="fa-solid fa-user-pen me-2 text-primary"></i>Edit User Account
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="editUserForm" onsubmit="submitEditUser(event)">
                <input type="hidden" id="editUserId" value="">
                <div class="modal-body am-modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-light">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editUserName" class="form-control am-input" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-light">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="editUserEmail" class="form-control am-input" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-light">Branch / Location</label>
                        <input type="text" name="district" id="editUserDistrict" class="form-control am-input" placeholder="e.g. Mirpur 1, Agargaon">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-light">Phone Number</label>
                        <input type="text" name="phone" id="editUserPhone" class="form-control am-input">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-light">Assign Role</label>
                        <select name="role_id" id="editUserRoleId" class="form-select am-input">
                            <option value="">— Custom (Direct Permissions Only) —</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-light">New Password (Leave blank to keep unchanged)</label>
                        <input type="password" name="password" class="form-control am-input" placeholder="Enter new password if changing">
                    </div>
                </div>

                <div class="modal-footer am-modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm px-3.5 rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-add-primary px-4" id="editUserSubmitBtn">
                        <i class="fa-solid fa-save"></i> Update User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const rolePermissionsCache = @json($rolePermissions);

    function toggleCheckboxDirectly(id, event) {
        const cb = document.getElementById(id);
        if (cb && event.target !== cb) {
            cb.checked = !cb.checked;
        }
    }

    function toggleSpecificGroupBox(boxId) {
        const box = document.getElementById(boxId);
        if (!box) return;
        const checkboxes = box.querySelectorAll('input[type="checkbox"]');
        if (!checkboxes.length) return;

        const hasUnchecked = Array.from(checkboxes).some(cb => !cb.checked);
        checkboxes.forEach(cb => {
            cb.checked = hasUnchecked;
        });
    }

    let roleMatrixModalInstance = null;

    function openRoleMatrixModal(roleId, roleTitle) {
        const modalEl = document.getElementById('roleMatrixModal');
        if (modalEl) {
            roleMatrixModalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        }

        document.getElementById('currentManagingRoleId').value = roleId;
        document.getElementById('roleModalTitle').textContent = roleTitle;

        const assignedIds = rolePermissionsCache[roleId] || [];
        document.querySelectorAll('.role-perm-modal-cb').forEach(cb => {
            cb.checked = assignedIds.includes(parseInt(cb.value));
        });

        if (roleMatrixModalInstance) roleMatrixModalInstance.show();
    }

function toggleAllRoleMatrixDynamic() {
    const cbs = document.querySelectorAll('.role-perm-modal-cb');
    if (!cbs.length) return;
    const hasUnchecked = Array.from(cbs).some(cb => !cb.checked);
    cbs.forEach(cb => { cb.checked = hasUnchecked; });
    const textEl = document.getElementById('toggleAllRoleMatrixText');
    if (textEl) {
        textEl.textContent = hasUnchecked ? 'Deselect All' : 'Select All';
    }
}

function saveRoleMatrixFromModal() {
    const roleId = document.getElementById('currentManagingRoleId').value;
    if (!roleId) return;

    const checkboxes = document.querySelectorAll('input[name="role_modal_permissions[]"]:checked');
    const permissions = Array.from(checkboxes).map(cb => parseInt(cb.value));

    const btn = document.getElementById('saveRoleMatrixBtn');
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1.5"></i> Saving...';

    axios.post(`/admin/rbac/roles/${roleId}/permissions`, { permissions: permissions })
    .then(res => {
        const data = res.data;
        rolePermissionsCache[roleId] = permissions;
        Swal.fire({
            icon: data.success ? 'success' : 'error',
            title: data.success ? 'Saved!' : 'Error',
            text: data.message,
            timer: 1500,
            showConfirmButton: false
        });
        if (roleMatrixModalInstance) roleMatrixModalInstance.hide();
    })
    .catch(err => {
        Swal.fire('Error', err.response?.data?.message || 'Failed to update permissions.', 'error');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    });
}

function autoGenerateRoleSlug(val) {
    const slugInput = document.getElementById('newRoleSlug');
    if (slugInput) {
        slugInput.value = val.toLowerCase().trim().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '');
    }
}

function toggleAllNewRolePermsDynamic() {
    const cbs = document.querySelectorAll('.new-role-perm-cb');
    if (!cbs.length) return;
    const hasUnchecked = Array.from(cbs).some(cb => !cb.checked);
    cbs.forEach(cb => { cb.checked = hasUnchecked; });
    const textEl = document.getElementById('toggleAllNewRoleText');
    if (textEl) {
        textEl.textContent = hasUnchecked ? 'Deselect All' : 'Select All';
    }
}

function toggleAllNewUserPermsDynamic() {
    const cbs = document.querySelectorAll('#addUserModal input[name="permissions[]"]');
    if (!cbs.length) return;
    const hasUnchecked = Array.from(cbs).some(cb => !cb.checked);
    cbs.forEach(cb => { cb.checked = hasUnchecked; });
    const textEl = document.getElementById('toggleAllNewUserText');
    if (textEl) {
        textEl.textContent = hasUnchecked ? 'Deselect All' : 'Select All';
    }
}

function submitNewRole(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const permissions = formData.getAll('permissions[]');

    const payload = {
        name: formData.get('name'),
        display_name: formData.get('display_name'),
        description: formData.get('description'),
        permissions: permissions
    };

    const btn = document.getElementById('createRoleSubmitBtn');
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1.5"></i> Saving...';

    axios.post('/admin/rbac/roles', payload)
    .then(res => {
        const data = res.data;
        Swal.fire({
            icon: 'success',
            title: 'Role Created!',
            text: data.message,
            timer: 1500,
            showConfirmButton: false
        });
        setTimeout(() => {
            if (window.Turbo) {
                window.Turbo.visit(window.location.href, { action: 'replace' });
            } else {
                location.reload();
            }
        }, 1200);
    })
    .catch(err => {
        const msg = err.response?.data?.message || (err.response?.data?.errors ? Object.values(err.response.data.errors).flat().join('<br>') : 'Failed to create role.');
        Swal.fire('Error', msg, 'error');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    });
}

function deleteRole(roleId, roleName) {
    Swal.fire({
        title: `Delete Role "${roleName}"?`,
        text: 'Users assigned to this role will lose their assigned role permissions.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, Delete Role'
    }).then((result) => {
        if (result.isConfirmed) {
            axios.delete(`/admin/rbac/roles/${roleId}`)
            .then(res => {
                Swal.fire({
                    icon: 'success',
                    title: 'Deleted!',
                    text: res.data.message,
                    timer: 1500,
                    showConfirmButton: false
                });
                const card = document.getElementById(`roleCardCol${roleId}`);
                if (card) card.remove();
            })
            .catch(err => {
                Swal.fire('Error', err.response?.data?.message || 'Failed to delete role.', 'error');
            });
        }
    });
}

function syncRoutePermissions() {
    Swal.fire({
        title: 'Syncing Routes...',
        html: '<p class="mb-0 text-muted">Scanning all /admin routes into permissions table.</p>',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    axios.post('/admin/rbac/sync')
    .then(res => {
        const s = res.data.stats || {};
        Swal.fire({
            icon: 'success',
            title: 'Sync Complete!',
            html: `<p class="mb-1"><b>${s.created ?? 0}</b> new permissions created.</p>
                   <p class="mb-0"><b>${s.total ?? 0}</b> total routes mapped.</p>`,
            timer: 2000,
            showConfirmButton: false
        });
        setTimeout(() => {
            if (window.Turbo) {
                window.Turbo.visit(window.location.href, { action: 'replace' });
            } else {
                location.reload();
            }
        }, 1500);
    }).catch(() => {
        Swal.fire('Error', 'Failed to sync route permissions.', 'error');
    });
}

let userPermModal = null;

function openUserPermissionsModal(userId, userName) {
    const modalEl = document.getElementById('userPermissionsModal');
    if (modalEl) {
        userPermModal = bootstrap.Modal.getOrCreateInstance(modalEl);
    }

    document.getElementById('currentManagingUserId').value = userId;
    document.getElementById('modalUserName').textContent = userName;
    document.getElementById('modalUserIdBadge').textContent = '#' + userId;
    document.getElementById('modalUserEmail').textContent = 'Loading...';

    const loadingEl = document.getElementById('userPermModalLoading');
    const formEl = document.getElementById('userDirectPermissionsForm');
    const container = document.getElementById('userPermModalGroups');

    loadingEl.style.display = 'block';
    formEl.style.display = 'none';
    container.innerHTML = '';

    userPermModal.show();

    axios.get(`/admin/rbac/users/${userId}/permissions`)
    .then(res => {
        const data = res.data;
        if (!data.success) {
            Swal.fire('Error', data.message || 'User not found', 'error');
            return;
        }

        document.getElementById('modalUserEmail').textContent = data.user.email;

        const directIds = data.direct_permission_ids || [];
        const roleIds = data.role_permission_ids || [];
        const groups = data.grouped_permissions || {};

        let html = '';
        let groupIdx = 0;
        for (const [groupName, perms] of Object.entries(groups)) {
            groupIdx++;
            const groupContainerId = `user_perm_group_box_${groupIdx}`;
            html += `
                <div class="am-perm-group-box" id="${groupContainerId}">
                    <div class="am-perm-group-header">
                        <div class="am-perm-group-title">
                            <i class="fa-solid fa-folder-open"></i> ${groupName}
                        </div>
                        <button type="button" class="btn-group-toggle" onclick="toggleSpecificGroupBox('${groupContainerId}')">
                            <i class="fa-solid fa-check-double"></i> Check Group
                        </button>
                    </div>
                    <div class="row g-2">
            `;

            perms.forEach(p => {
                const isDirect = directIds.includes(p.id);
                const isRoleInherited = roleIds.includes(p.id);

                html += `
                    <div class="col-md-6">
                        <div class="am-perm-item" onclick="toggleCheckboxDirectly('user_p_${p.id}', event)">
                            <div class="w-100">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <input class="form-check-input user-perm-checkbox" type="checkbox" name="permissions[]" value="${p.id}" id="user_p_${p.id}" ${isDirect ? 'checked' : ''} onclick="event.stopPropagation()">
                                        <label class="am-perm-label" for="user_p_${p.id}">
                                            ${p.display_name}
                                        </label>
                                    </div>
                                    <div>
                                        ${isRoleInherited ? '<span class="badge bg-secondary text-white font-monospace" style="font-size: 9px;">Role</span>' : ''}
                                    </div>
                                </div>
                                <div class="am-perm-route">${p.name}</div>
                            </div>
                        </div>
                    </div>
                `;
            });

            html += `
                    </div>
                </div>
            `;
        }

        container.innerHTML = html;
        loadingEl.style.display = 'none';
        formEl.style.display = 'block';
    })
    .catch(err => {
        loadingEl.innerHTML = '<div class="text-danger py-4">Failed to load user permissions.</div>';
    });
}

function toggleAllUserPermsOneButton() {
    const checkboxes = document.querySelectorAll('.user-perm-checkbox');
    if (!checkboxes.length) return;

    const hasUnchecked = Array.from(checkboxes).some(cb => !cb.checked);
    checkboxes.forEach(cb => {
        cb.checked = hasUnchecked;
    });

    const textEl = document.getElementById('toggleAllUserPermsText');
    if (textEl) {
        textEl.textContent = hasUnchecked ? 'Deselect All' : 'Select All';
    }
}

function submitUserDirectPermissions() {
    const userId = document.getElementById('currentManagingUserId').value;
    if (!userId) return;

    const checkboxes = document.querySelectorAll('#userDirectPermissionsForm input[name="permissions[]"]:checked');
    const permissions = Array.from(checkboxes).map(cb => parseInt(cb.value));

    const btn = document.getElementById('saveUserPermBtn');
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1.5"></i> Saving...';

    axios.post(`/admin/rbac/users/${userId}/permissions`, { permissions: permissions })
    .then(res => {
        const data = res.data;
        Swal.fire({
            icon: data.success ? 'success' : 'error',
            title: data.success ? 'Saved!' : 'Error',
            text: data.message,
            timer: 1500,
            showConfirmButton: false
        });
        if (userPermModal) userPermModal.hide();
        setTimeout(() => {
            if (window.Turbo) {
                window.Turbo.visit(window.location.href, { action: 'replace' });
            } else {
                location.reload();
            }
        }, 1000);
    })
    .catch(err => {
        Swal.fire('Error', err.response?.data?.message || 'Failed to update user permissions.', 'error');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    });
}

function submitNewUser(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const permissions = formData.getAll('permissions[]');

    const payload = {
        name: formData.get('name'),
        email: formData.get('email'),
        phone: formData.get('phone'),
        district: formData.get('district'),
        password: formData.get('password'),
        role_id: formData.get('role_id') || null,
        permissions: permissions
    };

    const btn = document.getElementById('addUserSubmitBtn');
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1.5"></i> Creating...';

    axios.post('/admin/rbac/users', payload)
    .then(res => {
        const data = res.data;
        Swal.fire({
            icon: 'success',
            title: 'User Created!',
            text: data.message,
            timer: 1500,
            showConfirmButton: false
        });
        setTimeout(() => {
            if (window.Turbo) {
                window.Turbo.visit(window.location.href, { action: 'replace' });
            } else {
                location.reload();
            }
        }, 1200);
    })
    .catch(err => {
        const msg = err.response?.data?.message || (err.response?.data?.errors ? Object.values(err.response.data.errors).flat().join('<br>') : 'Failed to create user.');
        Swal.fire('Error', msg, 'error');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    });
}

let editUserModalInstance = null;

function openEditUserModal(userId) {
    const modalEl = document.getElementById('editUserModal');
    if (modalEl) {
        editUserModalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    }

    axios.get(`/admin/rbac/users/${userId}/json`)
    .then(res => {
        const data = res.data;
        if (data.success && data.user) {
            document.getElementById('editUserId').value = data.user.id;
            document.getElementById('editUserName').value = data.user.name;
            document.getElementById('editUserEmail').value = data.user.email;
            document.getElementById('editUserDistrict').value = data.user.district || '';
            document.getElementById('editUserPhone').value = data.user.phone || '';
            document.getElementById('editUserRoleId').value = data.user.role_id || '';
            if (editUserModalInstance) editUserModalInstance.show();
        }
    })
    .catch(() => {
        Swal.fire('Error', 'Failed to fetch user details.', 'error');
    });
}

function submitEditUser(e) {
    e.preventDefault();
    const userId = document.getElementById('editUserId').value;
    const form = e.target;
    const formData = new FormData(form);

    const payload = {
        name: formData.get('name'),
        email: formData.get('email'),
        phone: formData.get('phone'),
        district: formData.get('district'),
        role_id: formData.get('role_id') || null,
    };

    if (formData.get('password')) {
        payload.password = formData.get('password');
    }

    const btn = document.getElementById('editUserSubmitBtn');
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1.5"></i> Updating...';

    axios.post(`/admin/rbac/users/${userId}/update`, payload)
    .then(res => {
        Swal.fire({
            icon: 'success',
            title: 'Updated!',
            text: res.data.message,
            timer: 1500,
            showConfirmButton: false
        });
        setTimeout(() => {
            if (window.Turbo) {
                window.Turbo.visit(window.location.href, { action: 'replace' });
            } else {
                location.reload();
            }
        }, 1200);
    })
    .catch(err => {
        const msg = err.response?.data?.message || 'Failed to update user.';
        Swal.fire('Error', msg, 'error');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    });
}

function deleteUser(userId, userName) {
    Swal.fire({
        title: `Delete User "${userName}"?`,
        text: 'This user will no longer be able to log in to the admin panel.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, Delete User'
    }).then((result) => {
        if (result.isConfirmed) {
            axios.delete(`/admin/rbac/users/${userId}`)
            .then(res => {
                Swal.fire({
                    icon: 'success',
                    title: 'Deleted!',
                    text: res.data.message,
                    timer: 1500,
                    showConfirmButton: false
                });
                const row = document.getElementById(`userRow${userId}`);
                if (row) row.remove();
            })
            .catch(err => {
                Swal.fire('Error', err.response?.data?.message || 'Failed to delete user.', 'error');
            });
        }
    });
}

window.toggleCheckboxDirectly = toggleCheckboxDirectly;
window.toggleSpecificGroupBox = toggleSpecificGroupBox;
window.openRoleMatrixModal = openRoleMatrixModal;
window.toggleAllRoleMatrixDynamic = toggleAllRoleMatrixDynamic;
window.saveRoleMatrixFromModal = saveRoleMatrixFromModal;
window.autoGenerateRoleSlug = autoGenerateRoleSlug;
window.toggleAllNewRolePermsDynamic = toggleAllNewRolePermsDynamic;
window.toggleAllNewUserPermsDynamic = toggleAllNewUserPermsDynamic;
window.submitNewRole = submitNewRole;
window.deleteRole = deleteRole;
window.syncRoutePermissions = syncRoutePermissions;
window.openUserPermissionsModal = openUserPermissionsModal;
window.toggleAllUserPermsOneButton = toggleAllUserPermsOneButton;
window.submitUserDirectPermissions = submitUserDirectPermissions;
window.submitNewUser = submitNewUser;
window.openEditUserModal = openEditUserModal;
window.submitEditUser = submitEditUser;
window.deleteUser = deleteUser;
})();
</script>
@endpush
