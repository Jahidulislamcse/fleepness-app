<?php

// app/Http/Controllers/SectionController.php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\Category;
use App\Models\SectionItem;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
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

        $background = $request->hasFile('background_image')
            ? $this->uploadImage($request->file('background_image'), 'sections/backgrounds/')
            : null;

        $banner_img = $request->hasFile('banner_image')
            ? $this->uploadImage($request->file('banner_image'), 'sections/banners/')
            : null;

        if ('search' === $validated['section_type']) {
            $lastIndex = \App\Models\Section::query()->where('section_type', 'search')->max('index');
        } else {
            $lastIndex = \App\Models\Section::query()->where('section_type', '!=', 'search')->max('index');
        }
        $newIndex = $lastIndex + 1;

        $catIndex = $this->nextCatIndex((int) $validated['category_id']);

        $section = Section::create([
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

        if (isset($validated['items']) && 0 < count($validated['items'])) {
            foreach ($request->items as $index => $item) {
                $itemIndex = $index + 1;
                $item_image = null;

                if ($request->hasFile("items.$index.image")) {
                    $uploadedFile = $request->file("items.$index.image");
                    $item_image = $this->uploadImage($uploadedFile, 'sections/items/');
                }

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

        return to_route('admin.sections.index')->with('success', 'Section created successfully');
    }

    private function uploadImage(?UploadedFile $image, string $folder): ?string
    {
        if (! $image instanceof UploadedFile) {
            return null;
        }

        $folder = trim($folder, '/');

        return $image->store('upload/'.$folder, 'r2');
    }

    public function edit($id): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $section = Section::with(['items' => function ($query) {
            $query->orderBy('index', 'asc');
        }])->findOrFail($id);

        $categories = Category::whereNull('parent_id')->get();

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

        $catCount = Section::where('category_id', $section->category_id)->count();
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
        $newIndex = $validated['index'] ?? $currentIndex;

        if ($currentIndex !== $newIndex) {
            $this->reorderSectionsAfterUpdate($newIndex, $currentIndex, $section);
        }

        $background = $section->getRawOriginal('background_image');
        if ($request->hasFile('background_image')) {
            $background = $this->uploadImage($request->file('background_image'), 'sections/backgrounds/');
        }

        $banner_img = $section->getRawOriginal('banner_image');
        if ($request->hasFile('banner_image')) {
            $banner_img = $this->uploadImage($request->file('banner_image'), 'sections/banners/');
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($section, $validated, $background, $banner_img, $newIndex, $request): void {
            $oldCategoryId = (int) $section->category_id;
            $newCategoryId = (int) $validated['category_id'];
            $oldCatIndex = (int) ($section->cat_index ?? 0);

            $maxInTarget = (int) \App\Models\Section::query()->where('category_id', $newCategoryId)->count();
            if ($newCategoryId !== $oldCategoryId) {
                $maxInTarget++;
            }

            $requestedCatIndex = $validated['cat_index'] ?? null;
            $targetCatIndex = max(1, min($requestedCatIndex ?? $maxInTarget, $maxInTarget));

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
            }

            $section->update([
                'section_name' => $validated['section_name'] ?? $section->section_name,
                'section_type' => $validated['section_type'] ?? $section->section_type,
                'section_title' => $validated['section_title'] ?? $section->section_title,
                'category_id' => $newCategoryId,
                'placement_type' => $validated['placement_type'] ?? $section->placement_type,
                'bio' => $validated['bio'] ?? $section->bio,
                'index' => $newIndex,
                'cat_index' => $targetCatIndex,
                'visibility' => $validated['visibility'] ?? $section->visibility,
                'background_image' => $background,
                'banner_image' => $banner_img,
            ]);

            $oldItems = $section->items()->get();
            $updatedItems = $request->input('items', []);

            foreach ($updatedItems as $i => $itemData) {
                $itemModel = $oldItems[$i] ?? new \App\Models\SectionItem;
                $itemModel->section_id = $section->id;

                if ($request->hasFile("items.$i.image")) {
                    $itemModel->image = $this->uploadImage($request->file("items.$i.image"), 'sections/items/');
                } elseif (! $itemModel->exists) {
                    $itemModel->image = null;
                }

                $itemModel->title = $itemData['title'] ?? $itemModel->title;
                $itemModel->bio = $itemData['bio'] ?? $itemModel->bio;
                $itemModel->tag_id = $itemData['tag_id'] ?? $itemModel->tag_id;
                $itemModel->index = $itemData['index'] ?? ($i + 1);
                $itemModel->visibility = $itemData['visibility'] ?? $itemModel->visibility ?? 1;
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
