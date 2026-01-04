<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\ProductImage;
use App\Models\ProductSize;
use App\Models\Stock;
use App\Models\SizeTemplate;
use App\Models\SizeTemplateItem;
use App\Models\SellerTags;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VendorProductController extends Controller
{
    public function index()
    {
        $data['products'] = Product::with('category')->latest()->whereNull('deleted_at')->where('user_id', auth()->id())->get();
        $data['categories'] = Category::whereNull('parent_id')->get();
        $data['size_templates'] = SizeTemplate::where('seller_id', auth()->id())->get();
        return view('vendor.products.index', $data);
    }

    public function getAllMyProducts(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $perPage = $request->input('per_page', 10);

        $products = Product::with([
                'category',
                'images',
                'sizes',
                'sizeTemplate.items'
            ])
            ->latest()
            ->whereNull('deleted_at')
            ->where('user_id', auth()->id())
            ->paginate($perPage);

        if ($products->isEmpty()) {
            return response()->json(['message' => 'No products found'], 404);
        }

        $products->getCollection()->transform(function ($product) {
            $data = $product->toArray();
            $data['tags_data'] = $product->tagCategories();
            return $data;
        });

        return response()->json([
            'success' => true,
            'products' => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'last_page' => $products->lastPage(),
                'next_page_url' => $products->nextPageUrl(),
                'prev_page_url' => $products->previousPageUrl(),
            ],
        ]);
    }

    public function getMyProducts()
    {
       $user = User::find(23);
        dd($user);

        $products = Product::with([
                'category',
                'sizes',
                'sizeTemplate.items'
            ])
            ->latest()
            ->whereNull('deleted_at')
            ->where('user_id', $user->id())
            ->get();

        // If you also want to append full tag data:
        $products->transform(function($product) {
            $data = $product->toArray();

            // Add resolved categories for tags (if any)
            $data['tags_data'] = $product->tagCategories();

            return $data;
        });

        return response()->json([
            'success'  => true,
            'products' => $products,
        ]);
    }

    public function getSingleProduct($id)
    {
        $product = Product::with([
                'images',
                'category',
                'sizes',
                'sizeTemplate.items'
            ])
            ->whereNull('deleted_at')
            ->where('user_id', auth()->id())
            ->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $data = $product->toArray();

        // Add resolved categories for tags (if any)
        $data['tags_data'] = $product->tagCategories();

        return response()->json([
            'success' => true,
            'product' => $data,
        ]);
    }



   public function search(Request $request)
    {
        $query = $request->input('query');
        $tag = $request->input('tag');
        $perPage = $request->input('per_page', 5);
        $page = $request->input('page', 1);

        $products = collect();

        // If neither query nor tag is provided, return all products
        if (!$query && !$tag) {
            $products = Product::with('images')->where('user_id', auth()->id())->get();
        }

        // Search by query
        if ($query) {
            $productsByQuery = Product::with('images')->where('user_id', auth()->id())
                ->where(function ($queryBuilder) use ($query) {
                    $queryBuilder->where('name', 'LIKE', "%{$query}%")
                        ->orWhere('short_description', 'LIKE', "%{$query}%")
                        ->orWhere('long_description', 'LIKE', "%{$query}%")
                        ->orWhere('slug', 'LIKE', "%{$query}%");
                })
                ->get();

            $products = $products->merge($productsByQuery);
        }

        // Search by tag
        if ($tag) {
            $productsByTag = Product::with('images')->where('user_id', auth()->id())
                ->get()
                ->filter(function ($product) use ($tag) {
                    $tags = json_decode($product->tags, true);
                    if (is_string($tags)) {
                        $tags = json_decode($tags, true);
                    }
                    return is_array($tags) && in_array($tag, $tags);
                });

            $products = $products->merge($productsByTag);
        }

        // Remove duplicates
        $products = $products->unique('id')->values();

        // Manual pagination
        $sliced = $products->slice(($page - 1) * $perPage, $perPage)->values();

        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $sliced,
            $products->count(),
            $perPage,
            $page,
            ['path' => url()->current()]
        );

        return response()->json([
            'success' => true,
            'products' => $paginated->items(),
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'last_page' => $paginated->lastPage(),
                'next_page_url' => $paginated->nextPageUrl(),
                'prev_page_url' => $paginated->previousPageUrl(),
            ],
        ]);
    }




    protected function generateUniqueCode()
    {
        do {
            $code = random_int(100000, 999999);
        } while (Product::where('code', $code)->exists());

        return $code;
    }


    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'long_description' => 'nullable|string',
                'short_description' => 'required|string',
                'size_template_id' => 'required|exists:size_templates,id',
                'quantity' => 'required|integer|min:0',
                'selling_price' => 'required|numeric|min:0',
                'discount_price' => 'nullable|numeric|min:0',
            ]);

            DB::beginTransaction();

            $validated['code'] = $this->generateUniqueCode();
            $validated['status'] = 'active';
            $validated['admin_approval'] = 'approved';
            $validated['user_id'] = auth()->id();
            $validated['tags'] = json_encode($request->tags);

            $validated['reviews'] = $this->generateRandomReviews();
            $validated['time'] = $this->generateRandomTime();
            $validated['discount'] = $this->generateRandomDiscount();

            $product = Product::create($validated);

            $sizes = SizeTemplateItem::where('template_id', $request->size_template_id)->get();
            foreach ($sizes as $size) {
                ProductSize::create([
                    'product_id' => $product->id,
                    'size_name' => $size->size_name,
                    'size_value' => $size->size_value,
                ]);
            }

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $photo) {
                    $path = $photo->store('products/images'); 

                    ProductImage::create([
                        'product_id' => $product->id,
                        'path' => $path, 
                        'alt_text' => $request->input('alt_text', ''),
                    ]);
                }
            }

            if (!empty($request->tags)) {
                $sellerTag = SellerTags::firstOrNew([
                    'vendor_id' => auth()->id(),
                ]);

                $sellerTag->tags = array_merge($sellerTag->tags ?? [], $request->tags);
                $sellerTag->save();
            }

            $tags = $product->tags; 
            $tagId = isset($tags[0]) ? (int) $tags[0] : null;

            $tagName = null;
            $categoryId = null;

            if ($tagId) {
                $tagCategory = Category::find($tagId);

                if ($tagCategory) {
                    $tagName = $tagCategory->name;

                    $parent = Category::find($tagCategory->parent_id);
                    $grandParent = $parent ? Category::find($parent->parent_id) : null;

                    if ($grandParent) {
                        $categoryId = $grandParent->id;
                    }
                }
            }

            if ($categoryId) {
                $product->update(['category_id' => $categoryId]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'product' => $product,
                'sizes' => ProductSize::where('product_id', $product->id)->get(),
                'images' => ProductImage::where('product_id', $product->id)->get(),
                'tag' => $tagName,
                'category_name' => $categoryId ? Category::find($categoryId)?->name : null,
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function generateRandomReviews(): string
    {
        $random = rand(100, 150);
        $value = $random / 100;

        return number_format($value, 2) . 'k';
    }



    private function generateRandomTime(): string
    {
        $minutes = rand(30, 90);
        if ($minutes >= 60) {
            $hours = intdiv($minutes, 60);
            $remaining = $minutes % 60;
            return $remaining > 0 ? "{$hours}h {$remaining}m" : "{$hours}h";
        }
        return "{$minutes}m";
    }

    private function generateRandomDiscount(): float
    {
        return rand(1, 20); 
    }



    public function edit(Product $product)
    {
        $productImageCount = ProductImage::where('product_id', $product->id)->count();
        $categories = Category::whereNull('parent_id')->get();
        return view('vendor.products.product_edit', compact('product', 'categories', 'productImageCount'));
    }

   public function update(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);

            if ($product->user_id !== auth()->id()) {
                return $request->wantsJson()
                    ? response()->json(['success' => false, 'message' => 'You are not authorized to update this product.'], 403)
                    : redirect()->back()->with('error', 'You are not authorized to update this product.');
            }

            $validated = $request->validate([
                'category_id' => 'nullable|exists:categories,id',
                'name' => 'nullable|string|max:255',
                'long_description' => 'nullable|string',
                'short_description' => 'nullable|string',
                'size_template_id' => 'nullable|exists:size_templates,id',
                'quantity' => 'nullable|integer|min:0',
                'selling_price' => 'nullable|numeric|min:0',
                'discount_price' => 'nullable|numeric|min:0',
            ]);

            $validated['tags'] = json_encode($request->tags);
            $product->update($validated);

            if ($request->has('sizes')) {
                $submittedSizes = $request->sizes;

                foreach ($submittedSizes as $sizeData) {
                    $sizeData['size_name'] = strtolower($sizeData['size_name']);  

                    $size = ProductSize::where('product_id', $product->id)
                        ->where('size_name', $sizeData['size_name'])
                        ->first();

                    if ($size) {
                        if ($sizeData['available'] === 'false') {
                            $size->delete();
                        } else {
                            $size->size_value = $sizeData['size_value'];
                            $size->save();
                        }
                    } else {
                        if ($sizeData['available'] === 'true') {
                            ProductSize::create([
                                'product_id' => $product->id,
                                'size_name' => $sizeData['size_name'], 
                                'size_value' => $sizeData['size_value'],
                            ]);
                        }
                    }
                }
            }

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $photo) {
                    $path = $photo->store('products/images'); 

                    ProductImage::create([
                        'product_id' => $product->id,
                        'path' => $path, 
                        'alt_text' => $request->input('alt_text', ''),
                    ]);
                }
            }

            if (!empty($request->tags)) {
                $sellerTag = SellerTags::firstOrNew([
                    'vendor_id' => auth()->id(),
                ]);

                $sellerTag->tags = array_merge($sellerTag->tags ?? [], $request->tags);
                $sellerTag->save();
            }

            $tags = $product->tags;
            $tag = $tags ? $tags[0] : null; 

            $tagName = null;
            $category = null;
            if ($tag) {
                $tagName = Category::where('id', $tag)->pluck('name')->first(); 
                $tagCategory = Category::where('id', $tag)->first();

                if ($tagCategory) {
                    $parentCategory = Category::where('id', $tagCategory->parent_id)->first();

                    if ($parentCategory) {
                        $grandParentCategory = Category::where('id', $parentCategory->parent_id)->first();

                        if ($grandParentCategory) {
                            $category = $grandParentCategory->id;
                        }
                    }
                }
            }
            if ($category) {
                $product->update([
                    'category_id' => $category,
                ]);
            }
            $categoryName = $category ? Category::where('id', $category)->pluck('name')->first() : null;

            $productSizes = ProductSize::where('product_id', $product->id)->get();

            return response()->json(['success' => true,
            'message' => 'Product updated successfully',
            'product' => $product,
            'sizes' => $productSizes,
            'images' => ProductImage::where('product_id', $product->id)->get(),
            'tag' => $tagName,
            'category_name' => $categoryName,
            ]);

            } catch (\Exception $e) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }
    }

    public function deleteImage($id, $img)
    {
        try {
            $product = Product::findOrFail($id);

            if ($product->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: You do not own this product.',
                ], 403);
            }

            $image = ProductImage::where('id', $img)
                                ->where('product_id', $product->id)
                                ->firstOrFail();

            if (\Storage::exists($image->path)) {
                \Storage::delete($image->path);
            }

            $image->delete();

            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully.',
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product or image not found.',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the image.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }




    public function destroy($id, Request $request)
    {
        $product = Product::findOrFail($id);
        // dd($product);

        // $order = OrderItem::where('product_id', $product->id)->count();

        // if ($order > 0) {
        //     $message = 'You cannot delete this product because it has related orders. Please delete the orders first.';

        //     if ($request->wantsJson()) {
        //         return response()->json([
        //             'success' => false,
        //             'message' => $message,
        //         ], 403);
        //     }

        //     return redirect()->route('vendor.products.index')->with('error', $message);
        // }

        $product->update([
            'deleted_at' => now(),
        ]);

        $message = 'Product deleted successfully.';

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'product' => $product,
            ]);
        }

        return redirect()->route('vendor.products.index')->with('success', $message);
    }

    public function inactive(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $product->update([
            'status' => 'inactive',
        ]);

        $message = 'Product inactivated successfully.';


        return response()->json([
            'success' => true,
            'message' => $message,
            'product' => $product,
        ]);
    }

    public function active(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $product->update([
            'status' => 'active',
        ]);

        $message = 'Product activated successfully.';

        return response()->json([
            'success' => true,
            'message' => $message,
            'product' => $product,
        ]);
    }


    public function ImageDelete($id)
    {
        $data = ProductImage::find($id);
        if (!$data) {
            return response()->json(['error' => 'Image not found.'], 404);
        }

        if (file_exists(public_path($data->path))) {
            unlink(public_path($data->path));
        }

        $data->delete();
        return back();
    }


    public function StockDelete($id)
    {
        $data = Stock::find($id);
        if (file_exists($data->photo)) {
            unlink(public_path($data->photo));
        }
        $data->delete();

        $notification = array(
            'message' => 'Data Deleted Successfully',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }
}
