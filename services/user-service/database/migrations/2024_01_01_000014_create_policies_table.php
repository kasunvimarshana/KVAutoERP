<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ABAC (Attribute-Based Access Control) Policies table.
 *
 * Each row defines one rule:
 *   IF  subject_attributes  MATCH conditions
 *   AND resource_attributes MATCH conditions
 *   AND environment         MATCH conditions
 *   THEN effect (allow|deny)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('policies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('tenant_id', 36)->nullable()->index();
            $table->string('name', 255);
            $table->string('slug', 255)->index();
            $table->text('description')->nullable();
            $table->string('effect', 10)->default('allow'); // allow | deny
            $table->string('action', 255);                  // wildcard pattern, e.g. "users:*"
            $table->json('subject_conditions')->nullable();  // {"roles":["admin"],"branch_id":"*"}
            $table->json('resource_conditions')->nullable(); // {"entity_type":"user","tenant_id":"{{tenant_id}}"}
            $table->json('environment_conditions')->nullable(); // {"ip_range":"10.0.0.0/8","time_range":{...}}
            $table->boolean('is_active')->default(true)->index();
            $table->integer('priority')->default(0)->index(); // higher = evaluated first
            $table->string('created_by', 36)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('policies');
    }
};
