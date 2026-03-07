<?php

namespace App\Modules\Tenant\Controllers;

use App\Core\Tenant\TenantManager;
use App\Http\Controllers\Controller;
use App\Models\TenantConfiguration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TenantConfigController extends Controller
{
    public function __construct(private TenantManager $tenantManager) {}

    public function index(): JsonResponse
    {
        $tenant  = $this->tenantManager->getTenant();
        $configs = TenantConfiguration::where('tenant_id', $tenant->id)
            ->select(['id', 'key', 'type', 'is_encrypted', 'created_at', 'updated_at'])
            ->get();

        return response()->json($configs);
    }

    public function upsert(Request $request): JsonResponse
    {
        $request->validate([
            'key'          => 'required|string|max:255',
            'value'        => 'required|string',
            'type'         => 'sometimes|string|in:string,boolean,integer,json',
            'is_encrypted' => 'sometimes|boolean',
        ]);

        $tenant       = $this->tenantManager->getTenant();
        $isEncrypted  = $request->boolean('is_encrypted', false);
        $storedValue  = $isEncrypted ? encrypt($request->value) : $request->value;

        $config = TenantConfiguration::updateOrCreate(
            ['tenant_id' => $tenant->id, 'key' => $request->key],
            [
                'value'        => $storedValue,
                'type'         => $request->input('type', 'string'),
                'is_encrypted' => $isEncrypted,
            ]
        );

        $this->tenantManager->clearCache();

        return response()->json($config->only(['id', 'key', 'type', 'is_encrypted', 'created_at', 'updated_at']));
    }

    public function destroy(string $key): JsonResponse
    {
        $tenant = $this->tenantManager->getTenant();
        TenantConfiguration::where('tenant_id', $tenant->id)->where('key', $key)->delete();
        $this->tenantManager->clearCache();

        return response()->json(['message' => 'Configuration key deleted.']);
    }
}
