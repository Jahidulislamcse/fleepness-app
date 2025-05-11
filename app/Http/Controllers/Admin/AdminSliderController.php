<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Slider;
use Illuminate\Http\Request;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class AdminSliderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::whereIn('id', Category::pluck('parent_id')->unique())->get();
        $data['sliders'] = Slider::latest()->get();
        $data['categories'] = $categories;
        $data['tags'] = Category::whereNotIn('id', Category::whereNotNull('parent_id')->pluck('parent_id'))->get();
        // dd($data);
        return view('admin.sliders.index', $data);
    }

    public function getTags(Request $request)
    {
        $tags = Category::where('parent_id', $request->category_id)->get();
        return response()->json($tags);
    }

    public function getAllSliders()
    {
        // Fetch all sliders
        $sliders = Slider::all();

        // Return the sliders as a JSON response
        return response()->json([
            'sliders' => $sliders
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = new Slider();

        // Validate the image upload
        if ($request->file('photo')) {
            $request->validate(
                [
                    'photo' => 'required|image|mimes:jpeg,jpg,png,gif,svg,webp,bmp',
                ]
            );

            // Generate a unique name for the image file
            $name_gen = hexdec(uniqid()) . '.' . $request->file('photo')->getClientOriginalExtension();

            // Define the folder path where the image will be stored
            $folder = 'slider/';

            // Check if the folder exists, if not create it
            if (!file_exists(public_path('upload/' . $folder))) {
                mkdir(public_path('upload/' . $folder), 0777, true);
            }

            // Move the uploaded file to the folder
            $request->file('photo')->move(public_path('upload/' . $folder), $name_gen);

            // Set the file path to the slider's photo field
            $save_url = 'upload/' . $folder . $name_gen;
            $data->photo = $save_url;
        }

        // Save other fields from the request
        $data->photo_alt = $request->photo_alt;
        $data->title = $request->title;
        $data->category_id = $request->category_id;
        $data->tag_id = $request->tag_id;
        $data->description = $request->description;
        $data->btn_name = $request->btn_name;
        $data->btn_url = $request->btn_url;

        // Save the data to the database
        $data->save();

        // Notification message
        $notification = array(
            'message' => 'Data Saved Successfully',
            'alert-type' => 'success'
        );

        // Redirect back with the success message
        return redirect()->back()->with($notification);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $data = Slider::find($id);

        // Validate the uploaded photo
        if ($request->file('photo')) {
            $request->validate(
                [
                    'photo' => 'required|image|mimes:jpeg,JPG,jpg,png,gif,svg,webp,bmp',
                ]
            );

            // Delete the old photo if it exists
            if (file_exists(public_path($data->photo))) {
                unlink(public_path($data->photo));
            }

            // Generate a unique file name for the new photo
            $name_gen = hexdec(uniqid()) . '.' . $request->file('photo')->getClientOriginalExtension();

            // Define the folder path where the image will be stored
            $folder = 'slider/';

            // Check if the folder exists, if not, create it
            if (!file_exists(public_path('upload/' . $folder))) {
                mkdir(public_path('upload/' . $folder), 0777, true);
            }

            // Move the uploaded photo to the folder
            $request->file('photo')->move(public_path('upload/' . $folder), $name_gen);

            // Set the file path to the slider's photo field
            $save_url = 'upload/' . $folder . $name_gen;

            // Assign the new photo URL to the model's `photo` attribute
            $data->photo = $save_url;
        }

        // Update other slider fields
        $data->photo_alt = $request->photo_alt;
        $data->title = $request->title;
        $data->category_id = $request->edit_category_id;
        $data->tag_id = $request->edit_tag_id;
        $data->description = $request->description;
        $data->btn_name = $request->btn_name;
        $data->btn_url = $request->btn_url;

        // Save the updated data
        $data->update();

        // Return success notification
        $notification = array(
            'message' => 'Updated Successfully',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = Slider::find($id);
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
