<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MemberProfileController extends Controller
{
    public function show()
    {
        return view('member.profile');
    }

    public function update(Request $request)
    {
        $request->validate([
            'profile_photo' => 'nullable|image|max:2048',
        ]);

        $user = auth()->user();

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $user->update(['profile_photo_path' => $path]);
        }

        return back()->with('success', 'Profile photo updated successfully (የፕሮፋይል ምስልዎ በተሳካ ሁኔታ ተቀይሯል)!');
    }
}
