@extends('backend.layouts.app')

@section('title')
    {{ __('Family Members') }} - {{ config('app.name') }}
@endsection

@section('admin-content')
<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">
            {{ $family->name }} — {{ __('Members') }}
        </h2>
        <a href="{{ route('admin.family.members.invite') }}" class="btn-primary">
            <i class="bi bi-plus-lg mr-2"></i> {{ __('Invite New Member') }}
        </a>
    </div>

    @include('backend.layouts.partials.messages')

    {{-- Transfer Head panel --}}
    @php
        $nonHeadMembers = $members->where('user_id', '!=', $family->father_id)->filter(fn ($m) => $m->user !== null);
    @endphp
    @if ($nonHeadMembers->count() > 0)
        <div x-data="{ showTransfer: false }" class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 dark:border-amber-900 dark:bg-amber-900/20 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-amber-800 dark:text-amber-200">{{ __('Transfer Family Head') }}</p>
                    <p class="text-xs text-amber-700 dark:text-amber-300">{{ __('Hand over family leadership to another member. You will become a regular member.') }}</p>
                </div>
                <button type="button" @click="showTransfer = !showTransfer" class="btn-default text-sm">
                    <i class="bi bi-arrow-left-right mr-1"></i> <span x-text="showTransfer ? '{{ __('Cancel') }}' : '{{ __('Transfer') }}'"></span>
                </button>
            </div>
            <form x-show="showTransfer" x-transition action="{{ route('admin.family.transfer-head') }}" method="POST"
                  class="mt-4 flex flex-wrap gap-3 items-end" style="display: none"
                  onsubmit="return confirm('This will transfer head privileges permanently. Continue?');">
                @csrf
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-medium mb-1 text-amber-900 dark:text-amber-200">{{ __('New head') }}</label>
                    <select name="new_head_user_id" required class="h-10 w-full rounded-lg border border-amber-300 bg-white px-3 text-sm">
                        <option value="">{{ __('-- Select a member --') }}</option>
                        @foreach ($nonHeadMembers as $m)
                            <option value="{{ $m->user_id }}">{{ $m->user->name }} ({{ ucfirst($m->role) }})</option>
                        @endforeach
                    </select>
                </div>
                <input type="hidden" name="confirm" value="transfer">
                <button type="submit" class="btn-primary">{{ __('Confirm Transfer') }}</button>
            </form>
        </div>
    @endif

    {{-- Active Members --}}
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-8">
        <div class="px-5 py-4 sm:px-6 sm:py-5">
            <h3 class="text-base font-medium text-gray-800 dark:text-white/90">{{ __('Active Members') }}</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800 text-left">
                        <th class="px-5 py-3">{{ __('Name') }}</th>
                        <th class="px-5 py-3">{{ __('Email') }}</th>
                        <th class="px-5 py-3">{{ __('Role') }}</th>
                        <th class="px-5 py-3">{{ __('Status') }}</th>
                        <th class="px-5 py-3 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($members as $member)
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-5 py-4">{{ $member->user?->name ?? '—' }}</td>
                            <td class="px-5 py-4">{{ $member->user?->email ?? '—' }}</td>
                            <td class="px-5 py-4 capitalize">{{ $member->role }}</td>
                            <td class="px-5 py-4 capitalize">{{ $member->status ?? 'accepted' }}</td>
                            <td class="px-5 py-4 text-right">
                                @if ($member->user_id !== $family->father_id)
                                    <a href="{{ route('admin.family.members.change-role-form', $member->id) }}" class="text-blue-600 hover:underline mr-3">
                                        {{ __('Edit') }}
                                    </a>
                                    <form action="{{ route('admin.family.members.remove', $member->id) }}" method="POST" class="inline" onsubmit="return confirm('Remove this member from the family?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">{{ __('Remove') }}</button>
                                    </form>
                                @else
                                    <span class="text-gray-400 text-sm">{{ __('Family Head') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-4 text-center text-gray-500">{{ __('No members yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pending Invitations --}}
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="px-5 py-4 sm:px-6 sm:py-5">
            <h3 class="text-base font-medium text-gray-800 dark:text-white/90">{{ __('Invitations') }}</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800 text-left">
                        <th class="px-5 py-3">{{ __('Email') }}</th>
                        <th class="px-5 py-3">{{ __('Role') }}</th>
                        <th class="px-5 py-3">{{ __('Status') }}</th>
                        <th class="px-5 py-3">{{ __('Expires') }}</th>
                        <th class="px-5 py-3 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invitations as $invitation)
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-5 py-4">{{ $invitation->email }}</td>
                            <td class="px-5 py-4 capitalize">{{ $invitation->role }}</td>
                            <td class="px-5 py-4">
                                @if ($invitation->isAccepted())
                                    <span class="text-green-600">{{ __('Accepted') }}</span>
                                @elseif ($invitation->isExpired())
                                    <span class="text-red-600">{{ __('Expired') }}</span>
                                @else
                                    <span class="text-yellow-600">{{ __('Pending') }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">{{ $invitation->expires_at?->format('d M Y') ?? '—' }}</td>
                            <td class="px-5 py-4 text-right">
                                @if (!$invitation->isAccepted())
                                    <form action="{{ route('admin.family.invitations.resend', $invitation->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-blue-600 hover:underline mr-3">{{ __('Resend') }}</button>
                                    </form>
                                    <form action="{{ route('admin.family.invitations.revoke', $invitation->id) }}" method="POST" class="inline" onsubmit="return confirm('Revoke this invitation?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">{{ __('Revoke') }}</button>
                                    </form>
                                @else
                                    <span class="text-gray-400 text-sm">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-4 text-center text-gray-500">{{ __('No invitations yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
