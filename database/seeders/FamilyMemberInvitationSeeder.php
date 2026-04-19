<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Family;
use App\Models\FamilyMemberInvitation;
use Illuminate\Database\Seeder;

class FamilyMemberInvitationSeeder extends Seeder
{
    public function run(): void
    {
        FamilyMemberInvitation::truncate();

        // Khan Family — pending invitation to a new child
        $khan = Family::where('name', 'Khan Family')->first();
        if ($khan) {
            FamilyMemberInvitation::create([
                'family_id'   => $khan->id,
                'name'        => 'Yusuf Khan',
                'email'       => 'yusuf.pending@spendium.com',
                'role'        => 'child',
                'token'       => FamilyMemberInvitation::generateToken(),
                'permissions' => [],
                'invited_by'  => $khan->father_id,
                'expires_at'  => now()->addDays(5),
                'accepted_at' => null,
            ]);
        }

        // Hassan Family — pending invitation to an aunt
        $hassan = Family::where('name', 'Hassan Family')->first();
        if ($hassan) {
            FamilyMemberInvitation::create([
                'family_id'   => $hassan->id,
                'name'        => 'Aunt Rabia',
                'email'       => 'rabia.pending@spendium.com',
                'role'        => 'mother',
                'token'       => FamilyMemberInvitation::generateToken(),
                'permissions' => ['family.expense.view', 'family.fund_request.view'],
                'invited_by'  => $hassan->father_id,
                'expires_at'  => now()->addDays(6),
                'accepted_at' => null,
            ]);
        }

        // Ali Family — expired invitation (test case)
        $ali = Family::where('name', 'Ali Family')->first();
        if ($ali) {
            FamilyMemberInvitation::create([
                'family_id'   => $ali->id,
                'name'        => 'Old Invite',
                'email'       => 'expired.invite@spendium.com',
                'role'        => 'child',
                'token'       => FamilyMemberInvitation::generateToken(),
                'permissions' => [],
                'invited_by'  => $ali->father_id,
                'expires_at'  => now()->subDays(3),
                'accepted_at' => null,
            ]);
        }

        // Farhan Ali Family — already-accepted invitation (history record)
        $farhan = Family::where('name', 'Farhan Ali Family')->first();
        if ($farhan) {
            FamilyMemberInvitation::create([
                'family_id'   => $farhan->id,
                'name'        => 'Nadia Farhan',
                'email'       => 'nadia@spendium.com',
                'role'        => 'mother',
                'token'       => FamilyMemberInvitation::generateToken(),
                'permissions' => ['family.expense.view', 'family.goal.view'],
                'invited_by'  => $farhan->father_id,
                'expires_at'  => now()->subDays(10),
                'accepted_at' => now()->subDays(15),
            ]);
        }

        $this->command->info('Seeded ' . FamilyMemberInvitation::count() . ' invitations (pending + expired + accepted).');
    }
}
