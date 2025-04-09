<?php

namespace App\Http\Controllers;

use App\Models\SizeTemplate;
use App\Models\SizeTemplateItem;
use Illuminate\Http\Request;

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
        $template = SizeTemplate::where('id', $id)
            ->where('seller_id', auth()->id())
            ->firstOrFail();

        $template->delete();

        SizeTemplateItem::where('template_id', $template->id)->delete(); 

        return response()->json([
            'message' => 'Size template deleted successfully'
        ]);
    }
}
