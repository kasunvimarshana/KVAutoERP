<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Location;
use App\Models\Organisation;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Ramsey\Uuid\Uuid;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            TenantSeeder::class,
        ]);
    }
}

class PermissionSeeder extends Seeder
{
    private array $permissions = [
        // Auth permissions
        'auth.users.view'         => ['group' => 'auth', 'display_name' => 'View Users'],
        'auth.users.create'       => ['group' => 'auth', 'display_name' => 'Create Users'],
        'auth.users.edit'         => ['group' => 'auth', 'display_name' => 'Edit Users'],
        'auth.users.delete'       => ['group' => 'auth', 'display_name' => 'Delete Users'],
        'auth.roles.manage'       => ['group' => 'auth', 'display_name' => 'Manage Roles'],
        'auth.permissions.manage' => ['group' => 'auth', 'display_name' => 'Manage Permissions'],
        'auth.sessions.manage'    => ['group' => 'auth', 'display_name' => 'Manage Sessions'],
        // Inventory permissions
        'inventory.view'          => ['group' => 'inventory', 'display_name' => 'View Inventory'],
        'inventory.create'        => ['group' => 'inventory', 'display_name' => 'Create Inventory'],
        'inventory.edit'          => ['group' => 'inventory', 'display_name' => 'Edit Inventory'],
        'inventory.delete'        => ['group' => 'inventory', 'display_name' => 'Delete Inventory'],
        'inventory.*'             => ['group' => 'inventory', 'display_name' => 'Full Inventory Access'],
        // Product permissions
        'products.view'           => ['group' => 'products', 'display_name' => 'View Products'],
        'products.create'         => ['group' => 'products', 'display_name' => 'Create Products'],
        'products.edit'           => ['group' => 'products', 'display_name' => 'Edit Products'],
        'products.delete'         => ['group' => 'products', 'display_name' => 'Delete Products'],
        // Order permissions
        'orders.view'             => ['group' => 'orders', 'display_name' => 'View Orders'],
        'orders.create'           => ['group' => 'orders', 'display_name' => 'Create Orders'],
        'orders.edit'             => ['group' => 'orders', 'display_name' => 'Edit Orders'],
        'orders.approve'          => ['group' => 'orders', 'display_name' => 'Approve Orders'],
        // Finance permissions
        'finance.view'            => ['group' => 'finance', 'display_name' => 'View Finance'],
        'finance.transactions'    => ['group' => 'finance', 'display_name' => 'Manage Transactions'],
        'finance.reports'         => ['group' => 'finance', 'display_name' => 'View Financial Reports'],
    ];

    public function run(): void
    {
        foreach ($this->permissions as $name => $meta) {
            Permission::firstOrCreate(['name' => $name], [
                'id'           => Uuid::uuid4()->toString(),
                'display_name' => $meta['display_name'],
                'group'        => $meta['group'],
                'guard_name'   => 'api',
                'is_system'    => true,
            ]);
        }
    }
}

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        // Create the demo tenant
        $tenant = Tenant::firstOrCreate(['slug' => 'acme-corp'], [
            'id'        => Uuid::uuid4()->toString(),
            'name'      => 'ACME Corporation',
            'slug'      => 'acme-corp',
            'is_active' => true,
            'plan'      => 'enterprise',
            'feature_flags' => [
                'sso_enabled'                => true,
                'multi_device_sessions'      => true,
                'suspicious_activity_alerts' => true,
                'audit_logging'              => true,
                'rate_limiting'              => true,
            ],
            'token_lifetimes' => ['access' => 15, 'refresh' => 43200],
        ]);

        // Tenant hierarchy
        $org = Organisation::firstOrCreate(['tenant_id' => $tenant->id, 'code' => 'HQ'], [
            'id'        => Uuid::uuid4()->toString(),
            'name'      => 'Headquarters',
            'is_active' => true,
        ]);

        $branch = Branch::firstOrCreate(['organisation_id' => $org->id, 'code' => 'MAIN'], [
            'id'              => Uuid::uuid4()->toString(),
            'tenant_id'       => $tenant->id,
            'name'            => 'Main Branch',
            'is_active'       => true,
        ]);

        $location = Location::firstOrCreate(['branch_id' => $branch->id, 'code' => 'WH1'], [
            'id'              => Uuid::uuid4()->toString(),
            'tenant_id'       => $tenant->id,
            'organisation_id' => $org->id,
            'name'            => 'Warehouse 1',
            'is_active'       => true,
        ]);

        // System roles
        $adminRole = Role::firstOrCreate(['tenant_id' => $tenant->id, 'name' => 'admin'], [
            'id'          => Uuid::uuid4()->toString(),
            'display_name' => 'Administrator',
            'guard_name'  => 'api',
            'is_system'   => true,
            'is_active'   => true,
        ]);

        $viewerRole = Role::firstOrCreate(['tenant_id' => $tenant->id, 'name' => 'viewer'], [
            'id'          => Uuid::uuid4()->toString(),
            'display_name' => 'Viewer',
            'guard_name'  => 'api',
            'is_system'   => true,
            'is_active'   => true,
        ]);

        // Assign all permissions to admin
        $allPermissions = Permission::all();
        $adminRole->permissions()->sync($allPermissions->pluck('id')->toArray());

        // Assign read-only permissions to viewer
        $viewPermissions = Permission::where('name', 'like', '%.view')->get();
        $viewerRole->permissions()->sync($viewPermissions->pluck('id')->toArray());

        // Super admin user
        $adminUser = User::firstOrCreate(['tenant_id' => $tenant->id, 'email' => 'admin@acme-corp.com'], [
            'id'                  => Uuid::uuid4()->toString(),
            'organisation_id'     => $org->id,
            'branch_id'           => $branch->id,
            'name'                => 'Super Admin',
            'password'            => Hash::make('Admin@12345!'),
            'is_active'           => true,
            'email_verified_at'   => now(),
            'password_changed_at' => now(),
        ]);

        $adminUser->roles()->syncWithoutDetaching([$adminRole->id]);

        $this->command->info('✓ Demo tenant "acme-corp" seeded successfully.');
        $this->command->info('  Admin credentials: admin@acme-corp.com / Admin@12345!');
        $this->command->info('  Tenant ID: ' . $tenant->id);
    }
}
