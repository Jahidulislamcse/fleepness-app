<?php

namespace App\Http\Controllers;

use App\Models\SizeTemplate;
use App\Models\SizeTemplateItem;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;


class SizeTemplateController extends Controller
{
    // Create a size template
    public function store(Request $request)
    {
        $request->validate([
            'template_name' => 'required|string|max:255',
        ]);

        $template = SizeTemplate::create([
            'seller_id' => auth()->id(),
            'template_name' => $request->template_name,
        ]);

        return response()->json([
            'message' => 'Size template created successfully',
            'template' => $template
        ], 201);
    }

    // Add multiple size items to an existing template
    public function addSizeToTemplate(Request $request, $templateId)
    {
        $request->validate([
            'sizes' => 'required|array|min:1',
            'sizes.*.size_name' => 'required|string|max:50',
            'sizes.*.size_value' => 'required|string|max:255',
        ]);

        $template = SizeTemplate::where('id', $templateId)
            ->where('seller_id', auth()->id())
            ->firstOrFail();

        $createdSizes = [];

        foreach ($request->sizes as $size) {
            $createdSizes[] = SizeTemplateItem::create([
                'template_id' => $template->id,
                'size_name' => $size['size_name'],
                'size_value' => $size['size_value'],
            ]);
        }

        return response()->json([
            'message' => 'Sizes added to template',
            'size_items' => $createdSizes
        ], 201);
    }

    public function updateSize(Request $request, $templateId, $sizeItemId)
    {
        $request->validate([
            'size_name' => 'nullable|string|max:50',
            'size_value' => 'nullable|string|max:255',
        ]);

        $template = SizeTemplate::where('id', $templateId)
            ->where('seller_id', auth()->id())
            ->firstOrFail();

        $sizeItem = SizeTemplateItem::where('id', $sizeItemId)
            ->where('template_id', $template->id)
            ->firstOrFail();

        if ($request->has('size_name')) {
            $sizeItem->size_name = $request->size_name;
        }

        if ($request->has('size_value')) {
            $sizeItem->size_value = $request->size_value;
        }

        $sizeItem->save();

        return response()->json([
            'message' => 'Size item updated successfully',
            'size_item' => $sizeItem
        ], 200);
    }

    public function destroySizeItem($templateId, $sizeItemId)
    {
        $template = SizeTemplate::where('id', $templateId)
            ->where('seller_id', auth()->id())
            ->firstOrFail();

        $sizeItem = SizeTemplateItem::where('template_id', $templateId)
            ->where('id', $sizeItemId)
            ->first();

        if (!$sizeItem) {
            return response()->json([
                'message' => 'Size item not found.',
            ], 404);
        }

        $sizeItem->delete();

        return response()->json([
            'message' => 'Size item deleted successfully',
        ]);
    }


    // Get all templates for the authenticated seller with their sizes
    public function getTemplates(Request $request)
    {
        $templates = SizeTemplate::with('items')
            ->where('seller_id', auth()->id())
            ->get();

        return response()->json($templates);
    }

    // Delete a template (will also delete its size items)
    public function destroy($id)
    {
        try {
            $template = SizeTemplate::where('id', $id)
                ->where('seller_id', auth()->id())
                ->firstOrFail();

            $template->delete(); // Will fail if FK constraint is violated

            SizeTemplateItem::where('template_id', $template->id)->delete();

            return response()->json([
                'message' => 'Size template deleted successfully'
            ]);

        } catch (QueryException $e) {
            // Check for foreign key constraint error code (MySQL: 1451)
            if ($e->getCode() === '23000') {
                return response()->json([
                    'message' => 'Cannot delete this template because it is used in one or more products.'
                ], 409);
            }

            return response()->json([
                'message' => 'Database error occurred.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
