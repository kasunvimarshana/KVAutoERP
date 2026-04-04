<?php

return [
    // Core
    Modules\Core\Infrastructure\Providers\CoreServiceProvider::class,
    // Identity & Access
    Modules\Tenant\Infrastructure\Providers\TenantServiceProvider::class,
    Modules\User\Infrastructure\Providers\UserServiceProvider::class,
    Modules\Authorization\Infrastructure\Providers\AuthorizationServiceProvider::class,
    Modules\Configuration\Infrastructure\Providers\ConfigurationServiceProvider::class,
    // Party modules
    Modules\Supplier\Infrastructure\Providers\SupplierServiceProvider::class,
    Modules\Customer\Infrastructure\Providers\CustomerServiceProvider::class,
    // Catalogue
    Modules\Warehouse\Infrastructure\Providers\WarehouseServiceProvider::class,
    Modules\Product\Infrastructure\Providers\ProductServiceProvider::class,
    Modules\UoM\Infrastructure\Providers\UomServiceProvider::class,
    Modules\GS1\Infrastructure\Providers\GS1ServiceProvider::class,
    Modules\Barcode\Infrastructure\Providers\BarcodeServiceProvider::class,
    Modules\Pricing\Infrastructure\Providers\PricingServiceProvider::class,
    // Inventory
    Modules\Inventory\Infrastructure\Providers\InventoryServiceProvider::class,
    // Procurement
    Modules\PurchaseOrder\Infrastructure\Providers\PurchaseOrderServiceProvider::class,
    Modules\GoodsReceipt\Infrastructure\Providers\GoodsReceiptServiceProvider::class,
    // Sales & Fulfilment
    Modules\SalesOrder\Infrastructure\Providers\SalesOrderServiceProvider::class,
    Modules\Dispatch\Infrastructure\Providers\DispatchServiceProvider::class,
    // Operations
    Modules\StockMovement\Infrastructure\Providers\StockMovementServiceProvider::class,
    Modules\Returns\Infrastructure\Providers\ReturnsServiceProvider::class,
    // Cross-cutting
    Modules\Attachment\Infrastructure\Providers\AttachmentServiceProvider::class,
    Modules\Accounting\Infrastructure\Providers\AccountingServiceProvider::class,
    // HR
    Modules\HR\Infrastructure\Providers\HRServiceProvider::class,
    // Notifications
    Modules\Notification\Infrastructure\Providers\NotificationServiceProvider::class,
];
