<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('tenant_id', 36)->nullable()->index();
            $table->string('entity_type', 100)->index();   // e.g. "user", "product", "document"
            $table->string('entity_id', 36)->index();
            $table->string('collection', 100)->default('default')->index(); // e.g. "avatar", "documents"
            $table->string('disk', 50)->default('public');
            $table->string('path', 1000);
            $table->string('original_filename', 500);
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('size')->default(0); // bytes
            $table->string('visibility', 20)->default('private'); // public | private
            $table->string('uploaded_by', 36)->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamps(); // created_at + updated_at (updated when attachment is replaced)

            // Composite index for the most common query pattern (listAttachments)
            $table->index(['entity_type', 'entity_id', 'collection'], 'attachments_entity_collection_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
