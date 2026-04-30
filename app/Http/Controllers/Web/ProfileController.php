<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        return view('profile.index', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'business_name' => 'nullable|string|max:255',
            'business_logo' => 'nullable|image|max:51200',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'image' => 'nullable|image|max:51200',
        ]);

        $data = $request->only(['name', 'business_name', 'phone', 'address']);

        if ($request->hasFile('image')) {
            // Delete old image
            if ($user->image && Storage::disk('public')->exists($user->image)) {
                Storage::disk('public')->delete($user->image);
            }

            $path = $request->file('image')->store('profiles', 'public');
            $data['image'] = $path;
        }

        if ($request->hasFile('business_logo')) {
            // Delete old logo
            if ($user->business_logo && Storage::disk('public')->exists($user->business_logo)) {
                Storage::disk('public')->delete($user->business_logo);
            }

            $path = $request->file('business_logo')->store('logos', 'public');
            $data['business_logo'] = $path;
        }

        $user->update($data);

        return back()->with('success', 'Profile updated successfully.');
    }
}
