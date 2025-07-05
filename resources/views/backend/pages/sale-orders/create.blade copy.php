@extends('backend.layouts.app')

@section('title')
    {{ __('Create Sale Order') }}
@endsection

@section('admin-content')

    <div class="p-6 mx-auto max-w-3xl">
        @include('backend.layouts.partials.messages')

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <form action="{{ route('admin.sale-orders.store') }}" method="POST">
                @csrf

                <div class="mb-6">
                    <label for="ItemCode" class="block mb-1 text-sm font-medium text-gray-700">Item Code</label>
                    <input type="text" id="ItemCode" name="ItemCode" class="w-full border rounded-lg p-2 cursor-pointer"
                        placeholder="Click to select Item" readonly onclick="openItemModal()">
                </div>
                <input type="hidden" id="account_code" name="account_code" value="">
                <input type="hidden" id="VatGroup" name="VatGroup" value="">

                <div class="mb-4">
                    <label for="ItemName" class="block text-sm font-medium">Item Description</label>
                    <input type="text" id="ItemName" name="ItemName" class="form-input w-full" readonly>
                </div>

                <!-- <div class="mb-4">
                    <label for="Price" class="block text-sm font-medium">Unit Price</label>
                    <input type="text" id="Price" name="Price" class="form-input w-full" readonly>
                </div> -->

                <div class="mb-4">
                    <label for="Discount" class="block text-sm font-medium">Discount</label>
                    <input type="text" id="Discount" name="Discount" class="form-input w-full" readonly>
                </div>

                <div class="mb-4">
                    <label for="TaxRate" class="block text-sm font-medium">Tax Rate</label>
                    <input type="text" id="TaxRate" name="TaxRate" class="form-input w-full" readonly>
                </div>

                <div class="mb-4">
                    <label for="Price" class="block text-sm font-medium">{{ __('Price ') }}</label>
                    <input type="text" id="Price" name="Price" class="form-input w-full" readonly>
                </div>


                @include('components.popups.item-modal')

                <div class="mb-4">
                    <label for="CardCode" class="block text-sm font-medium text-gray-700 dark:text-gray-400">Customer
                        Name</label>
                    <input type="text" name="CardCode" id="CardCode" required placeholder="Click to select Customer"
                        class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border px-4 py-2.5 cursor-pointer"
                        readonly onclick="openCustomerModal()">
                </div>
                <input type="hidden" name="CardName" id="CardName">
                <input type="hidden" name="address" id="address">

                @include('components.popups.customer-modal')


                <div class="mb-4">
                    <label class="block mb-1 font-medium">{{ __('Order Date') }}</label>
                    <input type="date" name="DocDueDate" class="form-input w-full" required>
                </div>

                <div class="mb-4">
                    <label class="block mb-1 font-medium">{{ __('Quantity ') }}</label>
                    <input type="text" name="Quantity" class="form-input w-full" required>
                </div>
                <!-- <div class="mb-4">
                    <label class="block mb-1 font-medium">{{ __('Price ') }}</label>
                    <input type="text" name="Price" class="form-input w-full" required>
                </div> -->

                <div class="mb-4">
                    <label class="block mb-1 font-medium">{{ __('Status') }}</label>
                    <select name="status" class="form-select w-full">
                        <option value="pending">{{ __('Pending') }}</option>
                        <option value="completed">{{ __('Completed') }}</option>
                    </select>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-primary text-white px-4 py-2 rounded hover:opacity-90">
                        {{ __('Create') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection