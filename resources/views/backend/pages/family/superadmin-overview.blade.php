@extends('backend.layouts.app')

@section('title')
    {{ __('All Families — System Overview') }} - {{ config('app.name') }}
@endsection

@section('admin-content')
<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">
                {{ __('All Families') }} — {{ __('Superadmin Overview') }}
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ __('Monitor every family, member, and invitation across the system.') }}
            </p>
        </div>
        <div class="flex gap-4 text-sm">
            <div class="text-right">
                <div class="text-xs text-gray-500">{{ __('Total Families') }}</div>
                <div class="text-xl font-bold text-gray-800 dark:text-white">{{ $families->count() }}</div>
            </div>
            <div class="text-right">
                <div class="text-xs text-gray-500">{{ __('Total Members') }}</div>
                <div class="text-xl font-bold text-gray-800 dark:text-white">{{ $families->sum('members_count') }}</div>
            </div>
            <div class="text-right">
                <div class="text-xs text-gray-500">{{ __('Pending Invites') }}</div>
                <div class="text-xl font-bold text-gray-800 dark:text-white">{{ $invitations->whereNull('accepted_at')->count() }}</div>
            </div>
        </div>
    </div>

    @include('backend.layouts.partials.messages')

    {{-- Families Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        @forelse ($families as $family)
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ $family->name }}</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ __('Head') }}: {{ $family->father?->name ?? '—' }}
                            ({{ $family->father?->email ?? '—' }})
                        </p>
                    </div>
                    <a href="{{ route('admin.users.login-as', $family->father_id) }}"
                       class="text-xs text-blue-600 hover:underline"
                       onclick="return confirm('Login as {{ $family->father?->name }}? You will be impersonating them.');">
                        <i class="bi bi-box-arrow-in-right mr-1"></i> {{ __('Login as Head') }}
                    </a>
                </div>
                <div class="p-4">
                    <p class="text-xs uppercase font-semibold text-gray-500 mb-3">
                        {{ __('Members') }} ({{ $family->members_count }})
                    </p>
                    <ul class="space-y-2">
                        @foreach ($family->members as $member)
                            <li class="flex items-center justify-between gap-3 text-sm">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-xs font-medium
                                        @if ($member->role === 'father') bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300
                                        @elseif ($member->role === 'mother') bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300
                                        @else bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300
                                        @endif">
                                        {{ strtoupper(substr($member->role, 0, 1)) }}
                                    </span>
                                    <div>
                                        <p class="font-medium text-gray-800 dark:text-white/90">{{ $member->user?->name ?? '—' }}</p>
                                        <p class="text-xs text-gray-500">{{ $member->user?->email ?? '—' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs px-2 py-0.5 rounded-full capitalize
                                        @if ($member->status === 'accepted') bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300
                                        @elseif ($member->status === 'pending') bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300
                                        @else bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300
                                        @endif">
                                        {{ $member->status }}
                                    </span>
                                    <span class="text-xs text-gray-500 capitalize">{{ $member->role }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-12 text-center text-gray-500">
                {{ __('No families in the system yet.') }}
            </div>
        @endforelse
    </div>

    {{-- Recent Invitations --}}
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Recent Invitations (all families)') }}</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800 text-left text-xs uppercase text-gray-500">
                        <th class="px-5 py-3">{{ __('Family') }}</th>
                        <th class="px-5 py-3">{{ __('Invitee Email') }}</th>
                        <th class="px-5 py-3">{{ __('Role') }}</th>
                        <th class="px-5 py-3">{{ __('Invited By') }}</th>
                        <th class="px-5 py-3">{{ __('Status') }}</th>
                        <th class="px-5 py-3">{{ __('Expires') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invitations as $invitation)
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-5 py-3">{{ $invitation->family?->name ?? '—' }}</td>
                            <td class="px-5 py-3">{{ $invitation->email }}</td>
                            <td class="px-5 py-3 capitalize">{{ $invitation->role }}</td>
                            <td class="px-5 py-3">{{ $invitation->inviter?->name ?? '—' }}</td>
                            <td class="px-5 py-3">
                                @if ($invitation->isAccepted())
                                    <span class="text-green-600">{{ __('Accepted') }}</span>
                                @elseif ($invitation->isExpired())
                                    <span class="text-red-600">{{ __('Expired') }}</span>
                                @else
                                    <span class="text-yellow-600">{{ __('Pending') }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-xs">{{ $invitation->expires_at?->format('d M Y') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-8 text-center text-gray-500">{{ __('No invitations yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
