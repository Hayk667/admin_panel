<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use App\Models\PagePermission;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Str;

class RolePermissionController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of roles.
     */
    public function index(): View
    {
        // Only admin can access
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();
        $pagePermissions = PagePermission::with('roles')->get();

        return view('admin.roles.index', compact('roles', 'permissions', 'pagePermissions'));
    }

    /**
     * Show the form for editing role permissions.
     */
    public function editRole(Role $role): View
    {
        // Only admin can access
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        // Don't allow editing admin role permissions
        if ($role->slug === 'admin') {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Admin role permissions cannot be modified.');
        }

        $permissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update role permissions.
     */
    public function updateRole(Request $request, Role $role): RedirectResponse
    {
        // Only admin can access
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        // Don't allow editing admin role permissions
        if ($role->slug === 'admin') {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Admin role permissions cannot be modified.');
        }

        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->permissions()->sync($request->input('permissions', []));

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role permissions updated successfully.');
    }

    /**
     * Show the form for editing page permissions.
     */
    public function editPagePermission(PagePermission $pagePermission): View
    {
        // Only admin can access
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $roles = Role::all();
        $allowedRoles = $pagePermission->roles->pluck('id')->toArray();

        return view('admin.page-permissions.edit', compact('pagePermission', 'roles', 'allowedRoles'));
    }

    /**
     * Update page permissions.
     */
    public function updatePagePermission(Request $request, PagePermission $pagePermission): RedirectResponse
    {
        // Only admin can access
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $pagePermission->roles()->sync($request->input('roles', []));

        return redirect()->route('admin.roles.index')
            ->with('success', 'Page permissions updated successfully.');
    }

    /**
     * Create a new permission.
     */
    public function storePermission(Request $request): RedirectResponse
    {
        // Only admin can access
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
            'description' => 'nullable|string',
        ]);

        Permission::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
        ]);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Permission created successfully.');
    }

    /**
     * Delete a permission.
     */
    public function destroyPermission(Permission $permission): RedirectResponse
    {
        // Only admin can access
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $permission->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Permission deleted successfully.');
    }

    /**
     * Create a new page permission.
     */
    public function storePagePermission(Request $request): RedirectResponse
    {
        // Only admin can access
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'page_route' => 'required|string|max:255|unique:page_permissions,page_route',
            'page_name' => 'required|string|max:255',
        ]);

        PagePermission::create([
            'page_route' => $request->page_route,
            'page_name' => $request->page_name,
        ]);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Page permission created successfully.');
    }

    /**
     * Delete a page permission.
     */
    public function destroyPagePermission(PagePermission $pagePermission): RedirectResponse
    {
        // Only admin can access
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $pagePermission->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Page permission deleted successfully.');
    }
}
