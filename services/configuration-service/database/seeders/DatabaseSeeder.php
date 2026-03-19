<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\FeatureFlag;
use App\Models\FormDefinition;
use App\Models\ModuleRegistry;
use App\Models\TenantConfiguration;
use App\Models\WorkflowDefinition;
use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SystemConfigurationSeeder::class,
            SystemFeatureFlagSeeder::class,
            SystemWorkflowSeeder::class,
            SystemFormDefinitionSeeder::class,
            SystemModuleSeeder::class,
        ]);
    }
}

class SystemConfigurationSeeder extends Seeder
{
    private string $demoTenantId = '00000000-0000-0000-0000-000000000001';

    private array $configs = [
        ['service' => 'inventory',  'key' => 'stock.reorder_notification_enabled', 'type' => 'boolean', 'value' => true,  'desc' => 'Enable low-stock reorder notifications'],
        ['service' => 'inventory',  'key' => 'stock.default_uom',                  'type' => 'string',  'value' => 'each', 'desc' => 'Default unit of measure'],
        ['service' => 'inventory',  'key' => 'stock.valuation_method',             'type' => 'string',  'value' => 'fifo', 'desc' => 'Stock valuation method: fifo, lifo, weighted_avg'],
        ['service' => 'orders',     'key' => 'order.auto_confirm_threshold',       'type' => 'integer', 'value' => 0,     'desc' => 'Auto-confirm orders below this amount (0 = disabled)'],
        ['service' => 'orders',     'key' => 'order.approval_required_above',      'type' => 'integer', 'value' => 10000, 'desc' => 'Manual approval required for orders above this value'],
        ['service' => 'orders',     'key' => 'order.currency_default',             'type' => 'string',  'value' => 'USD', 'desc' => 'Default order currency'],
        ['service' => 'finance',    'key' => 'finance.tax_rate_default',           'type' => 'string',  'value' => '0.0000', 'desc' => 'Default tax rate (4 decimal places)'],
        ['service' => 'finance',    'key' => 'finance.rounding_mode',              'type' => 'string',  'value' => 'half_up', 'desc' => 'BCMath rounding mode'],
        ['service' => 'crm',        'key' => 'crm.lead_scoring_enabled',           'type' => 'boolean', 'value' => true,  'desc' => 'Enable AI-based lead scoring'],
        ['service' => 'products',   'key' => 'product.sku_prefix',                 'type' => 'string',  'value' => 'SKU-', 'desc' => 'Auto-generated SKU prefix'],
        ['service' => 'products',   'key' => 'product.barcode_format',             'type' => 'string',  'value' => 'ean13', 'desc' => 'Default barcode format'],
        ['service' => 'auth',       'key' => 'auth.max_devices_per_user',          'type' => 'integer', 'value' => 10,    'desc' => 'Maximum concurrent device sessions per user'],
        ['service' => 'auth',       'key' => 'auth.session_timeout_minutes',       'type' => 'integer', 'value' => 480,   'desc' => 'Idle session timeout in minutes'],
    ];

    public function run(): void
    {
        foreach ($this->configs as $config) {
            TenantConfiguration::firstOrCreate(
                [
                    'tenant_id'    => $this->demoTenantId,
                    'service_name' => $config['service'],
                    'config_key'   => $config['key'],
                ],
                [
                    'id'           => Uuid::uuid4()->toString(),
                    'config_value' => ['value' => $config['value']],
                    'config_type'  => $config['type'],
                    'is_active'    => true,
                    'description'  => $config['desc'],
                ],
            );
        }

        $this->command->info('✓ System configurations seeded.');
    }
}

class SystemFeatureFlagSeeder extends Seeder
{
    private string $demoTenantId = '00000000-0000-0000-0000-000000000001';

    private array $flags = [
        ['key' => 'feature.inventory.pharma_compliance',   'enabled' => false, 'desc' => 'Enable pharmaceutical compliance mode (lot/expiry mandatory)'],
        ['key' => 'feature.inventory.serial_tracking',     'enabled' => true,  'desc' => 'Enable serial number tracking'],
        ['key' => 'feature.orders.backorder_support',      'enabled' => true,  'desc' => 'Allow backorders when stock is insufficient'],
        ['key' => 'feature.orders.drop_shipping',          'enabled' => false, 'desc' => 'Enable drop-shipping from suppliers directly to customers'],
        ['key' => 'feature.crm.ai_lead_scoring',           'enabled' => false, 'desc' => 'Enable AI-powered lead scoring'],
        ['key' => 'feature.finance.multi_currency',        'enabled' => true,  'desc' => 'Enable multi-currency transactions'],
        ['key' => 'feature.finance.auto_reconciliation',   'enabled' => false, 'desc' => 'Enable automatic bank reconciliation'],
        ['key' => 'feature.auth.sso',                      'enabled' => true,  'desc' => 'Enable Single Sign-On'],
        ['key' => 'feature.workflow.email_notifications',  'enabled' => true,  'desc' => 'Send email notifications on workflow state transitions'],
        ['key' => 'feature.reporting.advanced_analytics',  'enabled' => false, 'desc' => 'Enable advanced analytics dashboard', 'rollout' => 0],
    ];

    public function run(): void
    {
        foreach ($this->flags as $flag) {
            FeatureFlag::firstOrCreate(
                ['tenant_id' => $this->demoTenantId, 'flag_key' => $flag['key']],
                [
                    'id'                 => Uuid::uuid4()->toString(),
                    'is_enabled'         => $flag['enabled'],
                    'rollout_percentage' => $flag['rollout'] ?? 100,
                    'description'        => $flag['desc'],
                ],
            );
        }

        $this->command->info('✓ System feature flags seeded.');
    }
}

class SystemWorkflowSeeder extends Seeder
{
    private string $demoTenantId = '00000000-0000-0000-0000-000000000001';

    public function run(): void
    {
        // Sales Order Workflow
        WorkflowDefinition::firstOrCreate(
            ['tenant_id' => $this->demoTenantId, 'name' => 'Sales Order Approval'],
            [
                'id'          => Uuid::uuid4()->toString(),
                'entity_type' => 'order',
                'states'      => [
                    ['name' => 'draft',    'label' => 'Draft',       'initial' => true,  'final' => false],
                    ['name' => 'pending',  'label' => 'Pending',     'initial' => false, 'final' => false],
                    ['name' => 'approved', 'label' => 'Approved',    'initial' => false, 'final' => false],
                    ['name' => 'invoiced', 'label' => 'Invoiced',    'initial' => false, 'final' => false],
                    ['name' => 'paid',     'label' => 'Paid',        'initial' => false, 'final' => true],
                    ['name' => 'cancelled','label' => 'Cancelled',   'initial' => false, 'final' => true],
                ],
                'transitions' => [
                    ['from' => 'draft',    'to' => 'pending',   'event' => 'submit',   'label' => 'Submit for Approval'],
                    ['from' => 'pending',  'to' => 'approved',  'event' => 'approve',  'label' => 'Approve Order'],
                    ['from' => 'pending',  'to' => 'cancelled', 'event' => 'reject',   'label' => 'Reject Order'],
                    ['from' => 'approved', 'to' => 'invoiced',  'event' => 'invoice',  'label' => 'Generate Invoice'],
                    ['from' => 'invoiced', 'to' => 'paid',      'event' => 'pay',      'label' => 'Mark as Paid'],
                    ['from' => 'draft',    'to' => 'cancelled', 'event' => 'cancel',   'label' => 'Cancel Order'],
                ],
                'guards'    => [
                    ['event' => 'approve', 'permission' => 'orders.approve'],
                ],
                'actions'   => [
                    ['on_event' => 'approve', 'action' => 'notify_requester'],
                    ['on_event' => 'invoice', 'action' => 'generate_invoice_pdf'],
                    ['on_event' => 'pay',     'action' => 'post_journal_entry'],
                ],
                'is_active' => true,
                'version'   => 1,
            ],
        );

        // Purchase Request Workflow
        WorkflowDefinition::firstOrCreate(
            ['tenant_id' => $this->demoTenantId, 'name' => 'Purchase Request Approval'],
            [
                'id'          => Uuid::uuid4()->toString(),
                'entity_type' => 'purchase_request',
                'states'      => [
                    ['name' => 'draft',    'label' => 'Draft',    'initial' => true,  'final' => false],
                    ['name' => 'pending',  'label' => 'Pending',  'initial' => false, 'final' => false],
                    ['name' => 'approved', 'label' => 'Approved', 'initial' => false, 'final' => true],
                    ['name' => 'rejected', 'label' => 'Rejected', 'initial' => false, 'final' => true],
                ],
                'transitions' => [
                    ['from' => 'draft',   'to' => 'pending',  'event' => 'submit'],
                    ['from' => 'pending', 'to' => 'approved', 'event' => 'approve'],
                    ['from' => 'pending', 'to' => 'rejected', 'event' => 'reject'],
                ],
                'guards'    => null,
                'actions'   => null,
                'is_active' => true,
                'version'   => 1,
            ],
        );

        $this->command->info('✓ System workflow definitions seeded.');
    }
}

class SystemFormDefinitionSeeder extends Seeder
{
    private string $demoTenantId = '00000000-0000-0000-0000-000000000001';

    public function run(): void
    {
        FormDefinition::firstOrCreate(
            ['tenant_id' => $this->demoTenantId, 'service_name' => 'products', 'entity_type' => 'product', 'version' => 1],
            [
                'id'     => Uuid::uuid4()->toString(),
                'fields' => [
                    ['name' => 'name',        'type' => 'text',     'label' => 'Product Name',   'required' => true,  'order' => 1],
                    ['name' => 'sku',         'type' => 'text',     'label' => 'SKU',             'required' => true,  'order' => 2],
                    ['name' => 'description', 'type' => 'textarea', 'label' => 'Description',     'required' => false, 'order' => 3],
                    ['name' => 'price',       'type' => 'number',   'label' => 'Selling Price',   'required' => true,  'order' => 4],
                    ['name' => 'cost',        'type' => 'number',   'label' => 'Cost Price',      'required' => false, 'order' => 5],
                    ['name' => 'uom',         'type' => 'select',   'label' => 'Unit of Measure', 'required' => true,  'order' => 6],
                    ['name' => 'category',    'type' => 'select',   'label' => 'Category',        'required' => false, 'order' => 7],
                    ['name' => 'is_active',   'type' => 'checkbox', 'label' => 'Active',          'required' => false, 'order' => 8],
                ],
                'validations' => [
                    'name'  => ['required', 'max:255'],
                    'sku'   => ['required', 'max:100'],
                    'price' => ['required', 'numeric', 'min:0'],
                ],
                'is_active' => true,
                'version'   => 1,
            ],
        );

        $this->command->info('✓ System form definitions seeded.');
    }
}

class SystemModuleSeeder extends Seeder
{
    private string $demoTenantId = '00000000-0000-0000-0000-000000000001';

    private array $modules = [
        ['name' => 'Inventory Management',      'key' => 'module.inventory',         'enabled' => true,  'deps' => [],                        'ver' => '1.0.0'],
        ['name' => 'Warehouse Management',       'key' => 'module.warehouse',         'enabled' => true,  'deps' => ['module.inventory'],      'ver' => '1.0.0'],
        ['name' => 'Order Management',           'key' => 'module.orders',            'enabled' => true,  'deps' => [],                        'ver' => '1.0.0'],
        ['name' => 'Customer Relationship',      'key' => 'module.crm',               'enabled' => true,  'deps' => [],                        'ver' => '1.0.0'],
        ['name' => 'Finance & Accounting',       'key' => 'module.finance',           'enabled' => true,  'deps' => [],                        'ver' => '1.0.0'],
        ['name' => 'Procurement',                'key' => 'module.procurement',       'enabled' => true,  'deps' => ['module.inventory'],      'ver' => '1.0.0'],
        ['name' => 'Product Catalogue',          'key' => 'module.products',          'enabled' => true,  'deps' => [],                        'ver' => '1.0.0'],
        ['name' => 'Point of Sale',              'key' => 'module.pos',               'enabled' => false, 'deps' => ['module.orders', 'module.products'], 'ver' => '1.0.0'],
        ['name' => 'Advanced Reporting',         'key' => 'module.reporting',         'enabled' => true,  'deps' => [],                        'ver' => '1.0.0'],
        ['name' => 'Pharmaceutical Compliance',  'key' => 'module.pharma_compliance', 'enabled' => false, 'deps' => ['module.inventory'],      'ver' => '1.0.0'],
    ];

    public function run(): void
    {
        foreach ($this->modules as $module) {
            ModuleRegistry::firstOrCreate(
                ['tenant_id' => $this->demoTenantId, 'module_key' => $module['key']],
                [
                    'id'           => Uuid::uuid4()->toString(),
                    'module_name'  => $module['name'],
                    'is_enabled'   => $module['enabled'],
                    'dependencies' => $module['deps'],
                    'version'      => $module['ver'],
                ],
            );
        }

        $this->command->info('✓ System module registry seeded.');
    }
}
