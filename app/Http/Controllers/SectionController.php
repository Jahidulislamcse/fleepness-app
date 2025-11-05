<?php

// app/Http/Controllers/SectionController.php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\Category;
use App\Models\SectionItem;
use Illuminate\Http\Request;
use App\Http\Resources\SectionResource;

class SectionController extends Controller
{
    public function index(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
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

        return view('admin.sections.index', ['sections' => $sections, 'searchSections' => $searchSections]);
    }

    public function sections(Request $request)
    {
        $categoryName = trim((string) $request->query('category', ''));

        $query = Section::with(['category', 'items.tag'])
            ->where('section_type', '!=', 'search');

        if ('' !== $categoryName) {
            $query->whereIn('placement_type', ['category', 'global'])
                ->whereHas('category', function (\Illuminate\Contracts\Database\Query\Builder $q) use ($categoryName): void {
                    $q->where('name', '=', $categoryName);
                })
                ->orderBy('cat_index', 'asc')
                ->orderBy('index', 'asc');
        } else {
            $query->whereIn('placement_type', ['global', 'all_only'])
                ->orderBy('index', 'asc');
        }

        $sections = $query->paginate(5);

        return SectionResource::collection($sections);
    }

    public function searchSection()
    {
        $section = \App\Models\Section::query()->where('section_type', 'search')
            ->with(['category', 'items.tag'])
            ->orderBy('index')
            ->paginate(5);

        return SectionResource::collection($section);
    }

    public function create(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $categories = \App\Models\Category::query()->whereNull('parent_id')->get();

        return view('admin.sections.create', ['categories' => $categories]);
    }

    public function store(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'section_name' => ['nullable', 'string', 'max:255'],
            'section_type' => ['nullable', 'string'],
            'section_title' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'placement_type' => ['required', 'in:category,global,all_only'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'visibility' => ['boolean'],
            'background_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,svg'],
            'banner_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,svg'],
            'items' => ['array', 'nullable'],
            'items.*.image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,svg'],
            'items.*.title' => ['nullable', 'string'],
            'items.*.bio' => ['nullable', 'string'],
            'items.*.tag_id' => ['nullable', 'string'],
            'items.*.visibility' => ['nullable', 'boolean'],
        ]);

        $background = null;
        $banner_img = null;

        // Handle the background image upload
        if ($request->hasFile('background_image')) {
            $photo = $request->file('background_image');
            $name_gen = hexdec(uniqid()).'.'.$photo->getClientOriginalExtension();
            $folder = 'slider/';
            if (! file_exists(public_path('upload/'.$folder))) {
                mkdir(public_path('upload/'.$folder), 0777, true);
            }
            $photo->move(public_path('upload/'.$folder), $name_gen);
            $background = 'upload/'.$folder.$name_gen;
        }

        // Handle the banner image upload
        if ($request->hasFile('banner_image')) {
            $banner = $request->file('banner_image');
            $name_gen_banner = hexdec(uniqid()).'.'.$banner->getClientOriginalExtension();
            $folder = 'slider/';
            if (! file_exists(public_path('upload/'.$folder))) {
                mkdir(public_path('upload/'.$folder), 0777, true);
            }
            $banner->move(public_path('upload/'.$folder), $name_gen_banner);
            $banner_img = 'upload/'.$folder.$name_gen_banner;
        }

        // $lastIndex = Section::max('index');

        if ('search' === $validated['section_type']) {
            $lastIndex = \App\Models\Section::query()->where('section_type', 'search')->max('index');
        } else {
            $lastIndex = \App\Models\Section::query()->where('section_type', '!=', 'search')->max('index');
        }
        $newIndex = $lastIndex + 1;

        $catIndex = $this->nextCatIndex((int) $validated['category_id']);

        // Create the section record
        $section = \App\Models\Section::query()->create([
            'section_name' => $validated['section_name'],
            'section_type' => $validated['section_type'],
            'section_title' => $validated['section_title'],
            'category_id' => $validated['category_id'],
            'bio' => $validated['bio'] ?? null,
            'visibility' => $validated['visibility'] ?? false,
            'index' => $newIndex,
            'background_image' => $background,
            'banner_image' => $banner_img,
            'placement_type' => $validated['placement_type'],
            'cat_index' => $catIndex,
        ]);

        // Handle storing the section items
        if (isset($validated['items']) && 0 < count($validated['items'])) {
            foreach ($request->items as $index => $item) {
                $item_image = $item['image'] ?? null;
                $image_path = null;

                $itemIndex = $index + 1;

                if ($item_image && $request->hasFile('items.'.$index.'.image')) {
                    $image_name = hexdec(uniqid()).'.'.$item_image->getClientOriginalExtension();
                    $folder = 'slider/';
                    $image_path = public_path('upload/'.$folder);
                    if (! file_exists($image_path)) {
                        mkdir($image_path, 0777, true);
                    }
                    $item_image->move($image_path, $image_name);
                    $item_image = 'upload/'.$folder.$image_name;
                }

                // Create the section item
                \App\Models\SectionItem::query()->create([
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

        return to_route('admin.sections.index')->with('success', 'Section created successfully');
    }

    public function edit($id): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $section = \App\Models\Section::query()->findOrFail($id);
        $categories = \App\Models\Category::query()->whereNull('parent_id')->get();

        if ('search' === $section->section_type) {
            $sectionsGroup = \App\Models\Section::query()->where('section_type', 'search')
                ->orderBy('index')
                ->get();
        } else {
            $sectionsGroup = \App\Models\Section::query()->where('section_type', '!=', 'search')
                ->orderBy('index')
                ->get();
        }

        $availableIndices = $sectionsGroup->where('id', '!=', $section->id)->pluck('index')->toArray();

        $catCount = \App\Models\Section::query()->where('category_id', $section->category_id)->count();
        $availableCatPositions = range(1, max(1, $catCount));

        return view('admin.sections.edit', ['section' => $section, 'categories' => $categories, 'availableIndices' => $availableIndices, 'availableCatPositions' => $availableCatPositions]);
    }

    public function update(Request $request, $id)
    {
        $section = \App\Models\Section::query()->findOrFail($id);

        $validated = $request->validate([
            'section_name' => ['nullable', 'string', 'max:255'],
            'section_type' => ['nullable', 'string'],
            'section_title' => ['nullable', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'placement_type' => ['required', 'in:category,global,all_only'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'index' => ['nullable', 'integer', 'min:1'],
            'cat_index' => ['nullable', 'integer', 'min:1'],
            'visibility' => ['boolean'],
            'background_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,svg'],
            'banner_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,svg'],
            'items' => ['array', 'nullable'],
            'items.*.image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,svg'],
            'items.*.title' => ['nullable', 'string'],
            'items.*.bio' => ['nullable', 'string'],
            'items.*.tag_id' => ['nullable', 'string'],
            'items.*.index' => ['nullable', 'integer'],
            'items.*.visibility' => ['nullable', 'boolean'],
        ]);

        $currentIndex = (int) $section->index;
        $newIndex = isset($validated['index']) ? (int) $validated['index'] : $currentIndex;

        if ($currentIndex !== $newIndex) {
            $this->reorderSectionsAfterUpdate($newIndex, $currentIndex, $section);
        }

        $background = $section->background_image;
        if ($request->hasFile('background_image')) {
            $photo = $request->file('background_image');
            $name_gen = hexdec(uniqid()).'.'.$photo->getClientOriginalExtension();
            $folder = 'slider/';
            $photo->move(public_path('upload/'.$folder), $name_gen);
            $background = 'upload/'.$folder.$name_gen;
        }

        $banner_img = $section->banner_image;
        if ($request->hasFile('banner_image')) {
            $banner = $request->file('banner_image');
            $name_gen_banner = hexdec(uniqid()).'.'.$banner->getClientOriginalExtension();
            $folder = 'slider/';
            $banner->move(public_path('upload/'.$folder), $name_gen_banner);
            $banner_img = 'upload/'.$folder.$name_gen_banner;
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($section, $validated, $background, $banner_img, $newIndex, $request): void {
            $oldCategoryId = (int) $section->category_id;
            $newCategoryId = (int) $validated['category_id'];
            $oldCatIndex = (int) ($section->cat_index ?? 0);

            $maxInTarget = (int) \App\Models\Section::query()->where('category_id', $newCategoryId)->count();
            if ($newCategoryId !== $oldCategoryId) {
                $maxInTarget += 1;
            }
            $requestedCatIndex = isset($validated['cat_index']) ? (int) $validated['cat_index'] : null;
            $targetCatIndex = $requestedCatIndex ?? $maxInTarget; // default to end
            if (1 > $targetCatIndex) {
                $targetCatIndex = 1;
            }
            if ($targetCatIndex > $maxInTarget) {
                $targetCatIndex = $maxInTarget;
            }

            if ($newCategoryId !== $oldCategoryId) {
                if (0 < $oldCatIndex) {
                    \Illuminate\Support\Facades\DB::table('sections')
                        ->where('category_id', $oldCategoryId)
                        ->where('id', '!=', $section->id)
                        ->where('cat_index', '>', $oldCatIndex)
                        ->decrement('cat_index');
                }

                \Illuminate\Support\Facades\DB::table('sections')
                    ->where('category_id', $newCategoryId)
                    ->where('cat_index', '>=', $targetCatIndex)
                    ->increment('cat_index');

                $section->update([
                    'section_name' => $validated['section_name'],
                    'section_type' => $validated['section_type'] ?? $section->section_type,
                    'section_title' => $validated['section_title'],
                    'category_id' => $newCategoryId,
                    'placement_type' => $validated['placement_type'],
                    'bio' => $validated['bio'] ?? null,
                    'index' => $newIndex,
                    'cat_index' => $targetCatIndex,
                    'visibility' => $validated['visibility'] ?? false,
                    'background_image' => $background,
                    'banner_image' => $banner_img,
                ]);
            } else {
                $current = (int) ($section->cat_index ?? 0);

                if (0 === $current) {
                    \Illuminate\Support\Facades\DB::table('sections')
                        ->where('category_id', $newCategoryId)
                        ->where('cat_index', '>=', $targetCatIndex)
                        ->increment('cat_index');

                    $section->update([
                        'section_name' => $validated['section_name'],
                        'section_type' => $validated['section_type'] ?? $section->section_type,
                        'section_title' => $validated['section_title'],
                        'category_id' => $newCategoryId,
                        'placement_type' => $validated['placement_type'],
                        'bio' => $validated['bio'] ?? null,
                        'index' => $newIndex,
                        'cat_index' => $targetCatIndex,
                        'visibility' => $validated['visibility'] ?? false,
                        'background_image' => $background,
                        'banner_image' => $banner_img,
                    ]);
                } elseif ($targetCatIndex !== $current) {
                    if ($targetCatIndex < $current) {
                        \Illuminate\Support\Facades\DB::table('sections')
                            ->where('category_id', $newCategoryId)
                            ->where('id', '!=', $section->id)
                            ->whereBetween('cat_index', [$targetCatIndex, $current - 1])
                            ->increment('cat_index');
                    } else {
                        \Illuminate\Support\Facades\DB::table('sections')
                            ->where('category_id', $newCategoryId)
                            ->where('id', '!=', $section->id)
                            ->whereBetween('cat_index', [$current + 1, $targetCatIndex])
                            ->decrement('cat_index');
                    }

                    $section->update([
                        'section_name' => $validated['section_name'],
                        'section_type' => $validated['section_type'] ?? $section->section_type,
                        'section_title' => $validated['section_title'],
                        'category_id' => $newCategoryId,
                        'placement_type' => $validated['placement_type'],
                        'bio' => $validated['bio'] ?? null,
                        'index' => $newIndex,
                        'cat_index' => $targetCatIndex,
                        'visibility' => $validated['visibility'] ?? false,
                        'background_image' => $background,
                        'banner_image' => $banner_img,
                    ]);
                } else {
                    $section->update([
                        'section_name' => $validated['section_name'],
                        'section_type' => $validated['section_type'] ?? $section->section_type,
                        'section_title' => $validated['section_title'],
                        'category_id' => $newCategoryId,
                        'placement_type' => $validated['placement_type'],
                        'bio' => $validated['bio'] ?? null,
                        'index' => $newIndex,
                        'visibility' => $validated['visibility'] ?? false,
                        'background_image' => $background,
                        'banner_image' => $banner_img,
                    ]);
                }
            }

            $oldItems = $section->items()->get();
            $updatedItems = $request->input('items', []);

            foreach ($updatedItems as $i => $itemData) {
                $itemModel = $oldItems[$i] ?? new \App\Models\SectionItem;
                $itemModel->section_id = $section->id;

                $uploadedImage = $request->file("items.$i.image");
                if ($uploadedImage) {
                    $imageName = hexdec(uniqid()).'.'.$uploadedImage->getClientOriginalExtension();
                    $folder = 'slider/';
                    $uploadedImage->move(public_path('upload/'.$folder), $imageName);
                    $itemModel->image = 'upload/'.$folder.$imageName;
                } elseif (! $itemModel->exists) {
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
        });

        return to_route('admin.sections.index')->with('success', 'Section updated successfully');
    }

    protected function reorderSectionsAfterUpdate($newIndex, $currentIndex, Section $section)
    {
        if ('search' === $section->section_type) {
            // Reorder only within 'search' sections
            $sections = \App\Models\Section::query()->where('section_type', 'search')
                ->orderBy('index')
                ->get();
        } else {
            // Reorder among all sections except 'search'
            $sections = \App\Models\Section::query()->where('section_type', '!=', 'search')
                ->orderBy('index')
                ->get();
        }

        if ($newIndex < $currentIndex) {
            foreach ($sections as $sec) {
                if (
                    $sec->index >= $newIndex &&
                    $sec->index < $currentIndex &&
                    $sec->id !== $section->id
                ) {
                    $sec->index += 1;
                    $sec->save();
                }
            }
        } elseif ($newIndex > $currentIndex) {
            foreach ($sections as $sec) {
                if (
                    $sec->index > $currentIndex &&
                    $sec->index <= $newIndex &&
                    $sec->id !== $section->id
                ) {
                    $sec->index -= 1;
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
            $section = \App\Models\Section::query()->find($id);
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

        $deletedIndex = (int) $section->index;
        $categoryId = (int) $section->category_id;
        $deletedCatIndex = $section->cat_index;
        $isSearchType = 'search' === $section->section_type;

        $this->deleteFileIfExists($section->background_image);
        $this->deleteFileIfExists($section->banner_image);

        foreach ($section->items as $item) {
            $this->deleteFileIfExists($item->image);
            $item->delete();
        }

        $section->delete();

        $siblingsToShift = Section::query()
            ->when(
                $isSearchType,
                fn ($q) => $q->where('section_type', 'search'),
                fn ($q) => $q->where('section_type', '!=', 'search')
            )
            ->where('index', '>', $deletedIndex)
            ->orderBy('index', 'asc')
            ->get();

        foreach ($siblingsToShift as $sibling) {
            $sibling->index -= 1;
            $sibling->save();
        }

        if (! is_null($deletedCatIndex)) {
            \Illuminate\Support\Facades\DB::table('sections')
                ->where('category_id', $categoryId)
                ->where('cat_index', '>', $deletedCatIndex)
                ->decrement('cat_index');
        }

        return to_route('admin.sections.index')
            ->with('success', 'Section deleted and indices reordered successfully.');
    }

    protected function deleteFileIfExists(?string $path): void
    {
        if (! $path) {
            return;
        }

        $full = public_path($path);
        if (is_file($full) && file_exists($full)) {
            @unlink($full);
        }
    }

    protected function nextCatIndex(int $categoryId): int
    {
        return (int) \App\Models\Section::query()->where('category_id', $categoryId)->max('cat_index') + 1;
    }

    protected function compactCategoryAfterRemoval(int $categoryId, int $removedIndex, ?int $exceptId = null): void
    {
        \App\Models\Section::query()->where('category_id', $categoryId)
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->where('cat_index', '>', $removedIndex)
            ->orderBy('cat_index', 'asc')
            ->get()
            ->each(function ($s): void {
                $s->cat_index -= 1;
                $s->save();
            });
    }
}
