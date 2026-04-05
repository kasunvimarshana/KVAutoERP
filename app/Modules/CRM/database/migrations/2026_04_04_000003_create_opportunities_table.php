<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->unsignedBigInteger('lead_id')->nullable();
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->string('name');
            $table->string('stage')->default('prospecting');
            $table->decimal('value', 18, 4)->default(0);
            $table->char('currency', 3)->default('USD');
            $table->integer('probability')->default(0);
            $table->date('expected_close_date')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('lead_id')->references('id')->on('leads')->nullOnDelete();
            $table->foreign('contact_id')->references('id')->on('contacts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunities');
    }
};
