<?php

namespace App\Http\Controllers\Api\V1\ShuleYetu\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ShuleYetu\Finance\FeeStructureStoreRequest;
use App\Http\Resources\Api\V1\ShuleYetu\Finance\FeeStructureResource;
use App\Modules\ShuleYetu\Finance\Services\FeeService;
use App\Modules\ShuleYetu\Models\ShuleFeeStructure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FeeStructureController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorizePermission('finance.view');

        return FeeStructureResource::collection(
            ShuleFeeStructure::query()->with('items')->orderBy('name')->get()
        );
    }

    public function store(FeeStructureStoreRequest $request): FeeStructureResource
    {
        $this->authorizePermission('finance.manage');

        $structure = ShuleFeeStructure::query()->create($request->validated());

        return new FeeStructureResource($structure->load('items'));
    }

    public function generateBills(string $structure, FeeService $service): JsonResponse
    {
        $this->authorizePermission('finance.manage');

        $record = ShuleFeeStructure::query()->findOrFail($structure);
        $created = $service->generateBillsForStructure($record->id, (string) $record->school_id);

        return response()->json(['status' => 'ok', 'bills_created' => $created]);
    }

    private function authorizePermission(string $permission): void
    {
        abort_unless(auth()->user()?->hasPermission($permission), 403);
    }
}
