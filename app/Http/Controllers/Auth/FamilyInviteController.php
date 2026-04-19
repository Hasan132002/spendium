<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\FamilyMemberInvitation;
use App\Models\User;
use App\Notifications\InvitationAccepted;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FamilyInviteController extends Controller
{
    public function show(string $token): Renderable|RedirectResponse
    {
        $invitation = FamilyMemberInvitation::with('family')->where('token', $token)->first();

        if (!$invitation) {
            abort(404, 'Invitation not found.');
        }

        if ($invitation->isAccepted()) {
            return redirect()->route('admin.login')
                ->with('info', 'This invitation has already been accepted. Please log in.');
        }

        if ($invitation->isExpired()) {
            abort(410, 'This invitation has expired. Ask the family head to resend.');
        }

        $existingUser = User::where('email', $invitation->email)->first();

        return view('auth.family-invite', compact('invitation', 'existingUser'));
    }

    public function accept(Request $request, string $token): RedirectResponse
    {
        $invitation = FamilyMemberInvitation::with('family')->where('token', $token)->first();

        if (!$invitation) {
            abort(404, 'Invitation not found.');
        }

        if ($invitation->isAccepted()) {
            return redirect()->route('admin.login')
                ->with('info', 'This invitation has already been accepted.');
        }

        if ($invitation->isExpired()) {
            abort(410, 'This invitation has expired.');
        }

        $existingUser = User::where('email', $invitation->email)->first();

        if ($existingUser) {
            $request->validate([
                'password' => ['required', 'string'],
            ]);

            if (!Hash::check($request->password, $existingUser->password)) {
                return back()->withErrors(['password' => 'Incorrect password.']);
            }

            $user = $existingUser;
        } else {
            $data = $request->validate([
                'name'     => ['required', 'string', 'max:255'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            $user = User::create([
                'name'     => $data['name'],
                'email'    => $invitation->email,
                'username' => Str::slug($data['name']) . '-' . Str::lower(Str::random(4)),
                'password' => Hash::make($data['password']),
                'role'     => $invitation->role,
            ]);
        }

        if (FamilyMember::where('user_id', $user->id)->exists()) {
            return redirect()->route('admin.login')
                ->with('error', 'This account already belongs to a family.');
        }

        DB::transaction(function () use ($user, $invitation) {
            FamilyMember::create([
                'family_id' => $invitation->family_id,
                'user_id'   => $user->id,
                'role'      => $invitation->role,
                'status'    => 'accepted',
            ]);

            if (!$user->hasRole('Family Member')) {
                $user->assignRole('Family Member');
            }

            if (!empty($invitation->permissions)) {
                $user->syncPermissions($invitation->permissions);
            }

            $invitation->accepted_at = now();
            $invitation->save();
        });

        $family = Family::find($invitation->family_id);
        if ($family && $family->father_id) {
            User::find($family->father_id)?->notify(new InvitationAccepted($user, $invitation->role));
        }

        Auth::login($user);

        return redirect('/admin')->with('success', 'Welcome to ' . $invitation->family->name . '!');
    }
}
