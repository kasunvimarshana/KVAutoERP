<?php

namespace App\Modules\Product\Tests\Feature;

use App\Modules\Product\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_crud_lifecycle(): void
    {
        // Create
        $created = $this->postJson('/api/products', [
            'name'  => 'CRUD Product',
            'price' => 19.99,
            'stock' => 50,
            'sku'   => 'SKU-CRUD-001',
        ])->assertCreated()->json('data');

        $id = $created['id'];

        // Read
        $this->getJson("/api/products/{$id}")
             ->assertOk()
             ->assertJsonPath('data.sku', 'SKU-CRUD-001');

        // Update
        $this->putJson("/api/products/{$id}", ['price' => 29.99])
             ->assertOk()
             ->assertJsonPath('data.price', 29.99);

        // Delete
        $this->deleteJson("/api/products/{$id}")->assertOk();
        $this->getJson("/api/products/{$id}")->assertNotFound();
    }
}
