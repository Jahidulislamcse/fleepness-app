<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class AdminSettingController extends Controller
{
    public function Index()
    {
      $setting = Setting::first() ?? new Setting();
        return view('admin.settings', compact('setting'));
    }

   public function Update(Request $request)
{
    // 1) Validate input
    $request->validate([
        'title'                 => 'required|string|max:255',
        'num_of_tag'            => 'required|integer',
        'address'               => 'required|string|max:500',
        'phone'                 => 'required|string|max:15',
        'email'                 => 'required|email|max:255',
        'meta_keyword'          => 'nullable|string|max:500',
        'meta_description'      => 'nullable|string|max:1000',
        'footer_text'           => 'nullable|string',
        'footer_copyright_by'   => 'required|string|max:255',
        'footer_copyright_url'  => 'required|url|max:500',
        'logo'                  => 'nullable|image|mimes:jpeg,jpg,png,gif,svg,webp,bmp|max:2048',
        'footer_logo'           => 'nullable|image|mimes:jpeg,jpg,png,gif,svg,webp,bmp|max:2048',
        'favicon'               => 'nullable|image|mimes:jpeg,jpg,png,gif,svg,webp,bmp|max:2048',
        'footer_bg_image'       => 'nullable|image|mimes:jpeg,jpg,png,gif,svg,webp,bmp|max:2048',
    ]);

    // 2) Use existing Setting or new one if none exists
    $setting = Setting::first() ?? new Setting();

    // 3) Handle each file upload (only if a new file is present)
    if ($request->hasFile('logo')) {
        if ($setting->logo && file_exists(public_path($setting->logo))) {
            unlink(public_path($setting->logo));
        }
        $photo    = $request->file('logo');
        $name_gen = hexdec(uniqid()) . '.' . $photo->getClientOriginalExtension();
        $folder   = 'upload/setting/';
        if (!file_exists(public_path($folder))) {
            mkdir(public_path($folder), 0777, true);
        }
        $photo->move(public_path($folder), $name_gen);
        $setting->logo = $folder . $name_gen;
    }

    if ($request->hasFile('footer_logo')) {
        if ($setting->footer_logo && file_exists(public_path($setting->footer_logo))) {
            unlink(public_path($setting->footer_logo));
        }
        $photo    = $request->file('footer_logo');
        $name_gen = hexdec(uniqid()) . '.' . $photo->getClientOriginalExtension();
        $folder   = 'upload/setting/';
        if (!file_exists(public_path($folder))) {
            mkdir(public_path($folder), 0777, true);
        }
        $photo->move(public_path($folder), $name_gen);
        $setting->footer_logo = $folder . $name_gen;
    }

    if ($request->hasFile('favicon')) {
        if ($setting->favicon && file_exists(public_path($setting->favicon))) {
            unlink(public_path($setting->favicon));
        }
        $photo    = $request->file('favicon');
        $name_gen = hexdec(uniqid()) . '.' . $photo->getClientOriginalExtension();
        $folder   = 'upload/setting/';
        if (!file_exists(public_path($folder))) {
            mkdir(public_path($folder), 0777, true);
        }
        $photo->move(public_path($folder), $name_gen);
        $setting->favicon = $folder . $name_gen;
    }

    if ($request->hasFile('footer_bg_image')) {
        if ($setting->footer_bg_image && file_exists(public_path($setting->footer_bg_image))) {
            unlink(public_path($setting->footer_bg_image));
        }
        $photo    = $request->file('footer_bg_image');
        $name_gen = hexdec(uniqid()) . '.' . $photo->getClientOriginalExtension();
        $folder   = 'upload/setting/';
        if (!file_exists(public_path($folder))) {
            mkdir(public_path($folder), 0777, true);
        }
        $photo->move(public_path($folder), $name_gen);
        $setting->footer_bg_image = $folder . $name_gen;
    }

    // 4) Update all other columns
    $setting->title                = $request->title;
    $setting->num_of_tag           = $request->num_of_tag;
    $setting->address              = $request->address;
    $setting->phone                = $request->phone;
    $setting->email                = $request->email;
    $setting->meta_keyword         = $request->meta_keyword;
    $setting->meta_description     = $request->meta_description;
    $setting->footer_text          = $request->footer_text;
    $setting->footer_copyright_by  = $request->footer_copyright_by;
    $setting->footer_copyright_url = $request->footer_copyright_url;

    // 5) Save (inserts if new or updates if existing)
    $setting->save();

    return redirect()
        ->back()
        ->with([
            'message'    => 'Settings updated successfully.',
            'alert-type' => 'success',
        ]);
}

}
