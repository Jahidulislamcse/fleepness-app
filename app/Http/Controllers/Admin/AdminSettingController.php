<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class AdminSettingController extends Controller
{
    public function Index()
    {
        $data['setting'] = Setting::first();
        return view('admin.settings', $data);
    }

    public function Update(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'address' => 'required',
            'phone' => 'required|max:15',
            'email' => 'required|email',
            'footer_copyright_by' => 'required',
            'footer_copyright_url' => 'required',
            // 'shipping_charge' => 'required',
        ]);

        $data = Setting::first();

        // Logo
        if ($request->hasFile('logo')) {
            $request->validate([
                'logo' => 'image|mimes:jpeg,JPG,jpg,png,gif,svg,webp,bmp|max:2048',
            ]);
            if (file_exists($data->logo)) {
                unlink(public_path($data->logo));
            }
            $photo = $request->file('logo');
            $name_gen = hexdec(uniqid()) . '.' . $photo->getClientOriginalExtension();
            $folder = 'upload/setting/';
            if (!file_exists(public_path($folder))) {
                mkdir(public_path($folder), 0777, true);
            }
            $photo->move(public_path($folder), $name_gen);
            $data->logo = $folder . $name_gen;
        }

        // Footer Logo
        if ($request->hasFile('footer_logo')) {
            $request->validate([
                'footer_logo' => 'image|mimes:jpeg,JPG,jpg,png,gif,svg,webp,bmp|max:2048',
            ]);
            if (file_exists($data->footer_logo)) {
                unlink(public_path($data->footer_logo));
            }
            $photo = $request->file('footer_logo');
            $name_gen = hexdec(uniqid()) . '.' . $photo->getClientOriginalExtension();
            $folder = 'upload/setting/';
            if (!file_exists(public_path($folder))) {
                mkdir(public_path($folder), 0777, true);
            }
            $photo->move(public_path($folder), $name_gen);
            $data->footer_logo = $folder . $name_gen;
        }

        // Favicon
        if ($request->hasFile('favicon')) {
            $request->validate([
                'favicon' => 'image|mimes:jpeg,JPG,jpg,png,gif,svg,webp,bmp|max:2048',
            ]);
            if (file_exists($data->favicon)) {
                unlink(public_path($data->favicon));
            }
            $photo = $request->file('favicon');
            $name_gen = hexdec(uniqid()) . '.' . $photo->getClientOriginalExtension();
            $folder = 'upload/setting/';
            if (!file_exists(public_path($folder))) {
                mkdir(public_path($folder), 0777, true);
            }
            $photo->move(public_path($folder), $name_gen);
            $data->favicon = $folder . $name_gen;
        }

        // Footer Background Image
        if ($request->hasFile('footer_bg_image')) {
            $request->validate([
                'footer_bg_image' => 'image|mimes:jpeg,JPG,jpg,png,gif,svg,webp,bmp|max:2048',
            ]);
            if (file_exists($data->footer_bg_image)) {
                unlink(public_path($data->footer_bg_image));
            }
            $photo = $request->file('footer_bg_image');
            $name_gen = hexdec(uniqid()) . '.' . $photo->getClientOriginalExtension();
            $folder = 'upload/setting/';
            if (!file_exists(public_path($folder))) {
                mkdir(public_path($folder), 0777, true);
            }
            $photo->move(public_path($folder), $name_gen);
            $data->footer_bg_image = $folder . $name_gen;
        }

        // Update other settings fields
        $data->title = $request->title;
        $data->address = $request->address;
        $data->phone = $request->phone;
        $data->email = $request->email;
        $data->meta_keyword = $request->meta_keyword;
        $data->meta_description = $request->meta_description;
        $data->footer_text = $request->footer_text;
        $data->footer_copyright_by = $request->footer_copyright_by;
        $data->footer_copyright_url = $request->footer_copyright_url;
        // $data->shipping_charge = $request->shipping_charge;
        // $data->TC = $request->TC;
        // $data->about_us = $request->about_us;

        // Update the setting in the database
        $data->update();

        // Return success notification
        $notification = array(
            'message' => 'Updated Successfully',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }
}
