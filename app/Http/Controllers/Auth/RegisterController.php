<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = '/admin';

    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'    => ['required', 'string', 'min:8', 'confirmed'],
            'role'        => ['required', 'in:father,mother'],
            'family_name' => ['required', 'string', 'max:255'],
        ]);
    }

    protected function create(array $data): ?User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'username' => Str::slug($data['name']) . '-' . Str::lower(Str::random(4)),
                'password' => Hash::make($data['password']),
                'role'     => $data['role'],
            ]);

            $family = Family::create([
                'name'      => $data['family_name'],
                'father_id' => $user->id,
            ]);

            FamilyMember::create([
                'family_id' => $family->id,
                'user_id'   => $user->id,
                'role'      => $data['role'],
                'status'    => 'accepted',
            ]);

            $user->assignRole('Family Head');

            return $user;
        });
    }
}
