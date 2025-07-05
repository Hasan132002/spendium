<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Enums\ActionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\RoleRequest;
use App\Services\PermissionService;
use App\Services\RolesService;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;

class RolesController extends Controller
{
    public function __construct(
        private readonly RolesService $rolesService,
        private readonly PermissionService $permissionService
    ) {
    }

    public function index(): Renderable
    {
        $this->checkAuthorization(auth()->user(), ['role.view']);

        $perPage = config('settings.default_pagination') ?? 10;
        $search = request()->input('search') !== '' ? request()->input('search') : null;

        return view('backend.pages.roles.index', [
            'roles' => $this->rolesService->getPaginatedRolesWithUserCount($search, intval($perPage)),
        ]);
    }

    public function create(): Renderable
    {
        $this->checkAuthorization(auth()->user(), ['role.create']);

        return view('backend.pages.roles.create', [
            'roleService' => $this->rolesService,
            'all_permissions' => $this->permissionService->getAllPermissionModels(),
            'permission_groups' => $this->permissionService->getDatabasePermissionGroups(),
        ]);
    }

    public function store(RoleRequest $request): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['role.create']);

        $role = $this->rolesService->createRole($request->name, $request->input('permissions', []));

        session()->flash('success', __('Role has been created.'));

        $this->storeActionLog(ActionType::CREATED, ['role' => $role]);

        return redirect()->route('admin.roles.index');
    }

    public function edit(int $id): Renderable|RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['role.edit']);

        $role = $this->rolesService->findRoleById($id);
        if (!$role) {
            session()->flash('error', __('Role not found.'));

            return back();
        }

        return view('backend.pages.roles.edit', [
            'role' => $role,
            'roleService' => $this->rolesService,
            'all_permissions' => $this->permissionService->getAllPermissionModels(),
            'permission_groups' => $this->permissionService->getDatabasePermissionGroups(),
        ]);
    }

    public function update(RoleRequest $request, int $id): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['role.edit']);

        $role = $this->rolesService->findRoleById($id);

        if (!$role) {
            session()->flash('error', __('Role not found.'));
            return back();
        }

        $this->preventSuperAdminRoleModification($role, 'modified');

        $role = $this->rolesService->updateRole($role, $request->name, $request->input('permissions', []));

        session()->flash('success', __('Role has been updated.'));
        $this->storeActionLog(ActionType::UPDATED, ['role' => $role]);

        return back();
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['role.delete']);

        $role = $this->rolesService->findRoleById($id);

        if (!$role) {
            session()->flash('error', __('Role not found.'));
            return back();
        }

        $this->preventSuperAdminRoleModification($role, 'deleted');

        $this->rolesService->deleteRole($role);
        $this->storeActionLog(ActionType::DELETED, ['role' => $role]);
        session()->flash('success', __('Role has been deleted.'));

        return redirect()->route('admin.roles.index');
    }
}
