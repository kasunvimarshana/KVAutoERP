<?php
declare(strict_types=1);
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\Attachment\Domain\Entities\Attachment;

class AttachmentModuleTest extends TestCase
{
    private function makeAttachment(array $overrides = []): Attachment
    {
        return new Attachment(
            1, 1, 'product', 10, 'file_abc.pdf', 'product-spec.pdf',
            $overrides['mime'] ?? 'application/pdf', 1024, 'attachments/1/product/10/file_abc.pdf',
            'local', 'spec', 1, null, null
        );
    }

    public function test_attachment_creation(): void
    {
        $att = $this->makeAttachment();
        $this->assertEquals('product', $att->getAttachableType());
        $this->assertEquals(10, $att->getAttachableId());
        $this->assertEquals('product-spec.pdf', $att->getOriginalName());
        $this->assertEquals(1024, $att->getSize());
    }

    public function test_attachment_is_pdf(): void
    {
        $att = $this->makeAttachment(['mime' => 'application/pdf']);
        $this->assertTrue($att->isPdf());
        $this->assertFalse($att->isImage());
    }

    public function test_attachment_is_image(): void
    {
        $att = $this->makeAttachment(['mime' => 'image/jpeg']);
        $this->assertTrue($att->isImage());
        $this->assertFalse($att->isPdf());
    }

    public function test_attachment_category(): void
    {
        $att = $this->makeAttachment();
        $this->assertEquals('spec', $att->getCategory());
    }
}
