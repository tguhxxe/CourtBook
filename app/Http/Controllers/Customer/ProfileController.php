<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileRequest;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function edit(Request $r)
    {
        return view('customer.profile', ['user' => $r->user()]);
    }

    public function update(ProfileRequest $r)
    {
        $data = $r->safe()->only(['name', 'email', 'phone']);
        if ($r->filled('password')) {
            $data['password'] = $r->password;
        } $r->user()->update($data);

        return back()->with('status', 'Profil berhasil disimpan.');
    }
}
