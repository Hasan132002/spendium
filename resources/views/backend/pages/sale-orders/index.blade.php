@extends('backend.layouts.app')
<!-- @foreach(auth()->user()->getAllPermissions() as $permission)
    <li>{{ $permission->name }}</li>
@endforeach -->

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
            <a href="{{ route('admin.sale-orders.create') }}"
                class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg hover:opacity-90 transition-all">
                <i class="bi bi-plus-lg mr-2"></i> {{ __('Add New') }}
            </a>
        </div>
                               

        <div class="overflow-x-auto bg-white dark:bg-gray-900 rounded-lg shadow-sm">
            <table class="w-full text-sm text-left text-gray-700 dark:text-gray-300">
                <thead class="text-xs uppercase bg-gray-100 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3">S.No</th>
                        <th class="px-6 py-3">Order Number</th>
                        <th class="px-6 py-3">Customer</th>
                        <th class="px-6 py-3">Total</th>
                        <th class="px-6 py-3">CANCELED</th>
                        <th class="px-6 py-3">Date</th>
                        <!-- <th class="px-6 py-3">Status</th> -->
                        <th class="px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr class="bg-white border-b dark:bg-gray-900 dark:border-gray-700">
                            <td class="px-6 py-4">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4">{{ $order->DocNum }}</td>
                            <td class="px-6 py-4">{{ $order->CardCode }}</td>
                            <td class="px-6 py-4">{{ $order->DocTotal }}</td>
                            <td class="px-6 py-4">{{ $order->CANCELED }}</td>
                            <td class="px-6 py-4">{{ $order->DocDate }}</td>
                            <!-- <td class="px-6 py-4">
                                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $order->status == 'completed' ? 'bg-green-200 text-green-800' : 'bg-yellow-200 text-yellow-800' }}">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td> -->
                            <td class="px-6 py-4 flex items-center space-x-2">

                                {{-- View --}}
                                @can('sale-orders.view')
                                    <a href="{{ route('admin.sale-orders.view', $order->DocEntry) }}"
                                        class="text-blue-600 hover:text-blue-800" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                @endcan

                                {{-- Edit --}}
                                @can('sale-orders.edit')
                                    <a href="{{ route('admin.sale-orders.edit', $order->DocEntry) }}"
                                        class="text-yellow-500 hover:text-yellow-700" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                @endcan
                                


                                {{-- Delete --}}
                                @can('sale-orders.destroy')
                                    <form action="{{ route('admin.sale-orders.destroy', $order->DocEntry) }}" method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this sale order?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endcan

                                  <!-- <form action="{{ route('admin.sale-orders.destroy', $order->DocEntry) }}" method="POST"
                                    onsubmit="return confirm('Are you sure you want to delete this sale order?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form> -->


                                {{-- WhatsApp Share --}}
                                @can('sale-orders.whatsapp')
                                    <a href="https://wa.me/?text=Order%20Number:{{ $order->DocNum }}%0ACustomer:{{ $order->CardCode }}%0ATotal:{{ $order->DocTotal }}%0Ahttps://falanadhimkana.com/orders/{{ $order->DocNum }}"
                                        target="_blank" class="text-green-600 hover:text-green-800" title="Share on WhatsApp">
                                        <i class="bi bi-whatsapp"></i>
                                    </a>
                                @endcan

                            </td>


                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="p-4">
                {{ $orders->links() }}
            </div>
        </div>
    </div>

@endsection

