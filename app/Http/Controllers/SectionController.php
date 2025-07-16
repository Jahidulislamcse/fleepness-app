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
            'section_type' => 'nullable|string|unique:sections,section_type',
            'section_title' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'index' => 'nullable|integer',
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

        // dd($validated);

        // Initialize background and banner image variables as null by default
        $background = null;
        $banner_img = null;

        // Handle the background image upload
       if ($request->hasFile('background_image')) {
            $photo = $request->file('background_image');
            $name_gen = hexdec(uniqid()) . '.' . $photo->getClientOriginalExtension();
            $folder = 'sections/';
            if (!file_exists(public_path('upload/' . $folder))) {
                mkdir(public_path('upload/' . $folder), 0777, true);
            }
            $photo->move(public_path('upload/' . $folder), $name_gen);
            $background = 'upload/' . $folder . $name_gen;
        }

        if ($request->hasFile('banner_image')) {
            $banner = $request->file('banner_image');
            $name_gen_banner = hexdec(uniqid()) . '.' . $banner->getClientOriginalExtension();
            $folder = 'sections/';
            if (!file_exists(public_path('upload/' . $folder))) {
                mkdir(public_path('upload/' . $folder), 0777, true);
            }
            $banner->move(public_path('upload/' . $folder), $name_gen_banner);
            $banner_img = 'upload/' . $folder . $name_gen_banner;
        }


        // Create the section record
        $section = Section::create([
            'section_name' => $validated['section_name'],
            'section_type' => $validated['section_type'],
            'section_title' => $validated['section_title'],
            'category_id' => $validated['category_id'],
            'index' => $validated['index'],
            'visibility' => $validated['visibility'] ?? false,
            'background_image' =>  $background,
            'banner_image' =>  $banner_img,
        ]);

        // Handle storing the section items
        if (isset($validated['items']) && count($validated['items']) > 0) {
            foreach ($request->items as $index => $item) {
                $item_image = isset($item['image']) ? $item['image'] : null;
                $image_path = null;

                if ($item_image && $request->hasFile('items.' . $index . '.image')) {
                    $image_name = hexdec(uniqid()) . '.' . $item_image->getClientOriginalExtension();
                    $folder = 'sections/';
                    $image_path = public_path('upload/' . $folder);
                    if (!file_exists($image_path)) {
                        mkdir($image_path, 0777, true);
                    }
                    $item_image->move($image_path, $image_name);
                    $item_image = 'upload/' . $folder . $image_name;
                }

                SectionItem::create([
                    'section_id' => $section->id,
                    'image' => $item_image,
                    'title' => $item['title'] ?? null,
                    'bio' => $item['bio'] ?? null,
                    'tag_id' => $item['tag_id'] ?? null,
                    'index' => $item['index'] ?? null,
                    'visibility' => $item['visibility'] ?? 1,
                ]);
            }
        }


        return redirect()->route('admin.sections.index')->with('success', 'Section created successfully');
    }



   public function edit($id)
    {
        $section = Section::with('items')->findOrFail($id);
        $categories = Category::whereNull('parent_id')->get(); 

        return view('admin.sections.edit', compact('section', 'categories'));
    }


  public function update(Request $request, $id)
    {
        $section = Section::findOrFail($id);

        $validated = $request->validate([
            'section_name' => 'nullable|string|max:255',
            'section_type' => 'nullable|string|unique:sections,section_type,' . $id,
            'section_title' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'index' => 'nullable|integer',
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

       $background = $section->background_image;
        if ($request->hasFile('background_image')) {
            $photo = $request->file('background_image');
            $name_gen = hexdec(uniqid()) . '.' . $photo->getClientOriginalExtension();
            $folder = 'sections/';
            $photo->move(public_path('upload/' . $folder), $name_gen);
            $background = 'upload/' . $folder . $name_gen;
        }

        $banner_img = $section->banner_image;
        if ($request->hasFile('banner_image')) {
            $banner = $request->file('banner_image');
            $name_gen_banner = hexdec(uniqid()) . '.' . $banner->getClientOriginalExtension();
            $folder = 'sections/';
            $banner->move(public_path('upload/' . $folder), $name_gen_banner);
            $banner_img = 'upload/' . $folder . $name_gen_banner;
        }


        $section->update([
            'section_name' => $validated['section_name'],
            'section_type' => $validated['section_type'] ?? $section->section_type,
            'section_title' => $validated['section_title'],
            'category_id' => $validated['category_id'],
            'index' => $validated['index'],
            'visibility' => $validated['visibility'] ?? false,
            'background_image' => $background,
            'banner_image' => $banner_img,
        ]);

        $oldItems = $section->items()->get();
        $updatedItems = $request->input('items', []);

        foreach ($updatedItems as $i => $itemData) {
            $itemModel = $oldItems[$i] ?? new \App\Models\SectionItem();
            $itemModel->section_id = $section->id;

            $uploadedImage = $request->file("items.$i.image");
            if ($uploadedImage) {
                $imageName = hexdec(uniqid()) . '.' . $uploadedImage->getClientOriginalExtension();
                $folder = 'sections/';
                $uploadedImage->move(public_path('upload/' . $folder), $imageName);
                $itemModel->image = 'upload/' . $folder . $imageName;
            } elseif (!$itemModel->exists) {
                $itemModel->image = null; 
            }

            $itemModel->title = $itemData['title'] ?? null;
            $itemModel->bio = $itemData['bio'] ?? null;
            $itemModel->tag_id = $itemData['tag_id'] ?? null;
            $itemModel->index = $itemData['index'] ?? null;
            $itemModel->visibility = $itemData['visibility'] ?? 1;
            $itemModel->save();
        }


        if ($oldItems->count() > count($updatedItems)) {
            $oldItems->slice(count($updatedItems))->each->delete();
        }

        return redirect()->route('admin.sections.index')->with('success', 'Section updated successfully');
    }



}
