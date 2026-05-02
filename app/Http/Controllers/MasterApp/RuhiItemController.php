<?php

namespace App\Http\Controllers\MasterApp;

use App\Core\File\Services\FileManagementService;
use App\Core\RuhiItems\Services\RuhiItemService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RuhiItemController extends Controller
{
    public function index(): View
    {
        return view('masterapp.ruhi-items.index');
    }

    public function create(RuhiItemService $service): View
    {
        $itemTypes = $service->listTypes();

        return view('masterapp.ruhi-items.create', compact('itemTypes'));
    }

    public function store(Request $request, FileManagementService $fileService, RuhiItemService $service): RedirectResponse
    {
        $validated = $request->validate([
            'product_name' => ['required', 'string', 'max:100'],
            'photo1' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'product_type' => ['required', 'integer', 'exists:r_item_type,id'],
            'weight' => ['nullable', 'numeric', 'decimal:0,2'],
        ]);

        $photo1Path = $fileService->upload($request->file('photo1'), 'ruhi-products');

        $service->create([
            'product_name' => $validated['product_name'],
            'product_desc' => null,
            'photo1' => $photo1Path,
            'photo2' => null,
            'product_type' => $validated['product_type'],
            'weight' => isset($validated['weight']) ? number_format((float) $validated['weight'], 2, '.', '') : 0,
            'create_date' => now()->toDateString(),
        ]);

        return redirect()
            ->route('masterapp.ruhi-items.index')
            ->with('success', 'Item created successfully.');
    }

    public function edit(int $id, RuhiItemService $service): View
    {
        $item = $service->findById($id);
        $itemTypes = $service->listTypes();

        return view('masterapp.ruhi-items.edit', compact('item', 'itemTypes'));
    }

    public function update(Request $request, int $id, FileManagementService $fileService, RuhiItemService $service): RedirectResponse
    {
        $validated = $request->validate([
            'product_name' => ['required', 'string', 'max:100'],
            'photo1' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'product_type' => ['required', 'integer', 'exists:r_item_type,id'],
            'weight' => ['nullable', 'numeric', 'decimal:0,2'],
        ]);

        $item = $service->findById($id);

        $photo1Path = $item->photo1;
        if ($request->hasFile('photo1')) {
            $photo1Path = $fileService->upload($request->file('photo1'), 'ruhi-products');
            $fileService->delete($item->photo1);
        }

        $service->update($item, [
            'product_name' => $validated['product_name'],
            'photo1' => $photo1Path,
            'product_type' => $validated['product_type'],
            'weight' => isset($validated['weight']) ? number_format((float) $validated['weight'], 2, '.', '') : 0,
        ]);

        return redirect()
            ->route('masterapp.ruhi-items.index')
            ->with('success', 'Item updated successfully.');
    }

    public function destroy(int $id, RuhiItemService $service): RedirectResponse
    {
        $service->softDeleteById($id);

        return redirect()
            ->route('masterapp.ruhi-items.index')
            ->with('success', 'Item deleted successfully.');
    }
}

