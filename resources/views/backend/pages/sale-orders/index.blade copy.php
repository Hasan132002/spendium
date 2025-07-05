@extends('backend.layouts.app')

@section('title')
    {{ __('Sale Orders') }} - {{ config('settings.app_name') ?? config('app.name') }}
@endsection

@section('admin-content')

<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
    @include('backend.layouts.partials.messages')

    <div class="mb-6 flex items-center justify-between gap-3">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">
            {{ __('Sale Orders') }}
        </h2>
        <a href="{{ route('admin.sale-orders.create') }}" class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg hover:opacity-90 transition-all">
            <i class="bi bi-plus-lg mr-2"></i> {{ __('Add New') }}
        </a>
    </div>

    <!-- @if ($orders->isEmpty()) -->
        <!-- <x-backend.empty message="No Sale Orders Found" /> -->
    <!-- @else -->
        <div class="overflow-x-auto bg-white dark:bg-gray-900 rounded-lg shadow-sm">
            <table class="w-full text-sm text-left text-gray-700 dark:text-gray-300">
                <thead class="text-xs uppercase bg-gray-100 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3">#</th>
                        <th class="px-6 py-3">Order Number</th>
                        <th class="px-6 py-3">Customer</th>
                        <th class="px-6 py-3">Date</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($saleOrders as $order)
                        <tr class="bg-white border-b dark:bg-gray-900 dark:border-gray-700">
                            <td class="px-6 py-4">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4">{{ $order->order_number }}</td>
                            <td class="px-6 py-4">{{ $order->customer_name }}</td>
                            <td class="px-6 py-4">{{ $order->order_date }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $order->status == 'completed' ? 'bg-green-200 text-green-800' : 'bg-yellow-200 text-yellow-800' }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.sale-orders.edit', $order->id) }}" class="text-blue-600 hover:text-blue-800">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="{{ route('admin.sale-orders.destroy', $order->id) }}" method="POST" class="inline-block ml-2" onsubmit="return confirm('Are you sure?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    <!-- @endif -->
</div>
@endsection
