<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\User;

class FamilyController extends Controller
{
    public function create(Request $request)
    {
        $request->validate(['name' => 'required|string']);

        $family = Family::create([
            'name' => $request->name,
            'father_id' => auth()->id()
        ]);

        FamilyMember::create([
            'family_id' => $family->id,
            'user_id' => auth()->id(),
            'role' => 'father',
            'status' => 'accepted'
        ]);

        return $this->success('Family created', $family);
    }

    public function inviteMember(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'role' => 'required|in:mother,child'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || $user->role !== $request->role) {
            return $this->error('User with this email and role does not exist.', null, 404);
        }

        $family = Family::where('father_id', auth()->id())->first();
        if (!$family) return $this->error('No family found', null, 404);

        $alreadyMember = FamilyMember::where('family_id', $family->id)
            ->where('user_id', $user->id)->first();

        if ($alreadyMember) return $this->success('User already invited or added');

        FamilyMember::create([
            'family_id' => $family->id,
            'user_id' => $user->id,
            'role' => $request->role,
            'status' => 'pending'
        ]);

        return $this->success('Member invited successfully. Awaiting acceptance.');
    }
public function showMyInvitations()
{
    $pendingInvitations = FamilyMember::with('family')
        ->where('user_id', auth()->id())
        ->where('status', 'pending')
        ->get();

    if ($pendingInvitations->isEmpty()) {
        return $this->success('No pending invitations found.', []);
    }

    return $this->success('Pending invitations found.', $pendingInvitations);
}

    public function acceptInvitation(Request $request)
    {
        $member = FamilyMember::where('user_id', auth()->id())
            ->where('status', 'pending')
            ->first();

        if (!$member) {
            return $this->error('No pending invitation found.', null, 404);
        }

        $member->status = 'accepted';
        $member->save();

        return $this->success('Invitation accepted. Welcome to the family.');
    }

    public function listMembers()
    {
        $family = Family::where('father_id', auth()->id())->with([
            'members' => function ($query) {
                $query->where('status', 'accepted');
            },
            'members.user'
        ])->first();

        if (!$family) return $this->error('No family found', null, 404);

        return $this->success('Family members retrieved', $family->members);
    }
}
