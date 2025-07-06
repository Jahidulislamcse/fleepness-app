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
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class VendorProductController extends Controller
{
    public function index()
    {
        $data['products'] = Product::with('category')->latest()->whereNull('deleted_at')->where('user_id', auth()->id())->get();
        $data['categories'] = Category::whereNull('parent_id')->get();
        $data['size_templates'] = SizeTemplate::where('seller_id', auth()->id())->get();
        return view('vendor.products.index', $data);
    }


    public function show()
    {
        $products = Product::with([
                'category',
                'sizes',
                'sizeTemplate.items' // eager-load template + its items
            ])
            ->latest()
            ->whereNull('deleted_at')
            ->where('user_id', auth()->id())
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



    protected function generateUniqueCode()
    {
        do {
            $code = random_int(100000, 999999);
        } while (Product::where('code', $code)->exists());

        return $code;
    }


    public function store(Request $request)
    {
        // dd($request->all());
        try {
            $validated = $request->validate([
                // 'category_id' => 'nullable|exists:categories,id',
                'name' => 'nullable|string|max:255',
                'long_description' => 'nullable|string',
                'short_description' => 'nullable|string',
                'size_template_id' => 'nullable|exists:size_templates,id',
                'quantity' => 'nullable|integer|min:0',
                'selling_price' => 'nullable|numeric|min:0',
                'discount_price' => 'nullable|numeric|min:0',
            ]);

            $validated['code'] = $this->generateUniqueCode();
            $validated['status'] = 'active';
            $validated['user_id'] = auth()->id();
            $validated['tags'] = json_encode($request->tags);

            $product = Product::create($validated);

            // Copy sizes from template
            if ($request->size_template_id) {
                $sizes = SizeTemplateItem::where('template_id', $request->size_template_id)->get();
                foreach ($sizes as $size) {
                    ProductSize::create([
                        'product_id' => $product->id,
                        'size_name' => $size->size_name,
                        'size_value' => $size->size_value,
                    ]);
                }
            }

            // Save images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $photo) {
                    $name_gen = hexdec(uniqid()) . '.' . $photo->getClientOriginalExtension();
                    $path = public_path('upload/product');

                    if (!file_exists($path)) {
                        mkdir($path, 0777, true);
                    }

                    $photo->move($path, $name_gen);

                    ProductImage::create([
                        'product_id' => $product->id,
                        'path' => 'upload/product/' . $name_gen,
                        'alt_text' => $request->input('alt_text', ''),
                    ]);
                }
            }

            // Save tags to seller_tags table
            if (!empty($request->tags)) {
                $sellerTag = SellerTags::firstOrNew([
                    'vendor_id' => auth()->id(),
                ]);

                // Simply store the tags without removing duplicates
                $sellerTag->tags = array_merge($sellerTag->tags ?? [], $request->tags);
                $sellerTag->save();
            }

            DB::commit();

            $tags = json_decode($product->tags, true);
            $tags = is_array($tags) ? $tags : [];

            $tagNames = Category::whereIn('id', $tags) // Fetch the category names based on the tag IDs
                                ->pluck('name')
                                ->toArray();
            $productSizes = ProductSize::where('product_id', $product->id)->get();

            return response()->json(['success' => true, 'message' => 'Product Created successfully', 'product' => $product, 'sizes' => $productSizes, 'tags' => $tagNames]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }


    public function edit(Product $product)
    {
        // dd($product);
        $productImageCount = ProductImage::where('product_id', $product->id)->count();
        $categories = Category::whereNull('parent_id')->get();
        return view('vendor.products.product_edit', compact('product', 'categories', 'productImageCount'));
    }

   public function update(Request $request, $id)
{
    // dd($request->all());
    try {
        // Find the product
        $product = Product::findOrFail($id);

        // Check if the product belongs to the logged-in user
        if ($product->user_id !== auth()->id()) {
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => 'You are not authorized to update this product.'], 403)
                : redirect()->back()->with('error', 'You are not authorized to update this product.');
        }

        // Validate input
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

        // Update the product details
        $validated['tags'] = json_encode($request->tags);
        $product->update($validated);

        // Handle sizes (retain only submitted size IDs)
        if ($request->has('sizes')) {
            $submittedSizes = $request->sizes;

            foreach ($submittedSizes as $sizeData) {
                $sizeData['size_name'] = strtolower($sizeData['size_name']);  // Convert size name to lowercase

                // Check if the size already exists for the product
                $size = ProductSize::where('product_id', $product->id)
                    ->where('size_name', $sizeData['size_name'])
                    ->first();

                // If the size exists, update its value if available is true
                if ($size) {
                    if ($sizeData['available'] === 'false') {
                        // If available is false, delete the size
                        $size->delete();
                    } else {
                        // Update the size value
                        $size->size_value = $sizeData['size_value'];
                        $size->save();
                    }
                } else {
                    if ($sizeData['available'] === 'true') {
                        ProductSize::create([
                            'product_id' => $product->id,
                            'size_name' => $sizeData['size_name'], // Ensure size name is lowercase
                            'size_value' => $sizeData['size_value'],
                        ]);
                    }
                }
            }
        }

        // Add new images if any
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $photo) {
                $name_gen = hexdec(uniqid()) . '.' . $photo->getClientOriginalExtension();
                $path = public_path('upload/product');

                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }

                $photo->move($path, $name_gen);

                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => 'upload/product/' . $name_gen,
                    'alt_text' => $request->input('alt_text', ''),
                ]);
            }
        }

        // 🆕 Save tags to seller_tags table (add new tags only)
        if (!empty($request->tags)) {
            $sellerTag = SellerTags::firstOrNew([
                'vendor_id' => auth()->id(),
            ]);

            $sellerTag->tags = array_merge($sellerTag->tags ?? [], $request->tags);
            $sellerTag->save();
        }

        $tags = json_decode($product->tags, true);
        $tags = is_array($tags) ? $tags : [];

        $tagNames = Category::whereIn('id', $tags) // Fetch the category names based on the tag IDs
                            ->pluck('name')
                            ->toArray();

        $productSizes = ProductSize::where('product_id', $product->id)->get();

        return response()->json(['success' => true, 'message' => 'Product updated successfully', 'product' => $product, 'sizes' => $productSizes, 'tags' => $tagNames]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
}







    public function destroy($id, Request $request)
    {
        $product = Product::findOrFail($id);
        // dd($product);

        $order = OrderItem::where('product_id', $product->id)->count();

        if ($order > 0) {
            $message = 'You cannot delete this product because it has related orders. Please delete the orders first.';

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 403);
            }

            return redirect()->route('vendor.products.index')->with('error', $message);
        }

        $product->update([
            'deleted_at' => now(),
            'status' => 'inactive',
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
