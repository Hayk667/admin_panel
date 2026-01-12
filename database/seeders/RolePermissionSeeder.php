<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $userRole = Role::firstOrCreate(
            ['slug' => 'user'],
            [
                'name' => 'User',
                'description' => 'Default user role. Can edit only own posts, but can see others.',
            ]
        );

        $moderatorRole = Role::firstOrCreate(
            ['slug' => 'moderator'],
            [
                'name' => 'Moderator',
                'description' => 'Can edit everyone\'s posts, but delete can only be done by admin and the owner.',
            ]
        );

        $adminRole = Role::firstOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Admin',
                'description' => 'Can delete and edit everything. Has all permissions.',
            ]
        );

        // Create permissions
        $permissions = [
            [
                'name' => 'Edit Any Post',
                'slug' => 'edit_any_post',
                'description' => 'Allows editing posts created by other users',
            ],
            [
                'name' => 'Delete Any Post',
                'slug' => 'delete_any_post',
                'description' => 'Allows deleting posts created by other users. Used in User::canDeletePost() method.',
            ],
            [
                'name' => 'Delete Category',
                'slug' => 'delete_category',
                'description' => 'Allows deleting categories. Used in CategoryController::destroy() method.',
            ],
            [
                'name' => 'Delete Language',
                'slug' => 'delete_language',
                'description' => 'Allows deleting languages. Used in LanguageController::destroy() method.',
            ],
            [
                'name' => 'Manage Categories',
                'slug' => 'manage_categories',
                'description' => 'Allows creating, editing, and deleting categories',
            ],
            [
                'name' => 'Manage Languages',
                'slug' => 'manage_languages',
                'description' => 'Allows creating, editing, and deleting languages',
            ],
            [
                'name' => 'Manage Users',
                'slug' => 'manage_users',
                'description' => 'Allows viewing and editing user information',
            ],
            [
                'name' => 'Manage Roles',
                'slug' => 'manage_roles',
                'description' => 'Allows managing roles and permissions',
            ],
        ];

        foreach ($permissions as $permissionData) {
            Permission::firstOrCreate(
                ['slug' => $permissionData['slug']],
                $permissionData
            );
        }

        // Assign permissions to moderator role
        $editAnyPostPermission = Permission::where('slug', 'edit_any_post')->first();
        if ($editAnyPostPermission && !$moderatorRole->permissions->contains($editAnyPostPermission->id)) {
            $moderatorRole->permissions()->attach($editAnyPostPermission->id);
        }

        // Create admin user
        User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin',
                'email' => 'admin@admin.com',
                'password' => Hash::make('admin1234'),
                'role_id' => $adminRole->id,
                'email_verified_at' => now(),
            ]
        );

        // Note: Admin role doesn't need explicit permissions as it has all permissions by default
        // Users don't get any permissions by default - they can only edit their own posts
    }
}

