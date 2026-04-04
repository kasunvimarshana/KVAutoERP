<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouse_location_closures', function (Blueprint $table) {
            $table->unsignedBigInteger('ancestor_id');
            $table->unsignedBigInteger('descendant_id');
            $table->unsignedInteger('depth');

            $table->primary(['ancestor_id', 'descendant_id']);
            $table->foreign('ancestor_id')->references('id')->on('warehouse_locations')->cascadeOnDelete();
            $table->foreign('descendant_id')->references('id')->on('warehouse_locations')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_location_closures');
    }
};
