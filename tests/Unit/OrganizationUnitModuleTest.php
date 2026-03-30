<?php

namespace Tests\Unit;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Modules\OrganizationUnit\Application\Contracts\AttachmentStorageStrategyInterface;
use Modules\OrganizationUnit\Application\Contracts\BulkUploadOrganizationUnitAttachmentsServiceInterface;
use Modules\OrganizationUnit\Application\Contracts\CreateOrganizationUnitServiceInterface;
use Modules\OrganizationUnit\Application\Contracts\DeleteOrganizationUnitAttachmentServiceInterface;
use Modules\OrganizationUnit\Application\Contracts\DeleteOrganizationUnitServiceInterface;
use Modules\OrganizationUnit\Application\Contracts\FindOrganizationUnitAttachmentsServiceInterface;
use Modules\OrganizationUnit\Application\Contracts\FindOrganizationUnitServiceInterface;
use Modules\OrganizationUnit\Application\Contracts\MoveOrganizationUnitServiceInterface;
use Modules\OrganizationUnit\Application\Contracts\UpdateOrganizationUnitServiceInterface;
use Modules\OrganizationUnit\Application\Contracts\UploadOrganizationUnitAttachmentServiceInterface;
use Modules\OrganizationUnit\Application\Services\BulkUploadOrganizationUnitAttachmentsService;
use Modules\OrganizationUnit\Application\Services\DeleteOrganizationUnitAttachmentService;
use Modules\OrganizationUnit\Application\Services\FindOrganizationUnitAttachmentsService;
use Modules\OrganizationUnit\Application\Services\FindOrganizationUnitService;
use Modules\OrganizationUnit\Application\Services\UploadOrganizationUnitAttachmentService;
use Modules\OrganizationUnit\Domain\Entities\OrganizationUnit;
use Modules\OrganizationUnit\Domain\Entities\OrganizationUnitAttachment;
use Modules\OrganizationUnit\Domain\Exceptions\AttachmentNotFoundException;
use Modules\OrganizationUnit\Domain\Exceptions\OrganizationUnitNotFoundException;
use Modules\OrganizationUnit\Domain\RepositoryInterfaces\OrganizationUnitAttachmentRepositoryInterface;
use Modules\OrganizationUnit\Domain\RepositoryInterfaces\OrganizationUnitRepositoryInterface;
use Modules\OrganizationUnit\Infrastructure\Http\Controllers\OrganizationUnitAttachmentController;
use Modules\OrganizationUnit\Infrastructure\Http\Controllers\OrganizationUnitController;
use Modules\OrganizationUnit\Infrastructure\Http\Requests\UploadOrganizationUnitAttachmentRequest;
use Modules\OrganizationUnit\Infrastructure\Http\Resources\OrganizationUnitAttachmentResource;
use Modules\OrganizationUnit\Infrastructure\Providers\OrganizationUnitServiceProvider;
use Modules\OrganizationUnit\Infrastructure\Storage\DefaultAttachmentStorageStrategy;
use Modules\Core\Domain\ValueObjects\Name;
use PHPUnit\Framework\TestCase;

class OrganizationUnitModuleTest extends TestCase
{
    // ── Helpers ───────────────────────────────────────────────────────────────

    private function createTestUnit(int $id = 1, int $tenantId = 1): OrganizationUnit
    {
        $unit = new OrganizationUnit(tenantId: $tenantId, name: new Name('Test Unit'));
        $ref  = new \ReflectionProperty($unit, 'id');
        $ref->setAccessible(true);
        $ref->setValue($unit, $id);

        return $unit;
    }

    private function createTestAttachment(
        int $id = 1,
        int $orgUnitId = 1,
        int $tenantId = 1
    ): OrganizationUnitAttachment {
        $attachment = new OrganizationUnitAttachment(
            tenantId:           $tenantId,
            organizationUnitId: $orgUnitId,
            uuid:               'test-uuid-' . $id,
            name:               'test-file.pdf',
            filePath:           "org-units/{$orgUnitId}/test-file.pdf",
            mimeType:           'application/pdf',
            size:               1024,
            type:               null,
            metadata:           null,
            id:                 $id,
        );

        return $attachment;
    }

    // ── AttachmentStorageStrategyInterface ────────────────────────────────────

    public function test_attachment_storage_strategy_interface_exists(): void
    {
        $this->assertTrue(interface_exists(AttachmentStorageStrategyInterface::class));
    }

    public function test_attachment_storage_strategy_interface_declares_required_methods(): void
    {
        $rc = new \ReflectionClass(AttachmentStorageStrategyInterface::class);

        $this->assertTrue($rc->hasMethod('store'));
        $this->assertTrue($rc->hasMethod('storeFromPath'));
        $this->assertTrue($rc->hasMethod('delete'));
        $this->assertTrue($rc->hasMethod('stream'));
    }

    // ── DefaultAttachmentStorageStrategy ─────────────────────────────────────

    public function test_default_attachment_storage_strategy_class_exists(): void
    {
        $this->assertTrue(class_exists(DefaultAttachmentStorageStrategy::class));
    }

    public function test_default_attachment_storage_strategy_implements_interface(): void
    {
        $this->assertTrue(
            is_subclass_of(
                DefaultAttachmentStorageStrategy::class,
                AttachmentStorageStrategyInterface::class
            ),
            'DefaultAttachmentStorageStrategy must implement AttachmentStorageStrategyInterface.'
        );
    }

    public function test_default_attachment_storage_strategy_store_delegates_to_file_storage(): void
    {
        $fileStorage = $this->createMock(\Modules\Core\Application\Contracts\FileStorageServiceInterface::class);
        $file        = $this->createMock(UploadedFile::class);

        $fileStorage->expects($this->once())
            ->method('storeFile')
            ->with($file, 'org-units/42')
            ->willReturn('org-units/42/doc.pdf');

        $strategy = new DefaultAttachmentStorageStrategy($fileStorage);
        $path     = $strategy->store($file, 42);

        $this->assertSame('org-units/42/doc.pdf', $path);
    }

    public function test_default_attachment_storage_strategy_delete_delegates_to_file_storage(): void
    {
        $fileStorage = $this->createMock(\Modules\Core\Application\Contracts\FileStorageServiceInterface::class);
        $fileStorage->expects($this->once())
            ->method('delete')
            ->with('org-units/42/doc.pdf')
            ->willReturn(true);

        $strategy = new DefaultAttachmentStorageStrategy($fileStorage);
        $this->assertTrue($strategy->delete('org-units/42/doc.pdf'));
    }

    // ── FindOrganizationUnitAttachmentsServiceInterface ───────────────────────

    public function test_find_org_unit_attachments_service_interface_exists(): void
    {
        $this->assertTrue(interface_exists(FindOrganizationUnitAttachmentsServiceInterface::class));
    }

    public function test_find_org_unit_attachments_service_interface_declares_required_methods(): void
    {
        $rc = new \ReflectionClass(FindOrganizationUnitAttachmentsServiceInterface::class);

        $this->assertTrue($rc->hasMethod('findByOrganizationUnit'));
        $this->assertTrue($rc->hasMethod('findByUuid'));
        $this->assertTrue($rc->hasMethod('find'));
    }

    // ── FindOrganizationUnitAttachmentsService ────────────────────────────────

    public function test_find_org_unit_attachments_service_class_exists(): void
    {
        $this->assertTrue(class_exists(FindOrganizationUnitAttachmentsService::class));
    }

    public function test_find_org_unit_attachments_service_implements_interface(): void
    {
        $this->assertTrue(
            is_subclass_of(FindOrganizationUnitAttachmentsService::class, FindOrganizationUnitAttachmentsServiceInterface::class),
            'FindOrganizationUnitAttachmentsService must implement FindOrganizationUnitAttachmentsServiceInterface.'
        );
    }

    public function test_find_org_unit_attachments_service_find_by_org_unit_delegates_to_repository(): void
    {
        $repo = $this->createMock(OrganizationUnitAttachmentRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('getByOrganizationUnit')
            ->with(7, 'invoice')
            ->willReturn(new Collection);

        $service = new FindOrganizationUnitAttachmentsService($repo);
        $result  = $service->findByOrganizationUnit(7, 'invoice');

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_find_org_unit_attachments_service_find_by_uuid_delegates_to_repository(): void
    {
        $uuid       = 'test-uuid-1234';
        $attachment = $this->createTestAttachment();

        $repo = $this->createMock(OrganizationUnitAttachmentRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('findByUuid')
            ->with($uuid)
            ->willReturn($attachment);

        $service = new FindOrganizationUnitAttachmentsService($repo);
        $this->assertSame($attachment, $service->findByUuid($uuid));
    }

    public function test_find_org_unit_attachments_service_find_returns_null_when_missing(): void
    {
        $repo = $this->createMock(OrganizationUnitAttachmentRepositoryInterface::class);
        $repo->method('find')->willReturn(null);

        $service = new FindOrganizationUnitAttachmentsService($repo);
        $this->assertNull($service->find(9999));
    }

    // ── BulkUploadOrganizationUnitAttachmentsServiceInterface ─────────────────

    public function test_bulk_upload_org_unit_attachments_service_interface_exists(): void
    {
        $this->assertTrue(interface_exists(BulkUploadOrganizationUnitAttachmentsServiceInterface::class));
    }

    // ── BulkUploadOrganizationUnitAttachmentsService ──────────────────────────

    public function test_bulk_upload_org_unit_attachments_service_class_exists(): void
    {
        $this->assertTrue(class_exists(BulkUploadOrganizationUnitAttachmentsService::class));
    }

    public function test_bulk_upload_org_unit_attachments_service_implements_interface(): void
    {
        $this->assertTrue(
            is_subclass_of(
                BulkUploadOrganizationUnitAttachmentsService::class,
                BulkUploadOrganizationUnitAttachmentsServiceInterface::class
            ),
            'BulkUploadOrganizationUnitAttachmentsService must implement BulkUploadOrganizationUnitAttachmentsServiceInterface.'
        );
    }

    public function test_bulk_upload_service_constructor_accepts_storage_strategy(): void
    {
        $orgUnitRepo = $this->createMock(OrganizationUnitRepositoryInterface::class);
        $attachRepo  = $this->createMock(OrganizationUnitAttachmentRepositoryInterface::class);
        $strategy    = $this->createMock(AttachmentStorageStrategyInterface::class);

        $service = new BulkUploadOrganizationUnitAttachmentsService($orgUnitRepo, $attachRepo, $strategy);

        $this->assertInstanceOf(BulkUploadOrganizationUnitAttachmentsServiceInterface::class, $service);
    }

    public function test_bulk_upload_service_has_correct_dependencies(): void
    {
        $rc = new \ReflectionClass(BulkUploadOrganizationUnitAttachmentsService::class);

        $paramTypes = [];
        foreach ($rc->getConstructor()->getParameters() as $param) {
            $paramTypes[] = $param->getType() ? $param->getType()->getName() : null;
        }

        $this->assertContains(OrganizationUnitRepositoryInterface::class, $paramTypes);
        $this->assertContains(OrganizationUnitAttachmentRepositoryInterface::class, $paramTypes);
        $this->assertContains(AttachmentStorageStrategyInterface::class, $paramTypes);
    }

    // ── UploadOrganizationUnitAttachmentService ───────────────────────────────

    public function test_upload_org_unit_attachment_service_uses_storage_strategy(): void
    {
        $rc = new \ReflectionClass(UploadOrganizationUnitAttachmentService::class);

        $strategyParam = null;
        foreach ($rc->getConstructor()->getParameters() as $param) {
            if ($param->getType() && $param->getType()->getName() === AttachmentStorageStrategyInterface::class) {
                $strategyParam = $param;
                break;
            }
        }

        $this->assertNotNull(
            $strategyParam,
            'UploadOrganizationUnitAttachmentService constructor must accept AttachmentStorageStrategyInterface.'
        );
    }

    public function test_upload_org_unit_attachment_service_no_longer_uses_file_storage_service_directly(): void
    {
        $rc = new \ReflectionClass(UploadOrganizationUnitAttachmentService::class);

        foreach ($rc->getConstructor()->getParameters() as $param) {
            $type = $param->getType() ? $param->getType()->getName() : null;
            $this->assertNotSame(
                \Modules\Core\Application\Contracts\FileStorageServiceInterface::class,
                $type,
                'UploadOrganizationUnitAttachmentService must not directly depend on FileStorageServiceInterface.'
            );
        }
    }

    public function test_upload_org_unit_attachment_service_throws_when_unit_not_found(): void
    {
        $this->expectException(OrganizationUnitNotFoundException::class);

        $orgUnitRepo = $this->createMock(OrganizationUnitRepositoryInterface::class);
        $orgUnitRepo->method('find')->willReturn(null);

        $attachRepo = $this->createMock(OrganizationUnitAttachmentRepositoryInterface::class);
        $strategy   = $this->createMock(AttachmentStorageStrategyInterface::class);

        $service = new UploadOrganizationUnitAttachmentService($orgUnitRepo, $attachRepo, $strategy);

        $file = $this->createMock(UploadedFile::class);
        $file->method('getClientOriginalName')->willReturn('test.pdf');
        $file->method('getMimeType')->willReturn('application/pdf');
        $file->method('getSize')->willReturn(1024);

        // Use Reflection to call protected handle() directly (avoids DB::transaction)
        $method = new \ReflectionMethod($service, 'handle');
        $method->setAccessible(true);
        $method->invoke($service, ['organization_unit_id' => 99, 'file' => $file]);
    }

    public function test_upload_org_unit_attachment_service_stores_file_via_strategy(): void
    {
        $unit = $this->createTestUnit(1, 1);

        $orgUnitRepo = $this->createMock(OrganizationUnitRepositoryInterface::class);
        $orgUnitRepo->method('find')->willReturn($unit);

        $savedAttachment = $this->createTestAttachment(1, 1);

        $attachRepo = $this->createMock(OrganizationUnitAttachmentRepositoryInterface::class);
        $attachRepo->expects($this->once())
            ->method('save')
            ->willReturn($savedAttachment);

        $strategy = $this->createMock(AttachmentStorageStrategyInterface::class);
        $strategy->expects($this->once())
            ->method('store')
            ->willReturn('org-units/1/file.pdf');

        $file = $this->createMock(UploadedFile::class);
        $file->method('getClientOriginalName')->willReturn('file.pdf');
        $file->method('getMimeType')->willReturn('application/pdf');
        $file->method('getSize')->willReturn(2048);

        $service = new UploadOrganizationUnitAttachmentService($orgUnitRepo, $attachRepo, $strategy);

        $method = new \ReflectionMethod($service, 'handle');
        $method->setAccessible(true);
        $result = $method->invoke($service, [
            'organization_unit_id' => 1,
            'file'                 => $file,
            'type'                 => null,
            'metadata'             => null,
        ]);

        $this->assertInstanceOf(OrganizationUnitAttachment::class, $result);
        $this->assertSame($savedAttachment, $result);
    }

    // ── DeleteOrganizationUnitAttachmentService ───────────────────────────────

    public function test_delete_org_unit_attachment_service_uses_storage_strategy(): void
    {
        $rc = new \ReflectionClass(DeleteOrganizationUnitAttachmentService::class);

        $strategyParam = null;
        foreach ($rc->getConstructor()->getParameters() as $param) {
            if ($param->getType() && $param->getType()->getName() === AttachmentStorageStrategyInterface::class) {
                $strategyParam = $param;
                break;
            }
        }

        $this->assertNotNull(
            $strategyParam,
            'DeleteOrganizationUnitAttachmentService constructor must accept AttachmentStorageStrategyInterface.'
        );
    }

    public function test_delete_org_unit_attachment_service_throws_when_not_found(): void
    {
        $this->expectException(AttachmentNotFoundException::class);

        $attachRepo = $this->createMock(OrganizationUnitAttachmentRepositoryInterface::class);
        $attachRepo->method('find')->willReturn(null);

        $strategy = $this->createMock(AttachmentStorageStrategyInterface::class);

        $service = new DeleteOrganizationUnitAttachmentService($attachRepo, $strategy);

        $method = new \ReflectionMethod($service, 'handle');
        $method->setAccessible(true);
        $method->invoke($service, ['attachment_id' => 99]);
    }

    public function test_delete_org_unit_attachment_service_deletes_file_and_record(): void
    {
        $attachment = $this->createTestAttachment(5, 1);

        $attachRepo = $this->createMock(OrganizationUnitAttachmentRepositoryInterface::class);
        $attachRepo->method('find')->willReturn($attachment);
        $attachRepo->expects($this->once())->method('delete')->with(5)->willReturn(true);

        $strategy = $this->createMock(AttachmentStorageStrategyInterface::class);
        $strategy->expects($this->once())
            ->method('delete')
            ->with('org-units/1/test-file.pdf');

        $service = new DeleteOrganizationUnitAttachmentService($attachRepo, $strategy);

        $method = new \ReflectionMethod($service, 'handle');
        $method->setAccessible(true);
        $result = $method->invoke($service, ['attachment_id' => 5]);

        $this->assertTrue($result);
    }

    // ── OrganizationUnitAttachmentController ──────────────────────────────────

    public function test_org_unit_attachment_controller_class_exists(): void
    {
        $this->assertTrue(class_exists(OrganizationUnitAttachmentController::class));
    }

    public function test_org_unit_attachment_controller_injects_find_service_not_repo_directly(): void
    {
        $rc = new \ReflectionClass(OrganizationUnitAttachmentController::class);

        $paramTypes = [];
        foreach ($rc->getConstructor()->getParameters() as $param) {
            $paramTypes[] = $param->getType() ? $param->getType()->getName() : null;
        }

        $this->assertContains(FindOrganizationUnitAttachmentsServiceInterface::class, $paramTypes,
            'Controller must inject FindOrganizationUnitAttachmentsServiceInterface.');

        $this->assertNotContains(\Modules\OrganizationUnit\Domain\RepositoryInterfaces\OrganizationUnitAttachmentRepositoryInterface::class, $paramTypes,
            'Controller must NOT directly inject OrganizationUnitAttachmentRepositoryInterface.');
    }

    public function test_org_unit_attachment_controller_injects_storage_strategy_not_file_storage_directly(): void
    {
        $rc = new \ReflectionClass(OrganizationUnitAttachmentController::class);

        $paramTypes = [];
        foreach ($rc->getConstructor()->getParameters() as $param) {
            $paramTypes[] = $param->getType() ? $param->getType()->getName() : null;
        }

        $this->assertContains(AttachmentStorageStrategyInterface::class, $paramTypes,
            'Controller must inject AttachmentStorageStrategyInterface.');

        $this->assertNotContains(\Modules\Core\Application\Contracts\FileStorageServiceInterface::class, $paramTypes,
            'Controller must NOT directly inject FileStorageServiceInterface.');
    }

    public function test_org_unit_attachment_controller_injects_bulk_upload_service(): void
    {
        $rc = new \ReflectionClass(OrganizationUnitAttachmentController::class);

        $paramTypes = [];
        foreach ($rc->getConstructor()->getParameters() as $param) {
            $paramTypes[] = $param->getType() ? $param->getType()->getName() : null;
        }

        $this->assertContains(BulkUploadOrganizationUnitAttachmentsServiceInterface::class, $paramTypes,
            'Controller must inject BulkUploadOrganizationUnitAttachmentsServiceInterface.');
    }

    public function test_org_unit_attachment_controller_has_store_many_method(): void
    {
        $rc = new \ReflectionClass(OrganizationUnitAttachmentController::class);
        $this->assertTrue($rc->hasMethod('storeMany'),
            'OrganizationUnitAttachmentController must have a storeMany() method for bulk uploads.');
    }

    // ── OrganizationUnitController ────────────────────────────────────────────

    public function test_org_unit_controller_class_exists(): void
    {
        $this->assertTrue(class_exists(OrganizationUnitController::class));
    }

    public function test_org_unit_controller_injects_find_service_not_repository_directly(): void
    {
        $rc = new \ReflectionClass(OrganizationUnitController::class);

        $paramTypes = [];
        foreach ($rc->getConstructor()->getParameters() as $param) {
            $paramTypes[] = $param->getType() ? $param->getType()->getName() : null;
        }

        $this->assertContains(FindOrganizationUnitServiceInterface::class, $paramTypes,
            'OrganizationUnitController must inject FindOrganizationUnitServiceInterface.');

        $this->assertNotContains(\Modules\OrganizationUnit\Domain\RepositoryInterfaces\OrganizationUnitRepositoryInterface::class, $paramTypes,
            'OrganizationUnitController must NOT directly inject OrganizationUnitRepositoryInterface.');
    }

    public function test_org_unit_controller_injects_create_service(): void
    {
        $rc = new \ReflectionClass(OrganizationUnitController::class);

        $paramTypes = [];
        foreach ($rc->getConstructor()->getParameters() as $param) {
            $paramTypes[] = $param->getType() ? $param->getType()->getName() : null;
        }

        $this->assertContains(CreateOrganizationUnitServiceInterface::class, $paramTypes,
            'OrganizationUnitController must inject CreateOrganizationUnitServiceInterface.');
    }

    public function test_org_unit_controller_injects_update_service(): void
    {
        $rc = new \ReflectionClass(OrganizationUnitController::class);

        $paramTypes = [];
        foreach ($rc->getConstructor()->getParameters() as $param) {
            $paramTypes[] = $param->getType() ? $param->getType()->getName() : null;
        }

        $this->assertContains(UpdateOrganizationUnitServiceInterface::class, $paramTypes,
            'OrganizationUnitController must inject UpdateOrganizationUnitServiceInterface.');
    }

    public function test_org_unit_controller_injects_delete_service(): void
    {
        $rc = new \ReflectionClass(OrganizationUnitController::class);

        $paramTypes = [];
        foreach ($rc->getConstructor()->getParameters() as $param) {
            $paramTypes[] = $param->getType() ? $param->getType()->getName() : null;
        }

        $this->assertContains(DeleteOrganizationUnitServiceInterface::class, $paramTypes,
            'OrganizationUnitController must inject DeleteOrganizationUnitServiceInterface.');
    }

    public function test_org_unit_controller_injects_move_service(): void
    {
        $rc = new \ReflectionClass(OrganizationUnitController::class);

        $paramTypes = [];
        foreach ($rc->getConstructor()->getParameters() as $param) {
            $paramTypes[] = $param->getType() ? $param->getType()->getName() : null;
        }

        $this->assertContains(MoveOrganizationUnitServiceInterface::class, $paramTypes,
            'OrganizationUnitController must inject MoveOrganizationUnitServiceInterface.');
    }

    // ── FindOrganizationUnitServiceInterface ──────────────────────────────────

    public function test_find_org_unit_service_interface_exists(): void
    {
        $this->assertTrue(interface_exists(FindOrganizationUnitServiceInterface::class));
    }

    public function test_find_org_unit_service_interface_declares_required_methods(): void
    {
        $rc = new \ReflectionClass(FindOrganizationUnitServiceInterface::class);

        $this->assertTrue($rc->hasMethod('find'));
        $this->assertTrue($rc->hasMethod('list'));
        $this->assertTrue($rc->hasMethod('getTree'));
    }

    // ── FindOrganizationUnitService ───────────────────────────────────────────

    public function test_find_org_unit_service_class_exists(): void
    {
        $this->assertTrue(class_exists(FindOrganizationUnitService::class));
    }

    public function test_find_org_unit_service_implements_interface(): void
    {
        $this->assertTrue(
            is_subclass_of(FindOrganizationUnitService::class, FindOrganizationUnitServiceInterface::class),
            'FindOrganizationUnitService must implement FindOrganizationUnitServiceInterface.'
        );
    }

    public function test_find_org_unit_service_find_delegates_to_repository(): void
    {
        $unit = $this->createTestUnit(3, 1);

        $repo = $this->createMock(OrganizationUnitRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('find')
            ->with(3)
            ->willReturn($unit);

        $service = new FindOrganizationUnitService($repo);
        $result  = $service->find(3);

        $this->assertSame($unit, $result);
    }

    public function test_find_org_unit_service_find_returns_null_when_missing(): void
    {
        $repo = $this->createMock(OrganizationUnitRepositoryInterface::class);
        $repo->method('find')->willReturn(null);

        $service = new FindOrganizationUnitService($repo);
        $this->assertNull($service->find(9999));
    }

    public function test_find_org_unit_service_get_tree_delegates_to_repository(): void
    {
        $tree = [['id' => 1, 'name' => 'Root', 'children' => []]];

        $repo = $this->createMock(OrganizationUnitRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('getTree')
            ->with(1, null)
            ->willReturn($tree);

        $service = new FindOrganizationUnitService($repo);
        $result  = $service->getTree(1, null);

        $this->assertSame($tree, $result);
    }

    public function test_find_org_unit_service_get_tree_with_root_id(): void
    {
        $tree = [['id' => 5, 'name' => 'Sub', 'children' => []]];

        $repo = $this->createMock(OrganizationUnitRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('getTree')
            ->with(2, 5)
            ->willReturn($tree);

        $service = new FindOrganizationUnitService($repo);
        $result  = $service->getTree(2, 5);

        $this->assertSame($tree, $result);
    }

    public function test_find_org_unit_service_handle_throws_bad_method_call_exception(): void
    {
        $this->expectException(\BadMethodCallException::class);

        $repo    = $this->createMock(OrganizationUnitRepositoryInterface::class);
        $service = new FindOrganizationUnitService($repo);

        $method = new \ReflectionMethod($service, 'handle');
        $method->setAccessible(true);
        $method->invoke($service, []);
    }

    public function test_find_org_unit_service_does_not_depend_on_attachment_repository(): void
    {
        $rc = new \ReflectionClass(FindOrganizationUnitService::class);

        foreach ($rc->getConstructor()->getParameters() as $param) {
            $type = $param->getType() ? $param->getType()->getName() : null;
            $this->assertNotSame(
                \Modules\OrganizationUnit\Domain\RepositoryInterfaces\OrganizationUnitAttachmentRepositoryInterface::class,
                $type,
                'FindOrganizationUnitService must not depend on OrganizationUnitAttachmentRepositoryInterface.'
            );
        }
    }

    // ── UploadOrganizationUnitAttachmentRequest ───────────────────────────────

    public function test_upload_org_unit_attachment_request_class_exists(): void
    {
        $this->assertTrue(class_exists(UploadOrganizationUnitAttachmentRequest::class));
    }

    public function test_upload_org_unit_attachment_request_has_single_file_rule(): void
    {
        $request = new UploadOrganizationUnitAttachmentRequest;
        $rules   = $request->rules();

        $this->assertArrayHasKey('file', $rules,
            'Request must define a validation rule for single file upload.');
    }

    public function test_upload_org_unit_attachment_request_has_bulk_files_rule(): void
    {
        $request = new UploadOrganizationUnitAttachmentRequest;
        $rules   = $request->rules();

        $this->assertArrayHasKey('files', $rules,
            'Request must define a validation rule for bulk file uploads.');
        $this->assertArrayHasKey('files.*', $rules,
            'Request must define a validation rule for each file in the bulk upload.');
    }

    public function test_upload_org_unit_attachment_request_has_with_validator_method(): void
    {
        $rc = new \ReflectionClass(UploadOrganizationUnitAttachmentRequest::class);
        $this->assertTrue($rc->hasMethod('withValidator'),
            'Request must define withValidator() to enforce at-least-one-file constraint.');
    }

    public function test_upload_org_unit_attachment_request_file_rule_is_nullable(): void
    {
        $request = new UploadOrganizationUnitAttachmentRequest;
        $rules   = $request->rules();

        $this->assertStringContainsString('nullable', $rules['file'],
            'The single file field should be nullable to support bulk-only uploads.');
    }

    // ── OrganizationUnitAttachmentResource ────────────────────────────────────

    public function test_org_unit_attachment_resource_class_exists(): void
    {
        $this->assertTrue(class_exists(OrganizationUnitAttachmentResource::class));
    }

    // ── OrganizationUnitServiceProvider ──────────────────────────────────────

    public function test_org_unit_service_provider_class_exists(): void
    {
        $this->assertTrue(class_exists(OrganizationUnitServiceProvider::class));
    }

    public function test_org_unit_service_provider_registers_attachment_storage_strategy(): void
    {
        $rc       = new \ReflectionClass(OrganizationUnitServiceProvider::class);
        $method   = $rc->getMethod('register');
        $filename = $rc->getFileName();
        $start    = $method->getStartLine();
        $end      = $method->getEndLine();
        $lines    = array_slice(file($filename), $start - 1, $end - $start + 1);
        $body     = implode('', $lines);

        $this->assertStringContainsString('AttachmentStorageStrategyInterface', $body,
            'ServiceProvider must bind AttachmentStorageStrategyInterface.');
    }

    public function test_org_unit_service_provider_registers_find_attachments_service(): void
    {
        $rc       = new \ReflectionClass(OrganizationUnitServiceProvider::class);
        $method   = $rc->getMethod('register');
        $filename = $rc->getFileName();
        $start    = $method->getStartLine();
        $end      = $method->getEndLine();
        $lines    = array_slice(file($filename), $start - 1, $end - $start + 1);
        $body     = implode('', $lines);

        $this->assertStringContainsString('FindOrganizationUnitAttachmentsServiceInterface', $body,
            'ServiceProvider must bind FindOrganizationUnitAttachmentsServiceInterface.');
    }

    public function test_org_unit_service_provider_registers_bulk_upload_service(): void
    {
        $rc       = new \ReflectionClass(OrganizationUnitServiceProvider::class);
        $method   = $rc->getMethod('register');
        $filename = $rc->getFileName();
        $start    = $method->getStartLine();
        $end      = $method->getEndLine();
        $lines    = array_slice(file($filename), $start - 1, $end - $start + 1);
        $body     = implode('', $lines);

        $this->assertStringContainsString('BulkUploadOrganizationUnitAttachmentsServiceInterface', $body,
            'ServiceProvider must bind BulkUploadOrganizationUnitAttachmentsServiceInterface.');
    }

    public function test_org_unit_service_provider_registers_find_org_unit_service(): void
    {
        $rc       = new \ReflectionClass(OrganizationUnitServiceProvider::class);
        $method   = $rc->getMethod('register');
        $filename = $rc->getFileName();
        $start    = $method->getStartLine();
        $end      = $method->getEndLine();
        $lines    = array_slice(file($filename), $start - 1, $end - $start + 1);
        $body     = implode('', $lines);

        $this->assertStringContainsString('FindOrganizationUnitServiceInterface', $body,
            'ServiceProvider must bind FindOrganizationUnitServiceInterface.');
    }

    // ── Routes ────────────────────────────────────────────────────────────────

    public function test_routes_file_exists(): void
    {
        $routesFile = __DIR__.'/../../app/Modules/OrganizationUnit/routes/api.php';
        $this->assertFileExists($routesFile);
    }

    public function test_routes_file_has_bulk_upload_route(): void
    {
        $routesFile = __DIR__.'/../../app/Modules/OrganizationUnit/routes/api.php';
        $content    = file_get_contents($routesFile);

        $this->assertStringContainsString('storeMany', $content,
            'Routes must include a bulk upload endpoint (storeMany).');
        $this->assertStringContainsString('bulk', $content,
            'Routes must include a /bulk path segment for the bulk upload endpoint.');
    }

    // ── OrganizationUnitAttachment Entity ─────────────────────────────────────

    public function test_org_unit_attachment_entity_class_exists(): void
    {
        $this->assertTrue(class_exists(OrganizationUnitAttachment::class));
    }

    public function test_org_unit_attachment_entity_can_be_constructed(): void
    {
        $attachment = $this->createTestAttachment(1, 2, 3);

        $this->assertSame(3, $attachment->getTenantId());
        $this->assertSame(2, $attachment->getOrganizationUnitId());
        $this->assertSame('test-uuid-1', $attachment->getUuid());
        $this->assertSame('test-file.pdf', $attachment->getName());
        $this->assertSame('application/pdf', $attachment->getMimeType());
        $this->assertSame(1024, $attachment->getSize());
    }

    // ── UpdateOrganizationUnitRequest ─────────────────────────────────────────

    public function test_update_org_unit_request_does_not_require_tenant_id(): void
    {
        $request = new \Modules\OrganizationUnit\Infrastructure\Http\Requests\UpdateOrganizationUnitRequest;
        $rules   = $request->rules();

        $this->assertArrayNotHasKey('tenant_id', $rules,
            'UpdateOrganizationUnitRequest must not require tenant_id — tenants never change on update.');
    }

    public function test_update_org_unit_request_name_uses_sometimes_rule(): void
    {
        $request = new \Modules\OrganizationUnit\Infrastructure\Http\Requests\UpdateOrganizationUnitRequest;
        $rules   = $request->rules();

        $this->assertArrayHasKey('name', $rules,
            'UpdateOrganizationUnitRequest must declare a rule for name.');
        $this->assertStringContainsString('sometimes', $rules['name'],
            'The name rule must use "sometimes" to support partial updates.');
    }

    // ── UpdateOrganizationUnitService ─────────────────────────────────────────

    public function test_update_org_unit_service_does_not_use_dto_directly(): void
    {
        $rc       = new \ReflectionClass(\Modules\OrganizationUnit\Application\Services\UpdateOrganizationUnitService::class);
        $filename = $rc->getFileName();
        $source   = file_get_contents($filename);

        $this->assertStringNotContainsString('OrganizationUnitData::fromArray',
            $source,
            'UpdateOrganizationUnitService must not build OrganizationUnitData from the raw input array — use array_key_exists guards instead.');
    }

    public function test_update_org_unit_service_throws_when_unit_not_found(): void
    {
        $this->expectException(\Modules\OrganizationUnit\Domain\Exceptions\OrganizationUnitNotFoundException::class);

        $repo = $this->createMock(\Modules\OrganizationUnit\Domain\RepositoryInterfaces\OrganizationUnitRepositoryInterface::class);
        $repo->method('find')->willReturn(null);

        $service = new \Modules\OrganizationUnit\Application\Services\UpdateOrganizationUnitService($repo);

        $method = new \ReflectionMethod($service, 'handle');
        $method->setAccessible(true);
        $method->invoke($service, ['id' => 99, 'name' => 'New Name']);
    }

    public function test_update_org_unit_service_updates_full_fields(): void
    {
        $unit = $this->createTestUnit(1, 1);

        $repo = $this->createMock(\Modules\OrganizationUnit\Domain\RepositoryInterfaces\OrganizationUnitRepositoryInterface::class);
        $repo->expects($this->once())->method('find')->with(1)->willReturn($unit);
        $repo->expects($this->once())->method('save')->willReturn($unit);

        $service = new \Modules\OrganizationUnit\Application\Services\UpdateOrganizationUnitService($repo);

        $method = new \ReflectionMethod($service, 'handle');
        $method->setAccessible(true);
        $result = $method->invoke($service, [
            'id'          => 1,
            'name'        => 'Updated Name',
            'code'        => 'UPD',
            'description' => 'New description',
            'metadata'    => ['key' => 'value'],
        ]);

        $this->assertInstanceOf(\Modules\OrganizationUnit\Domain\Entities\OrganizationUnit::class, $result);
    }

    public function test_update_org_unit_service_partial_update_preserves_existing_name(): void
    {
        $unit = $this->createTestUnit(2, 1);
        $originalName = $unit->getName();

        $repo = $this->createMock(\Modules\OrganizationUnit\Domain\RepositoryInterfaces\OrganizationUnitRepositoryInterface::class);
        $repo->expects($this->once())->method('find')->with(2)->willReturn($unit);
        $repo->expects($this->once())->method('save')->willReturn($unit);

        $service = new \Modules\OrganizationUnit\Application\Services\UpdateOrganizationUnitService($repo);

        $method = new \ReflectionMethod($service, 'handle');
        $method->setAccessible(true);
        // Only update description — name is intentionally omitted.
        $method->invoke($service, [
            'id'          => 2,
            'description' => 'Only description changed',
        ]);

        $this->assertSame($originalName, $unit->getName(),
            'The entity name must be preserved when name is not included in the update payload.');
    }

    public function test_update_org_unit_service_move_only_when_parent_id_changes(): void
    {
        $unit = $this->createTestUnit(3, 1);

        $repo = $this->createMock(\Modules\OrganizationUnit\Domain\RepositoryInterfaces\OrganizationUnitRepositoryInterface::class);
        $repo->expects($this->once())->method('find')->with(3)->willReturn($unit);
        $repo->expects($this->never())->method('moveNode');
        $repo->expects($this->once())->method('save')->willReturn($unit);

        $service = new \Modules\OrganizationUnit\Application\Services\UpdateOrganizationUnitService($repo);

        $method = new \ReflectionMethod($service, 'handle');
        $method->setAccessible(true);
        // parent_id not supplied — moveNode must not be called.
        $method->invoke($service, ['id' => 3, 'name' => 'Stable Name']);
    }

    // ── OrganizationUnitController — no double-DTO ────────────────────────────

    public function test_org_unit_controller_store_does_not_build_dto(): void
    {
        $rc       = new \ReflectionClass(OrganizationUnitController::class);
        $filename = $rc->getFileName();
        $source   = file_get_contents($filename);

        // Verify the specific (non-Move) DTO is neither imported nor instantiated in the controller.
        $this->assertStringNotContainsString(
            'use Modules\OrganizationUnit\Application\DTOs\OrganizationUnitData',
            $source,
            'OrganizationUnitController must not import OrganizationUnitData — pass validated() directly to the service.'
        );
    }

    public function test_update_org_unit_service_preserves_metadata_when_omitted(): void
    {
        // Build a unit that already has metadata.
        $unit = $this->createTestUnit(10, 1);
        $existingMetadata = new \Modules\Core\Domain\ValueObjects\Metadata(['org_type' => 'dept']);
        // Inject the metadata through updateDetails so the entity has a real Metadata VO.
        $unit->updateDetails($unit->getName(), $unit->getCode(), $unit->getDescription(), $existingMetadata);

        $repo = $this->createMock(\Modules\OrganizationUnit\Domain\RepositoryInterfaces\OrganizationUnitRepositoryInterface::class);
        $repo->expects($this->once())->method('find')->with(10)->willReturn($unit);
        $repo->expects($this->once())->method('save')->willReturn($unit);

        $service = new \Modules\OrganizationUnit\Application\Services\UpdateOrganizationUnitService($repo);
        $method  = new \ReflectionMethod($service, 'handle');
        $method->setAccessible(true);

        // Omit metadata entirely — the existing metadata must be unchanged.
        $method->invoke($service, ['id' => 10, 'name' => 'New Name']);

        $this->assertSame(
            $existingMetadata,
            $unit->getMetadata(),
            'Entity metadata must not be modified when metadata is omitted from the update payload.'
        );
    }
}
