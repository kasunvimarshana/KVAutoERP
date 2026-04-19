# Schema vs Domain Audit Report

## Model: AuditLogModel.php (Table: audit_logs)
- **Missing in Model $fillable**: occurred_at

## Model: JournalEntryLineModel.php (Table: journal_entry_lines)
- **Missing in Entity**: journal_entry_id (journalEntryId)

## Model: OrganizationUnitAttachmentModel.php (Table: org_unit_attachments)
- **Missing in Entity**: org_unit_id (orgUnitId)

## Model: OrganizationUnitModel.php (Table: org_units)
- **Missing in Model $fillable**: image_path, default_revenue_account_id, default_expense_account_id, default_asset_account_id, default_liability_account_id, warehouse_id
- **Missing in Entity**: image_path (imagePath), _lft (lft), _rgt (rgt), default_revenue_account_id (defaultRevenueAccountId), default_expense_account_id (defaultExpenseAccountId), default_asset_account_id (defaultAssetAccountId), default_liability_account_id (defaultLiabilityAccountId), warehouse_id (warehouseId)

## Model: OrganizationUnitUserModel.php (Table: org_unit_users)
- **Missing in Entity**: org_unit_id (orgUnitId)

## Model: ProductBrandModel.php (Table: product_brands)
- **Missing in Model $fillable**: image_path
- **Missing in Entity**: image_path (imagePath)

## Model: ProductCategoryModel.php (Table: product_categories)
- **Missing in Model $fillable**: image_path
- **Missing in Entity**: image_path (imagePath)

## Model: ProductModel.php (Table: products)
- **Missing in Model $fillable**: image_path
- **Missing in Entity**: image_path (imagePath)

## Model: PermissionModel.php (Table: permissions)
- **Missing in Model $fillable**: module, description
- **Missing in Entity**: guard_name (guardName), module (module), description (description)

## Model: RoleModel.php (Table: roles)
- **Missing in Model $fillable**: description
- **Missing in Entity**: guard_name (guardName), description (description)

## Model: UserModel.php (Table: users)
- **Missing in Model $fillable**: email_verified_at, remember_token
- **Missing in Entity**: email_verified_at (emailVerifiedAt), password (password), status (status), remember_token (rememberToken)

