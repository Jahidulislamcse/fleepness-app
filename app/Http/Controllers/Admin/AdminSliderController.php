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
        if ($request->file('photo')) {
            $request->validate(
                [
                    'photo' => 'required|image|mimes:jpeg,JPG,jpg,png,gif,svg,webp,bmp',
                ]
            );
            $manager = new ImageManager();
            $name_gen = hexdec(uniqid()) . '.' . $request->file('photo')->getClientOriginalExtension();
            $img = $manager->make($request->file('photo'));
            $img->fit(1920, 1280);

            $folder = 'slider/';
            if (!file_exists(public_path('upload/' . $folder))) {
                mkdir(public_path('upload/' . $folder), 0777, true);
            }
            $img->encode('jpg', 80)->save(public_path('upload/' . $folder . $name_gen));
            $save_url = 'upload/' . $folder . $name_gen;

            $data->photo = $save_url;
        }

        $data->photo_alt = $request->photo_alt;
        $data->title = $request->title;
        $data->category_id = $request->category_id;
        $data->tag_id = $request->tag_id;
        $data->description = $request->description;
        $data->btn_name = $request->btn_name;
        $data->btn_url = $request->btn_url;

        $data->save();


        $notification = array(
            'message' => 'Data Saved Successfully',
            'alert-type' => 'success'
        );
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

        $data =  Slider::find($id);

        if ($request->file('photo')) {
            $request->validate(
                [
                    'photo' => 'required|image|mimes:jpeg,JPG,jpg,png,gif,svg,webp,bmp',
                ]
            );
            if (file_exists($data->photo)) {
                unlink(public_path($data->photo));
            }
            $manager = new ImageManager();
            $name_gen = hexdec(uniqid()) . '.' . $request->file('photo')->getClientOriginalExtension();
            $img = $manager->make($request->file('photo'));
            $img->fit(1200, 630);


            $folder = 'slider/';
            if (!file_exists(public_path('upload/' . $folder))) {
                mkdir(public_path('upload/' . $folder), 0777, true);
            }

            $img->encode('jpg', 80)->save(public_path('upload/' . $folder . $name_gen));
            $save_url = 'upload/' . $folder . $name_gen;

            $data->photo = $save_url;
        }

        $data->photo_alt = $request->photo_alt;
        $data->title = $request->title;
        $data->category_id = $request->edit_category_id;
        $data->tag_id = $request->edit_tag_id;
        $data->description = $request->description;
        $data->btn_name = $request->btn_name;
        $data->btn_url = $request->btn_url;



        $data->update();

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
