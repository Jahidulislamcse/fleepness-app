<?php

// app/Http/Controllers/SectionController.php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Section;
use App\Models\SectionItem;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function index()
    {
        // Retrieve all sections with their associated items
        $sections = Section::with('items')->get();

        return view('admin.sections.index', compact('sections'));
    }


    public function create()
    {
        $categories = Category::whereNull('parent_id')->get();
        return view('admin.sections.create', compact('categories'));
    }

public function store(Request $request)
{
    // dd($request->all());

    // Validate the request data
    $validated = $request->validate([
        'section_name' => 'nullable|string|max:255',
        'section_type' => 'nullable|string',
        'section_title' => 'nullable|string|max:255',
        'category_id' => 'nullable|exists:categories,id',
        'index' => 'nullable|integer',
        'visibility' => 'boolean',
        'background_image' => 'nullable|image|mimes:jpeg,jpg,png,gif,svg',
        'items' => 'array|nullable',
        'items.*.image' => 'nullable|image|mimes:jpeg,jpg,png,gif,svg',
        'items.*.tag_id' => 'nullable|string',
        'items.*.index' => 'nullable|integer',
        'items.*.visibility' => 'nullable|boolean',
    ]);



    // Handle the background image upload
    if ($request->hasFile('background_image')) {
        $photo = $request->file('background_image');
        $name_gen = hexdec(uniqid()) . '.' . $photo->getClientOriginalExtension();
        $path = public_path('upload/sections');
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        $photo->move($path, $name_gen);
        $background = 'upload/sections/' . $name_gen;
    }

    // Create the section record
     $section = Section::create([
        'section_name' => $validated['section_name'],
        'section_type' => $validated['section_type'],
        'section_title' => $validated['section_title'],
        'category_id' => $validated['category_id'],
        'index' => $validated['index'],
        'visibility' => $validated['visibility'] ?? false, // Default to false if not provided
        'background_image' =>  $background,
    ]);

    // Handle storing the section items
    if (isset($validated['items'])) {
        foreach ($request->items as $index => $item) {
            // Handle the item image upload
            if (isset($item['image']) && $item['image'] && $request->hasFile('items.' . $index . '.image')) {
                $item_image = $item['image'];
                $image_name = hexdec(uniqid()) . '.' . $item_image->getClientOriginalExtension();
                $image_path = public_path('upload/section_items');
                if (!file_exists($image_path)) {
                    mkdir($image_path, 0777, true);
                }
                $item_image->move($image_path, $image_name);
                SectionItem::create([
                    'section_id' => $section->id,
                    'image' => 'upload/section_items/' . $image_name,
                    'tag_id' => $item['tag_id'],
                    'index' => $item['index'],
                    'visibility' => $item['visibility'],
                ]);
            } else {
                SectionItem::create([
                    'section_id' => $section->id,
                    'image' => null,
                    'tag_id' => $item['tag_id'],
                    'index' => $item['index'],
                    'visibility' => $item['visibility'],
                ]);
            }
        }
    }

    return redirect()->route('admin.sections.index')->with('success', 'Section created successfully');
}


    public function edit(Section $section)
    {
        $categories = Category::all();
        return view('admin.sections.edit', compact('section', 'categories'));
    }

    public function update(Request $request, Section $section)
    {
        $validated = $request->validate([
            'section_name' => 'required|string|max:255',
            'section_type' => 'required|string',
            'section_title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'index' => 'required|integer',
            'visibility' => 'boolean',
            'background_image' => 'nullable|image|mimes:jpeg,jpg,png,gif,svg',
            'items' => 'array|required',
            'items.*.image' => 'required|image|mimes:jpeg,jpg,png,gif,svg',
            'items.*.tag_id' => 'required|string',
            'items.*.index' => 'required|integer',
            'items.*.visibility' => 'boolean',
        ]);

        $section->update($validated);

        // Handle background image upload if new image is provided
        if ($request->hasFile('background_image')) {
            $imagePath = $request->file('background_image')->store('uploads', 'public');
            $section->background_image = $imagePath;
            $section->save();
        }

        // Update section items
        foreach ($request->items as $item) {
            $sectionItem = SectionItem::find($item['id']); // Assuming items have an 'id'
            $sectionItem->update([
                'image' => $item['image'],
                'tag_id' => $item['tag_id'],
                'index' => $item['index'],
                'visibility' => $item['visibility'],
            ]);
        }

        return redirect()->route('admin.sections.index')->with('success', 'Section updated successfully');
    }
}
