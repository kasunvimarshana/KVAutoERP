# Authorization Permissions

This document lists the canonical RBAC permission names used by controller-level authorization through `AuthorizedController`.

The shared controller now resolves only canonical permission keys (plus the raw controller ability and Laravel Gate/policy fallback), so these names should be seeded and assigned to roles.

## Tenant

- `tenants.view`
- `tenants.create`
- `tenants.update`
- `tenants.delete`
- `tenants.update_config`
- `tenants.view_attachments`
- `tenants.upload_attachment`
- `tenants.delete_attachment`
- `tenant_attachments.view`

## Organization Unit

- `organization_units.view`
- `organization_units.create`
- `organization_units.update`
- `organization_units.delete`
- `organization_units.move`
- `organization_units.view_attachments`
- `organization_units.upload_attachment`
- `organization_units.delete_attachment`
- `organization_unit_attachments.view`

## User

- `users.view`
- `users.create`
- `users.update`
- `users.delete`
- `users.assign_role`
- `users.update_preferences`
- `users.view_attachments`
- `users.upload_attachment`
- `users.delete_attachment`
- `user_attachments.view`

## Role

- `roles.view`
- `roles.create`
- `roles.delete`
- `roles.sync_permissions`

## Permission

- `permissions.view`
- `permissions.create`
- `permissions.delete`

## Endpoint Mapping

### TenantController

- `GET /api/tenants` -> `tenants.view`
- `POST /api/tenants` -> `tenants.create`
- `GET /api/tenants/{id}` -> `tenants.view`
- `PUT /api/tenants/{id}` -> `tenants.update`
- `PATCH /api/tenants/{id}/config` -> `tenants.update_config`
- `DELETE /api/tenants/{id}` -> `tenants.delete`

### TenantAttachmentController

- `GET /api/tenants/{tenant}/attachments` -> `tenants.view_attachments`
- `POST /api/tenants/{tenant}/attachments` -> `tenants.upload_attachment`
- `DELETE /api/tenants/{tenant}/attachments/{attachment}` -> `tenants.delete_attachment`
- `GET /api/storage/tenant-attachments/{uuid}` -> `tenant_attachments.view`

### OrganizationUnitController

- `GET /api/org-units` -> `organization_units.view`
- `POST /api/org-units` -> `organization_units.create`
- `GET /api/org-units/{id}` -> `organization_units.view`
- `PUT /api/org-units/{id}` -> `organization_units.update`
- `DELETE /api/org-units/{id}` -> `organization_units.delete`
- `GET /api/org-units/tree` -> `organization_units.view`
- `PATCH /api/org-units/{id}/move` -> `organization_units.move`

### OrganizationUnitAttachmentController

- `GET /api/org-units/{unit}/attachments` -> `organization_units.view_attachments`
- `POST /api/org-units/{unit}/attachments` -> `organization_units.upload_attachment`
- `DELETE /api/org-units/{unit}/attachments/{attachment}` -> `organization_units.delete_attachment`
- `GET /api/storage/org-unit-attachments/{uuid}` -> `organization_unit_attachments.view`

### UserController

- `GET /api/users` -> `users.view`
- `POST /api/users` -> `users.create`
- `GET /api/users/{id}` -> `users.view`
- `PUT /api/users/{id}` -> `users.update`
- `DELETE /api/users/{id}` -> `users.delete`
- `POST /api/users/{id}/assign-role` -> `users.assign_role`
- `PATCH /api/users/{id}/preferences` -> `users.update_preferences`

### UserAttachmentController

- `GET /api/users/{user}/attachments` -> `users.view_attachments`
- `POST /api/users/{user}/attachments` -> `users.upload_attachment`
- `DELETE /api/users/{user}/attachments/{attachment}` -> `users.delete_attachment`
- `GET /api/storage/user-attachments/{uuid}` -> `user_attachments.view`

### RoleController

- `GET /api/roles` -> `roles.view`
- `GET /api/roles/{id}` -> `roles.view`
- `POST /api/roles` -> `roles.create`
- `DELETE /api/roles/{id}` -> `roles.delete`
- `PUT /api/roles/{id}/permissions` -> `roles.sync_permissions`

### PermissionController

- `GET /api/permissions` -> `permissions.view`
- `GET /api/permissions/{id}` -> `permissions.view`
- `POST /api/permissions` -> `permissions.create`
- `DELETE /api/permissions/{id}` -> `permissions.delete`
