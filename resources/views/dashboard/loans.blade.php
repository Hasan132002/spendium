@extends('backend.layouts.app')

@section('title')
    {{ __('Loans') }} | {{ config('app.name') }}
@endsection

@section('admin-content')
<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('Loans') }}</h2>
        @can('family.loan.manage')
            <a href="{{ route('admin.loans.create') }}" class="btn-primary text-sm">
                <i class="bi bi-plus-lg mr-1"></i> {{ __('New Loan') }}
            </a>
        @endcan
    </div>

    @include('backend.layouts.partials.messages')

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800 text-left text-xs uppercase text-gray-500 dark:text-gray-400">
                        <th class="px-5 py-3">{{ __('Category') }}</th>
                        <th class="px-5 py-3">{{ __('Lender') }}</th>
                        <th class="px-5 py-3">{{ __('Amount') }}</th>
                        <th class="px-5 py-3">{{ __('Remaining') }}</th>
                        <th class="px-5 py-3">{{ __('Status') }}</th>
                        <th class="px-5 py-3">{{ __('Due') }}</th>
                        <th class="px-5 py-3 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($loans as $loan)
                        <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                            <td class="px-5 py-4">{{ $loan->category->name ?? '—' }}</td>
                            <td class="px-5 py-4">{{ $loan->lender ?? '—' }}</td>
                            <td class="px-5 py-4 font-semibold">{{ config('app.currency_symbol', '$') }}{{ number_format((float) $loan->amount, 2) }}</td>
                            <td class="px-5 py-4">{{ config('app.currency_symbol', '$') }}{{ number_format((float) $loan->remaining_amount, 2) }}</td>
                            <td class="px-5 py-4">
                                <span class="inline-block text-xs px-2 py-0.5 rounded-full capitalize
                                    @if ($loan->status === 'paid') bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300
                                    @elseif ($loan->status === 'partially_paid') bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300
                                    @else bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300 @endif">
                                    {{ str_replace('_', ' ', $loan->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-sm">{{ $loan->due_date ? \Carbon\Carbon::parse($loan->due_date)->format('d M Y') : '—' }}</td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ url('admin/loans/' . $loan->id) }}" class="text-blue-600 hover:underline text-sm mr-2"><i class="bi bi-eye"></i></a>
                                @can('personal.loan.contribute')
                                    <button type="button" onclick="document.getElementById('contrib-form-{{ $loan->id }}').classList.toggle('hidden')" class="text-green-600 hover:underline text-sm mr-2">
                                        <i class="bi bi-coin"></i> {{ __('Contribute') }}
                                    </button>
                                @endcan
                                @can('family.loan.manage')
                                    <form action="{{ route('admin.loans.destroy', $loan->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this loan?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline text-sm"><i class="bi bi-trash"></i></button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                        @can('personal.loan.contribute')
                            <tr id="contrib-form-{{ $loan->id }}" class="hidden bg-gray-50 dark:bg-white/[0.02]">
                                <td colspan="7" class="px-5 py-3">
                                    <form action="{{ route('admin.loans.contribute', $loan->id) }}" method="POST" class="flex flex-wrap gap-3 items-end">
                                        @csrf
                                        <input type="number" step="0.01" name="amount" required placeholder="Amount" class="h-9 w-40 rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                        <input type="text" name="note" placeholder="Note (optional)" class="h-9 flex-1 min-w-[200px] rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                        <button type="submit" class="btn-success text-sm">{{ __('Submit Contribution') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @endcan
                    @empty
                        <tr><td colspan="7" class="px-5 py-8 text-center text-gray-500">{{ __('No loans yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
