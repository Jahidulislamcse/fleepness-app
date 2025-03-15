<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\ProductImage;
use App\Models\Stock;
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
        return view('vendor.products.index', $data);
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
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'long_description' => 'required|string',
            'short_description' => 'required|string',
        ]);

        $validated['code'] = $this->generateUniqueCode();
        $validated['status'] = 'active';
        $validated['user_id'] = auth()->id();
        $validated['tags'] = json_encode($request->tags);

        // Save the product using $validated data
        $product = Product::create($validated);

        if ($request->hasFile('images')) {

            $photos = $request->file('images');

            foreach ($photos as $photo) {
                $name_gen = hexdec(uniqid()) . '.' . $photo->getClientOriginalExtension();

                // Define the path where the photo will be stored
                $path = public_path('upload/product');

                // Check if the directory exists, create it if not
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }

                // Move the image to the desired folder
                $photo->move($path, $name_gen);

                // Set the path URL for storage
                $photo_url = 'upload/product/' . $name_gen;

                // Create the ProductImage record
                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => $photo_url,
                    'alt_text' => $request->input('alt_text', ''),
                ]);
            }
        }


        if ($request->variants) {
            foreach ($request->variants as $variant) {
                $photoPath = null;
                if (isset($variant['photo'])) {
                    $photo = $variant['photo'];

                    // Generate a unique name for the photo
                    $name_gen = hexdec(uniqid()) . '.' . $photo->getClientOriginalExtension();

                    // Define the path where the photo will be stored
                    $path = public_path('upload/product');

                    // Check if the directory exists, create it if not
                    if (!file_exists($path)) {
                        mkdir($path, 0777, true);
                    }

                    // Move the image to the desired folder
                    $photo->move($path, $name_gen);

                    // Set the photo URL path
                    $photoPath = 'upload/product/' . $name_gen;
                }


                if (isset($variant['sizes'])) {
                    foreach ($variant['sizes'] as $sizeData) {
                        $variantSize = new Stock();
                        $variantSize->product_id = $product->id;
                        $variantSize->size = $sizeData['size'];
                        $variantSize->quantity = $sizeData['quantity'];
                        $variantSize->buying_price = $sizeData['buying_price'];
                        $variantSize->selling_price = $sizeData['selling_price'];
                        $variantSize->photo = $photoPath;
                        $variantSize->discount_price = $sizeData['discount_price'] ?? null;
                        $variantSize->save();
                    }
                }
            }
        }
        $productPrice = Product::where('id', $product->id)->first();
        $stockPrice = Stock::where('product_id', $product->id)->first();
        $productPrice->selling_price = $stockPrice->selling_price;
        $productPrice->discount_price = $stockPrice->discount_price;
        $productPrice->quantity = $stockPrice->quantity;
        $productPrice->save();
        return redirect()->route('vendor.products.index')->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        // dd($product);
        $productImageCount = ProductImage::where('product_id', $product->id)->count();
        $categories = Category::whereNull('parent_id')->get();
        return view('vendor.products.product_edit', compact('product', 'categories', 'productImageCount'));
    }

    public function update(Request $request, Product $product)
    {
        $productImageCount = ProductImage::where('product_id', $product->id)->count();
        if ($productImageCount == 0 && $request->hasFile('images') == null) {
            return back()->with('success', 'Please add updated images.');
        }
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'short_description' => 'required|string',
            'long_description' => 'required|string',

        ]);
        //  dd($request);
        // If code is not provided, keep the existing code or generate a new one
        $validated['code'] = $validated['code'] ?? $product->code ?? $this->generateUniqueCode();
        $validated['status'] = $request->status;
        if ($request->has('tags')) {
            $validated['tags'] = json_encode($request->tags);
        } else {
            $validated['tags'] = json_encode([]); // If no tags are selected, store an empty array
        }
        $product->update($validated);

        if ($request->hasFile('images')) {
            $photos = $request->file('images');
            foreach ($photos as $photo) {
                // Generate a unique name for the photo
                $name_gen = hexdec(uniqid()) . '.' . $photo->getClientOriginalExtension();

                // Define the path where the photo will be stored
                $path = public_path('upload/product');

                // Check if the directory exists, create it if not
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }

                // Move the image to the desired folder
                $photo->move($path, $name_gen);

                // Set the photo URL path
                $photo_url = 'upload/product/' . $name_gen;

                // Create a new record for the photo in the ProductImage model
                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => $photo_url,
                    'alt_text' => $request->input('alt_text', ''),
                ]);
            } // end foreach

        }
        if ($request->variantsUpdate) {
            foreach ($request->variantsUpdate as $variant) {
                // Initialize photoPath with null
                $photoPath = null;

                // Check if a new photo is uploaded
                if (isset($variant['photo'])) {
                    $photo = $variant['photo'];

                    // Generate a unique name for the photo
                    $name_gen = hexdec(uniqid()) . '.' . $photo->getClientOriginalExtension();

                    // Define the path where the photo will be stored
                    $path = public_path('upload/product');

                    // Check if the directory exists, create it if not
                    if (!file_exists($path)) {
                        mkdir($path, 0777, true);
                    }

                    // Move the image to the desired folder
                    $photo->move($path, $name_gen);

                    // Set the photo URL path
                    $photoPath = 'upload/product/' . $name_gen;
                }


                // Loop through sizes to update them
                if (isset($variant['sizes'])) {
                    foreach ($variant['sizes'] as $sizeData) {
                        $variantSizeUpdate = Stock::where('id', $sizeData['id'])->first();

                        // If no new photo is uploaded, keep the old one
                        if (is_null($photoPath)) {
                            $photoPath = $variantSizeUpdate->photo; // Use old photo path
                        }

                        // Update stock details
                        $variantSizeUpdate->product_id = $product->id;
                        $variantSizeUpdate->size = $sizeData['size'];
                        $variantSizeUpdate->quantity = $sizeData['quantity'];
                        $variantSizeUpdate->buying_price = $sizeData['buying_price'];
                        $variantSizeUpdate->selling_price = $sizeData['selling_price'];
                        $variantSizeUpdate->photo = $photoPath; // Set photo path (new or old)
                        $variantSizeUpdate->discount_price = $sizeData['discount_price'] ?? null;
                        $variantSizeUpdate->save();
                    }
                }
            }
        }


        if ($request->variants) {
            foreach ($request->variants as $variant) {
                $photoPath = null;
                if (isset($variant['photo'])) {
                    $photo = $variant['photo'];

                    // Generate a unique name for the photo
                    $name_gen = hexdec(uniqid()) . '.' . $photo->getClientOriginalExtension();

                    // Define the path where the photo will be stored
                    $path = public_path('upload/product');

                    // Check if the directory exists, create it if not
                    if (!file_exists($path)) {
                        mkdir($path, 0777, true);
                    }

                    // Move the image to the desired folder
                    $photo->move($path, $name_gen);

                    // Set the photo URL path
                    $photoPath = 'upload/product/' . $name_gen;
                }


                if (isset($variant['sizes'])) {
                    foreach ($variant['sizes'] as $sizeData) {
                        $variantSize = new Stock();
                        $variantSize->product_id = $product->id;
                        $variantSize->size = $sizeData['size'];
                        $variantSize->quantity = $sizeData['quantity'];
                        $variantSize->buying_price = $sizeData['buying_price'];
                        $variantSize->selling_price = $sizeData['selling_price'];
                        $variantSize->photo = $photoPath;
                        $variantSize->discount_price = $sizeData['discount_price'] ?? null;
                        $variantSize->save();
                    }
                }
            }
        }

        $productPrice = Product::where('id', $product->id)->first();
        $stockPrice = Stock::where('product_id', $product->id)->first();
        $productPrice->selling_price = $stockPrice->selling_price;
        $productPrice->discount_price = $stockPrice->discount_price;
        $productPrice->quantity = $stockPrice->quantity;
        $productPrice->save();
        return redirect()->route('vendor.products.index')->with('success', 'Product updated successfully.');
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
