<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function edit()
    {
        $setting = Setting::first();
        return Inertia::render('Settings/Edit', [
            'setting' => $setting
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'store_name' => 'required|string|max:255',
            'store_address' => 'nullable|string',
            'store_contact' => 'nullable|string|max:255',
            'receipt_template' => 'nullable|string',
            'store_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'tax_rate' => 'nullable|numeric',
        ]);

        $setting = Setting::first() ?? new Setting();

        if ($request->hasFile('store_logo')) {
            $path = $request->file('store_logo')->store('logos', 'public');
            $data['store_logo'] = $path;
        } else {
            unset($data['store_logo']);
        }

        $setting->fill($data)->save();

        return redirect()->route('settings.edit')->with([
            'flash' => ['success' => 'Pengaturan Telah Diubah'],
        ]);
    }
}
