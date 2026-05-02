<?php

namespace App\Http\Controllers;

use App\Models\PayrollSetting;
use Illuminate\Http\Request;

class PayrollSettingController extends Controller
{
    public function index()
    {
        $settings = PayrollSetting::orderBy('category')
            ->orderBy('key')
            ->get()
            ->groupBy('category');

        return view('salary.setting', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings'          => 'required|array',
            'settings.*.key'    => 'required|string',
            'settings.*.value'  => 'required|numeric|min:0',
        ]);

        foreach ($validated['settings'] as $data) {
            PayrollSetting::set($data['key'], $data['value']);
        }

        return back()->with('success', 'Paramètres de paie mis à jour !');
    }
}