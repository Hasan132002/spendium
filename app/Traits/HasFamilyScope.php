<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Family;
use App\Models\FamilyMember;
use Illuminate\Support\Facades\Auth;

trait HasFamilyScope
{
    protected function isSuperadmin(): bool
    {
        $user = Auth::user();
        return $user !== null && $user->hasRole('Superadmin');
    }

    /**
     * Get family ids the current user can access.
     *
     * Superadmin → all families.
     * Family head → their own family.
     * Regular member → the family they belong to.
     */
    protected function familyIdsInScope(): array
    {
        if ($this->isSuperadmin()) {
            return Family::pluck('id')->all();
        }

        $userId = Auth::id();

        $headFamily = Family::where('father_id', $userId)->first();
        if ($headFamily) {
            return [$headFamily->id];
        }

        $memberFamily = FamilyMember::where('user_id', $userId)->first();
        return $memberFamily ? [$memberFamily->family_id] : [];
    }

    /**
     * All user ids visible across the families in scope.
     */
    protected function memberIdsInScope(): array
    {
        $familyIds = $this->familyIdsInScope();
        if (empty($familyIds)) {
            return [Auth::id()];
        }
        return FamilyMember::whereIn('family_id', $familyIds)->pluck('user_id')->all();
    }

    /**
     * Get all families in scope (eager-loadable).
     */
    protected function familiesInScope()
    {
        if ($this->isSuperadmin()) {
            return Family::with('members.user')->get();
        }
        return Family::with('members.user')->whereIn('id', $this->familyIdsInScope())->get();
    }
}
