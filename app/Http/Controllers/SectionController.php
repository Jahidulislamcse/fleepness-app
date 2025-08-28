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
        // Non-search sections
        $sections = Section::with('items')
            ->whereNot('section_type', 'search')
            ->orderBy('index', 'asc')
            ->get();

        // Search sections
        $searchSections = Section::with('items')
            ->where('section_type', 'search')
            ->orderBy('index', 'asc')
            ->get();

        return view('admin.sections.index', compact('sections', 'searchSections'));
    }

    // public function sections()
    // {
    //    $sections = \App\Models\Section::with(['category', 'items.tag'])
    //     ->whereNot('section_type', 'search')
    //     ->orderBy('index')
    //     ->get();
    //     return SectionResource::collection($sections);
    // }

    public function sections(Request $request)
    {
        $categoryName = $request->query('category'); // reads ?category=w from URL

        $query = \App\Models\Section::with(['category', 'items.tag'])
            ->whereNot('section_type', 'search')
            ->orderBy('index');

        if ($categoryName) {
            $query->whereHas('category', function ($q) use ($categoryName) {
                $q->where('name', 'LIKE', '%' . $categoryName . '%');
            });
        }

        $sections = $query->get();

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
        'section_type' => 'nullable|string',
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

    // $lastIndex = Section::max('index');

    if ($validated['section_type'] === 'search') {
        $lastIndex = Section::where('section_type', 'search')->max('index');
    } else {
        $lastIndex = Section::where('section_type', '!=', 'search')->max('index');
    }
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

        if ($section->section_type === 'search') {
            // Only fetch 'search' sections
            $sectionsGroup = Section::where('section_type', 'search')
                ->orderBy('index')
                ->get();
        } else {
            // Fetch all non-'search' sections
            $sectionsGroup = Section::where('section_type', '!=', 'search')
                ->orderBy('index')
                ->get();
        }

        // Generate available indices excluding the current section's index
        $availableIndices = $sectionsGroup->where('id', '!=', $section->id)->pluck('index')->toArray();

        return view('admin.sections.edit', compact('section', 'categories', 'availableIndices'));
}






    public function update(Request $request, $id)
    {
        $section = Section::findOrFail($id);

        // Validate request data
        $validated = $request->validate([
            'section_name' => 'nullable|string|max:255',
            'section_type' => 'nullable|string',
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
    protected function reorderSectionsAfterUpdate($newIndex, $currentIndex, Section $section)
    {
        if ($section->section_type === 'search') {
            // Reorder only within 'search' sections
            $sections = Section::where('section_type', 'search')
                ->orderBy('index')
                ->get();
        } else {
            // Reorder among all sections except 'search'
            $sections = Section::where('section_type', '!=', 'search')
                ->orderBy('index')
                ->get();
        }

        if ($newIndex < $currentIndex) {
            foreach ($sections as $sec) {
                if (
                    $sec->index >= $newIndex &&
                    $sec->index < $currentIndex &&
                    $sec->id != $section->id
                ) {
                    $sec->index += 1;  // Shift down
                    $sec->save();
                }
            }
        } elseif ($newIndex > $currentIndex) {
            foreach ($sections as $sec) {
                if (
                    $sec->index > $currentIndex &&
                    $sec->index <= $newIndex &&
                    $sec->id != $section->id
                ) {
                    $sec->index -= 1;  // Shift up
                    $sec->save();
                }
            }
        }

        // Update the moved section's index
        $section->index = $newIndex;
        $section->save();
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


    public function destroy($id)
    {
        $section = Section::with('items')->findOrFail($id);

        $deletedIndex = $section->index;
        $isSearchType = $section->section_type === 'search';

        $this->deleteFileIfExists($section->background_image);
        $this->deleteFileIfExists($section->banner_image);

        foreach ($section->items as $item) {
            $this->deleteFileIfExists($item->image);
            $item->delete();
        }

        $section->delete();

         $siblingsToShift = Section::query()
            ->when($isSearchType,
                fn ($q) => $q->where('section_type', 'search'),
                fn ($q) => $q->where('section_type', '!=', 'search')
            )
            ->where('index', '>', $deletedIndex)
            ->orderBy('index', 'asc')
            ->get();

        foreach ($siblingsToShift as $sibling) {
            $sibling->index = $sibling->index - 1;  
            $sibling->save();
        }

        return redirect()
            ->route('admin.sections.index')
            ->with('success', 'Section deleted and indices reordered successfully.');
    }

    protected function deleteFileIfExists(?string $path): void
    {
        if (!$path) return;

        $full = public_path($path);
        if (is_file($full) && file_exists($full)) {
            @unlink($full);
        }
    }

}
