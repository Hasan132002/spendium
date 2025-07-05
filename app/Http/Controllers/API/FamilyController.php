<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

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

        return response()->json(['message' => 'Family created', 'family' => $family]);
    }

    public function inviteMember(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'role' => 'required|in:mother,child'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || $user->role !== $request->role) {
            return response()->json([
                'message' => 'User with this email and role does not exist.'
            ], 404);
        }

        $family = Family::where('father_id', auth()->id())->first();
        if (!$family) return response()->json(['error' => 'No family found'], 404);

        $alreadyMember = FamilyMember::where('family_id', $family->id)
            ->where('user_id', $user->id)->first();

        if ($alreadyMember) return response()->json(['message' => 'User already invited or added']);

        FamilyMember::create([
            'family_id' => $family->id,
            'user_id' => $user->id,
            'role' => $request->role,
            'status' => 'pending'
        ]);

        return response()->json(['message' => 'Member invited successfully. Awaiting acceptance.']);
    }

    public function acceptInvitation(Request $request)
    {
        $member = FamilyMember::where('user_id', auth()->id())
            ->where('status', 'pending')
            ->first();

        if (!$member) {
            return response()->json(['message' => 'No pending invitation found.'], 404);
        }

        $member->status = 'accepted';
        $member->save();

        return response()->json(['message' => 'Invitation accepted. Welcome to the family.']);
    }

    public function listMembers()
    {
        $family = Family::where('father_id', auth()->id())->with(['members' => function ($query) {
            $query->where('status', 'accepted');
        }, 'members.user'])->first();

        if (!$family) return response()->json(['message' => 'No family found'], 404);

        return response()->json($family->members);
    }
}
