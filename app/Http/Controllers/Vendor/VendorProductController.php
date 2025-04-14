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
use Carbon\Carbon;
use Illuminate\Http\Request;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

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
        $products = Product::with('category')
            ->latest()
            ->whereNull('deleted_at')
            ->where('user_id', auth()->id())
            ->get();

        return response()->json([
            'success' => true,
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
                'category_id' => 'required|exists:categories,id',
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

            // 👇 Conditionally return redirect or JSON
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product created successfully',
                    'product' => $product,
                ]);
            } else {
                return redirect()->route('vendor.products.index')
                    ->with('success', 'Product created successfully');
            }
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                ], 500);
            } else {
                return redirect()->back()
                    ->with('error', 'Something went wrong: ' . $e->getMessage());
            }
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
        // dd($id);
        // dd($request->all());

        try {
            $validated = $request->validate([
                'category_id' => 'required|exists:categories,id',
                'name' => 'nullable|string|max:255',
                'long_description' => 'nullable|string',
                'short_description' => 'nullable|string',
                'size_template_id' => 'nullable|exists:size_templates,id',
                'quantity' => 'nullable|integer|min:0',
                'selling_price' => 'nullable|numeric|min:0',
                'discount_price' => 'nullable|numeric|min:0',
            ]);

            $product = Product::findOrFail($id);

            $validated['tags'] = json_encode($request->tags);
            // $validated['user_id'] = auth()->id(); // optional
            $product->update($validated);

            // Handle sizes (retain only submitted size IDs)
            if ($request->has('size_ids')) {
                $submittedIds = $request->size_ids; // array of product_size IDs to keep
                $existingIds = ProductSize::where('product_id', $product->id)->pluck('id')->toArray();

                // Delete removed sizes
                $toDelete = array_diff($existingIds, $submittedIds);
                ProductSize::whereIn('id', $toDelete)->delete();
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

            return $request->wantsJson()
                ? response()->json(['success' => true, 'message' => 'Product updated successfully', 'product' => $product])
                : redirect()->route('vendor.products.index')->with('success', 'Product updated successfully');
        } catch (\Exception $e) {
            return $request->wantsJson()
                ? response()->json(['success' => false, 'error' => $e->getMessage()], 500)
                : redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }



    public function destroy(Product $product)
    {
        $order = OrderItem::where('product_id', $product->id)->count('id');
        if ($order > 0) {
            return redirect()->route('vendor.products.index')->with('success', 'You can not delete this Product . Because Under this product First delete Order.');
        }

        $product->deleted_at = Carbon::now();
        $product->status = 'inactive';
        $product->save();


        return redirect()->route('vendor.products.index')->with('success', 'Product deleted successfully.');
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
