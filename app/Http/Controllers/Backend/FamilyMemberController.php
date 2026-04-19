<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Mail\FamilyInvitation as FamilyInvitationMail;
use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\FamilyMemberInvitation;
use App\Services\PermissionService;
use App\Traits\HasFamilyScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class FamilyMemberController extends Controller
{
    use HasFamilyScope;

    public function __construct(private readonly PermissionService $permissionService)
    {
    }

    public function index(Request $request): Renderable|RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['family.member.invite']);

        // Superadmin sees ALL families consolidated
        if ($this->isSuperadmin()) {
            $families = Family::with([
                'members.user',
                'father:id,name,email',
            ])->withCount('members')->get();

            $invitations = FamilyMemberInvitation::with('family:id,name', 'inviter:id,name')
                ->orderByDesc('created_at')
                ->limit(50)
                ->get();

            return view('backend.pages.family.superadmin-overview', compact('families', 'invitations'));
        }

        $family = $this->currentFamily();
        if (!$family) {
            return redirect()->route('admin.dashboard')->with('error', 'You are not a family head.');
        }

        $members = FamilyMember::with('user')
            ->where('family_id', $family->id)
            ->get();

        $invitations = FamilyMemberInvitation::where('family_id', $family->id)
            ->orderByDesc('created_at')
            ->get();

        return view('backend.pages.family.members', compact('family', 'members', 'invitations'));
    }

    public function showInviteForm(): Renderable|RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['family.member.invite']);

        $family = $this->currentFamily();
        if (!$family) {
            return redirect()->route('admin.dashboard')->with('error', 'You are not a family head.');
        }

        $familyPermissions = $this->permissionService->getPermissionsByGroup('family') ?? [];

        return view('backend.pages.family.invite-form', compact('family', 'familyPermissions'));
    }

    public function storeInvite(Request $request): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['family.member.invite']);

        $family = $this->currentFamily();
        if (!$family) {
            return redirect()->route('admin.dashboard')->with('error', 'You are not a family head.');
        }

        // Rate limit: max 10 invitations per hour per user
        $rateKey = 'invite-rate:' . auth()->id();
        $hits = cache()->get($rateKey, 0);
        if ($hits >= 10) {
            return back()->with('error', 'Invitation rate limit reached (10/hour). Try again later.');
        }
        cache()->put($rateKey, $hits + 1, now()->addHour());

        $data = $request->validate([
            'name'          => ['nullable', 'string', 'max:255'],
            'email'         => ['required', 'email', 'max:255'],
            'role'          => ['required', 'in:mother,child'],
            'permissions'   => ['array'],
            'permissions.*' => ['string'],
        ]);

        if (FamilyMemberInvitation::where('family_id', $family->id)
            ->where('email', $data['email'])
            ->whereNull('accepted_at')
            ->exists()
        ) {
            return back()->with('error', 'A pending invitation already exists for this email. Use Resend instead.');
        }

        $allowedPermissions = $this->permissionService->getPermissionsByGroup('family') ?? [];
        $selected = array_values(array_intersect($data['permissions'] ?? [], $allowedPermissions));

        $invitation = FamilyMemberInvitation::create([
            'family_id'   => $family->id,
            'name'        => $data['name'] ?? null,
            'email'       => $data['email'],
            'role'        => $data['role'],
            'token'       => FamilyMemberInvitation::generateToken(),
            'permissions' => $selected,
            'invited_by'  => Auth::id(),
            'expires_at'  => now()->addDays(FamilyMemberInvitation::DEFAULT_EXPIRY_DAYS),
        ]);

        Mail::to($invitation->email)->send(new FamilyInvitationMail($invitation));

        return redirect()->route('admin.family.members.index')
            ->with('success', 'Invitation sent to ' . $invitation->email);
    }

    public function remove(int $memberId): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['family.member.remove']);

        $family = $this->currentFamily();
        if (!$family) {
            return redirect()->route('admin.dashboard')->with('error', 'You are not a family head.');
        }

        $member = FamilyMember::where('family_id', $family->id)->findOrFail($memberId);

        if ($member->user_id === $family->father_id) {
            return back()->with('error', 'You cannot remove the family head.');
        }

        DB::transaction(function () use ($member) {
            $user = $member->user;
            if ($user) {
                $user->removeRole('Family Member');
                $user->syncPermissions([]);
            }
            $member->delete();
        });

        return back()->with('success', 'Member removed from family.');
    }

    public function showChangeRoleForm(int $memberId): Renderable|RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['family.member.edit']);

        $family = $this->currentFamily();
        if (!$family) {
            return redirect()->route('admin.dashboard')->with('error', 'You are not a family head.');
        }

        $member = FamilyMember::with('user')->where('family_id', $family->id)->findOrFail($memberId);

        if ($member->user_id === $family->father_id) {
            return back()->with('error', 'You cannot edit the family head from this screen.');
        }

        $familyPermissions = $this->permissionService->getPermissionsByGroup('family') ?? [];
        $currentPermissions = $member->user?->getDirectPermissions()->pluck('name')->all() ?? [];

        return view('backend.pages.family.change-role', compact('family', 'member', 'familyPermissions', 'currentPermissions'));
    }

    public function changeRole(Request $request, int $memberId): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['family.member.edit']);

        $family = $this->currentFamily();
        if (!$family) {
            return redirect()->route('admin.dashboard')->with('error', 'You are not a family head.');
        }

        $member = FamilyMember::where('family_id', $family->id)->findOrFail($memberId);

        if ($member->user_id === $family->father_id) {
            return back()->with('error', 'You cannot edit the family head.');
        }

        $data = $request->validate([
            'role'          => ['required', 'in:mother,child'],
            'permissions'   => ['array'],
            'permissions.*' => ['string'],
        ]);

        $allowedPermissions = $this->permissionService->getPermissionsByGroup('family') ?? [];
        $selected = array_values(array_intersect($data['permissions'] ?? [], $allowedPermissions));

        DB::transaction(function () use ($member, $data, $selected) {
            $member->role = $data['role'];
            $member->save();

            $user = $member->user;
            if ($user) {
                if (!$user->hasRole('Family Member')) {
                    $user->assignRole('Family Member');
                }
                $user->syncPermissions($selected);
            }
        });

        return redirect()->route('admin.family.members.index')
            ->with('success', 'Member role and permissions updated.');
    }

    public function resendInvite(int $invitationId): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['family.member.invite']);

        $family = $this->currentFamily();
        if (!$family) {
            return redirect()->route('admin.dashboard')->with('error', 'You are not a family head.');
        }

        $invitation = FamilyMemberInvitation::where('family_id', $family->id)->findOrFail($invitationId);

        if ($invitation->isAccepted()) {
            return back()->with('error', 'Invitation already accepted.');
        }

        $invitation->update([
            'token'      => FamilyMemberInvitation::generateToken(),
            'expires_at' => now()->addDays(FamilyMemberInvitation::DEFAULT_EXPIRY_DAYS),
        ]);

        Mail::to($invitation->email)->send(new FamilyInvitationMail($invitation));

        return back()->with('success', 'Invitation resent to ' . $invitation->email);
    }

    public function revokeInvite(int $invitationId): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['family.member.invite']);

        $family = $this->currentFamily();
        if (!$family) {
            return redirect()->route('admin.dashboard')->with('error', 'You are not a family head.');
        }

        $invitation = FamilyMemberInvitation::where('family_id', $family->id)->findOrFail($invitationId);

        if ($invitation->isAccepted()) {
            return back()->with('error', 'Cannot revoke an accepted invitation.');
        }

        $invitation->delete();

        return back()->with('success', 'Invitation revoked.');
    }

    private function currentFamily(): ?Family
    {
        return Family::where('father_id', Auth::id())->first();
    }
}
