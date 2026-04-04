<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('org_unit_closures', function (Blueprint $table) {
            $table->unsignedBigInteger('ancestor_id');
            $table->unsignedBigInteger('descendant_id');
            $table->unsignedInteger('depth')->default(0);
            $table->primary(['ancestor_id', 'descendant_id']);
            $table->index('descendant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('org_unit_closures');
    }
};
