@extends('backend.layouts.app')

@section('title')
    {{ __('View Sale Order') }}
@endsection

@section('admin-content')
<div class="p-6 mx-auto max-w-7xl">
    <div class="mb-6 flex items-center justify-between gap-3">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('View Sale Order') }}</h2>
        <a href="{{ route('admin.sale-orders.index') }}"
           class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg hover:opacity-90 transition-all">
            <i class="bi bi-arrow-left mr-2"></i> {{ __('Back') }}
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div>
            <label class="block mb-1 font-medium text-gray-800 dark:text-white">{{ __('Customer Code') }}</label>
            <input type="text" class="form-input w-full bg-gray-100 dark:bg-gray-800 dark:text-white" value="{{ $saleOrder->CardCode }}" readonly>
        </div>
        <div>
            <label class="block mb-1 font-medium text-gray-800 dark:text-white">{{ __('Customer Name') }}</label>
            <input type="text" class="form-input w-full bg-gray-100 dark:bg-gray-800 dark:text-white" value="{{ $saleOrder->CardName }}" readonly>
        </div>
        <div>
            <label class="block mb-1 font-medium text-gray-800 dark:text-white">{{ __('Order Date') }}</label>
            <input type="date" class="form-input w-full bg-gray-100 dark:bg-gray-800 dark:text-white"
                   value="{{ \Carbon\Carbon::parse($saleOrder->DocDueDate)->toDateString() }}" readonly>
        </div>
    </div>

    <div class="mb-4">
        <h3 class="font-semibold text-lg mb-2 text-gray-800 dark:text-white">{{ __('Order Items') }}</h3>
        <div class="overflow-x-auto">
            <table class="w-full border text-sm text-left">
                <thead class="bg-gray-100 dark:bg-gray-800 dark:text-white">
                    <tr>
                        <th class="p-2 text-left px-5">{{ __('Item Code') }}</th>
                        <th class="p-2 text-left px-5">{{ __('Description') }}</th>
                        <th class="p-2 text-left px-5">{{ __('Qty') }}</th>
                        <th class="p-2 text-left px-5">{{ __('Price') }}</th>
                        <th class="p-2 text-left px-5">{{ __('Tax Rate') }}</th>
                        <th class="p-2 text-left px-5">{{ __('Line Total') }}</th>
                    </tr>
                </thead>
               <tbody>
    @foreach($saleOrderItems as $item)
        <tr class="border-b dark:border-gray-700">
            <td class="px-3 py-2 text-gray-800 dark:text-white">{{ $item->ItemCode }}</td>
            <td class="px-3 py-2 text-gray-800 dark:text-white">{{ $item->Dscription }}</td>
            <td class="px-3 py-2 text-gray-800 dark:text-white">{{ $item->Quantity }}</td>
            <td class="px-3 py-2 text-gray-800 dark:text-white">{{ $item->Price }}</td>
            <td class="px-3 py-2 text-gray-800 dark:text-white">{{ $item->VatPrcnt }}%</td>
            <td class="px-3 py-2 text-gray-800 dark:text-white">{{ $item->LineTotal }}</td>
        </tr>
    @endforeach
</tbody>

            </table>
        </div>
    </div>
</div>
@endsection
