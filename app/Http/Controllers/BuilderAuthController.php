<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Orchid\Platform\Models\Role;
use Orchid\Attachment\File;

class BuilderAuthController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.builder-register');
    }

    public function register(Request $request)
    {
        // 1. Validate inputs
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            // Notice the ".*" -> checks EACH file in the array
            'kyc_documents' => 'required|array|min:1',
            'kyc_documents.*' => 'file|mimes:pdf,jpg,png|max:5120',
        ]);

        // 2. Create User
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'verification_status' => 'pending', 
            'permissions' => [
                'platform.index' => true,
                'platform.builder.projects' => true,
                'platform.builder.properties' => true,
            ],
        ]);

        // 3. Assign Role
        $builderRole = Role::where('slug', 'builder')->first();
        if ($builderRole) {
            $user->addRole($builderRole);
        }

        // 4. Handle MULTIPLE File Uploads
        if ($request->hasFile('kyc_documents')) {
            $attachmentIds = [];

            // Loop through each uploaded file
            foreach ($request->file('kyc_documents') as $uploadedFile) {
                // Upload to Orchid storage
                $file = new File($uploadedFile);
                $attachment = $file->load();
                
                // Collect the ID
                $attachmentIds[] = $attachment->id;
            }

            // Sync all IDs to the user at once
            $user->attachment()->sync($attachmentIds);
        }

        return redirect()->route('builder.thankyou');
    }

    public function showThankYou()
    {
        return view('auth.builder-thank-you');
    }
}