<?php

// app/Http/Controllers/SectionController.php

namespace App\Http\Controllers;

use App\Http\Resources\SectionResource;
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

    public function sections()
    {
        $sections = \App\Models\Section::with(['category', 'items.tag'])->orderBy('index')->get();
        return SectionResource::collection($sections);
    }

    public function searchSection()
    {
        $section = \App\Models\Section::where('section_type', 'search')->with(['category', 'items.tag'])->orderBy('index')->get();
        return SectionResource::collection($section);
    }


    public function create()
    {
        $categories = Category::whereNull('parent_id')->get();
        return view('admin.sections.create', compact('categories'));
    }

    public function store(Request $request)
    {
        // Validate the request data
    $validated = $request->validate([
        'section_name' => 'nullable|string|max:255',
        'section_type' => 'nullable|string|unique:sections,section_type',
        'section_title' => 'nullable|string|max:255',
        'category_id' => 'nullable|exists:categories,id',
        'visibility' => 'boolean',
        'background_image' => 'nullable|image|mimes:jpeg,jpg,png,gif,svg',
        'banner_image' => 'nullable|image|mimes:jpeg,jpg,png,gif,svg',
        'items' => 'array|nullable',
        'items.*.image' => 'nullable|image|mimes:jpeg,jpg,png,gif,svg',
        'items.*.title' => 'nullable|string',
        'items.*.bio' => 'nullable|string',
        'items.*.tag_id' => 'nullable|string',
        'items.*.visibility' => 'nullable|boolean',
    ]);

    $background = null;
    $banner_img = null;

    // Handle the background image upload
    if ($request->hasFile('background_image')) {
        $photo = $request->file('background_image');
        $name_gen = hexdec(uniqid()) . '.' . $photo->getClientOriginalExtension();
        $folder = 'slider/';
        if (!file_exists(public_path('upload/' . $folder))) {
            mkdir(public_path('upload/' . $folder), 0777, true);
        }
        $photo->move(public_path('upload/' . $folder), $name_gen);
        $background = 'upload/' . $folder . $name_gen;
    }

    // Handle the banner image upload
    if ($request->hasFile('banner_image')) {
        $banner = $request->file('banner_image');
        $name_gen_banner = hexdec(uniqid()) . '.' . $banner->getClientOriginalExtension();
        $folder = 'slider/';
        if (!file_exists(public_path('upload/' . $folder))) {
            mkdir(public_path('upload/' . $folder), 0777, true);
        }
        $banner->move(public_path('upload/' . $folder), $name_gen_banner);
        $banner_img = 'upload/' . $folder . $name_gen_banner;
    }

    $lastIndex = Section::max('index');
    $newIndex = $lastIndex + 1;

    // Create the section record
    $section = Section::create([
        'section_name' => $validated['section_name'],
        'section_type' => $validated['section_type'],
        'section_title' => $validated['section_title'],
        'category_id' => $validated['category_id'],
        'visibility' => $validated['visibility'] ?? false,
        'index' => $newIndex,
        'background_image' =>  $background,
        'banner_image' =>  $banner_img,
    ]);

    // Handle storing the section items
    if (isset($validated['items']) && count($validated['items']) > 0) {
        foreach ($request->items as $index => $item) {
            $item_image = isset($item['image']) ? $item['image'] : null;
            $image_path = null;

            $itemIndex = $index + 1;

            if ($item_image && $request->hasFile('items.' . $index . '.image')) {
                $image_name = hexdec(uniqid()) . '.' . $item_image->getClientOriginalExtension();
                $folder = 'slider/';
                $image_path = public_path('upload/' . $folder);
                if (!file_exists($image_path)) {
                    mkdir($image_path, 0777, true);
                }
                $item_image->move($image_path, $image_name);
                $item_image = 'upload/' . $folder . $image_name;
            }

            // Create the section item
            SectionItem::create([
                'section_id' => $section->id,
                'image' => $item_image,
                'title' => $item['title'] ?? null,
                'bio' => $item['bio'] ?? null,
                'tag_id' => $item['tag_id'] ?? null,
                'index' => $itemIndex,
                'visibility' => $item['visibility'] ?? 1,
            ]);
        }
    }

    return redirect()->route('admin.sections.index')->with('success', 'Section created successfully');
}





    public function edit($id)
    {
        $section = Section::findOrFail($id);
        $categories = Category::all();

        // Get all sections ordered by their index
        $allSections = Section::orderBy('index')->get();

        // Generate available indices excluding the current section's index
        $availableIndices = [];
        foreach ($allSections as $sec) {
            if ($sec->id != $section->id) {
                $availableIndices[] = $sec->index;
            }
        }

        return view('admin.sections.edit', compact('section', 'categories', 'availableIndices'));
    }






    public function update(Request $request, $id)
    {
        $section = Section::findOrFail($id);

        // Validate request data
        $validated = $request->validate([
            'section_name' => 'nullable|string|max:255',
            'section_type' => 'nullable|string|unique:sections,section_type,' . $id,
            'section_title' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'index' => 'nullable|integer|min:1',
            'visibility' => 'boolean',
            'background_image' => 'nullable|image|mimes:jpeg,jpg,png,gif,svg',
            'banner_image' => 'nullable|image|mimes:jpeg,jpg,png,gif,svg',
            'items' => 'array|nullable',
            'items.*.image' => 'nullable|image|mimes:jpeg,jpg,png,gif,svg',
            'items.*.title' => 'nullable|string',
            'items.*.bio' => 'nullable|string',
            'items.*.tag_id' => 'nullable|string',
            'items.*.index' => 'nullable|integer',
            'items.*.visibility' => 'nullable|boolean',
        ]);

        // Get the current index of the section
        $currentIndex = $section->index;

        // Get the new index from the request
        $newIndex = $validated['index'];

        // If the new index is different from the current one, proceed with reordering
        if ($currentIndex != $newIndex) {
            // Reorder sections after the updated index
            $this->reorderSectionsAfterUpdate($newIndex, $currentIndex, $section);
        }

        // Handle background image upload
        $background = $section->background_image;
        if ($request->hasFile('background_image')) {
            $photo = $request->file('background_image');
            $name_gen = hexdec(uniqid()) . '.' . $photo->getClientOriginalExtension();
            $folder = 'slider/';
            $photo->move(public_path('upload/' . $folder), $name_gen);
            $background = 'upload/' . $folder . $name_gen;
        }

        // Handle banner image upload
        $banner_img = $section->banner_image;
        if ($request->hasFile('banner_image')) {
            $banner = $request->file('banner_image');
            $name_gen_banner = hexdec(uniqid()) . '.' . $banner->getClientOriginalExtension();
            $folder = 'slider/';
            $banner->move(public_path('upload/' . $folder), $name_gen_banner);
            $banner_img = 'upload/' . $folder . $name_gen_banner;
        }

        // Update the section with the new values
        $section->update([
            'section_name' => $validated['section_name'],
            'section_type' => $validated['section_type'] ?? $section->section_type,
            'section_title' => $validated['section_title'],
            'category_id' => $validated['category_id'],
            'index' => $newIndex,
            'visibility' => $validated['visibility'] ?? false,
            'background_image' => $background,
            'banner_image' => $banner_img,
        ]);

        // Get the current items from the database
        $oldItems = $section->items()->get();
        // Get the updated items from the form
        $updatedItems = $request->input('items', []);

        // Loop through the updated items and save them
        foreach ($updatedItems as $i => $itemData) {
            $itemModel = $oldItems[$i] ?? new \App\Models\SectionItem();
            $itemModel->section_id = $section->id;

            // Handle image upload for each item
            $uploadedImage = $request->file("items.$i.image");
            if ($uploadedImage) {
                $imageName = hexdec(uniqid()) . '.' . $uploadedImage->getClientOriginalExtension();
                $folder = 'slider/';
                $uploadedImage->move(public_path('upload/' . $folder), $imageName);
                $itemModel->image = 'upload/' . $folder . $imageName;
            } elseif (!$itemModel->exists) {
                $itemModel->image = null;
            }

            // Update item data
            $itemModel->title = $itemData['title'] ?? null;
            $itemModel->bio = $itemData['bio'] ?? null;
            $itemModel->tag_id = $itemData['tag_id'] ?? null;
            $itemModel->index = $itemData['index'] ?? null;  // Ensure index is updated based on drag-and-drop
            $itemModel->visibility = $itemData['visibility'] ?? 1;
            $itemModel->save();
        }

        // If the number of old items is greater than the updated items, delete the excess items
        if ($oldItems->count() > count($updatedItems)) {
            $oldItems->slice(count($updatedItems))->each->delete();
        }

        return redirect()->route('admin.sections.index')->with('success', 'Section updated successfully');
    }

    /**
     * Reorder sections after updating the selected section's index.
     *
     * @param int $newIndex
     * @param int $currentIndex
     * @param \App\Models\Section $section
     * @return void
     */
    protected function reorderSectionsAfterUpdate($newIndex, $currentIndex, $section)
    {
        // Fetch all sections ordered by index
        $sections = Section::orderBy('index')->get();

        // If the new index is less than the current index, shift sections with a higher index
        if ($newIndex < $currentIndex) {
            foreach ($sections as $sec) {
                // Only shift sections that have an index greater than or equal to the new index
                if ($sec->index >= $newIndex && $sec->index < $currentIndex && $sec->id != $section->id) {
                    $sec->index += 1;  // Increment index by 1
                    $sec->save();
                }
            }
        } elseif ($newIndex > $currentIndex) {
            // If the new index is greater, shift sections with an index greater than the current index
            foreach ($sections as $sec) {
                // Only shift sections that have an index greater than the new index
                if ($sec->index > $currentIndex && $sec->index <= $newIndex && $sec->id != $section->id) {
                    $sec->index -= 1;  // Decrement index by 1
                    $sec->save();
                }
            }
        }
    }





    public function reorderSectionItems(Request $request)
    {
        $orderedIds = $request->input('orderedIds');

        foreach ($orderedIds as $index => $id) {
            $section = Section::find($id);
            if ($section) {
                $section->index = $index + 1;
                $section->save();
            }
        }

        return response()->json(['success' => true]);
    }




}
