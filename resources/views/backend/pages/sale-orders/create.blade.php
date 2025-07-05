@extends('backend.layouts.app')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>

@section('title', 'Create Sale Order')

@section('admin-content')
    @php
        $isEdit = isset($saleOrder);
    @endphp

    <div class="p-6 mx-auto max-w-7xl">
        @include('backend.layouts.partials.messages')

        <div class="mb-6 flex items-center justify-between gap-3">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">Create Sale</h2>
            <a href="{{ route('admin.sale-orders.index') }}"
                class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg hover:opacity-90 transition-all">
                <i class="bi bi-arrow-left mr-2"></i> Back
            </a>
        </div>

        <!-- <form action="{{ route('admin.sale-orders.store') }}" method="POST">
            @csrf -->
        <form
            action="{{ $isEdit ? route('admin.sale-orders.update', $saleOrder->DocEntry) : route('admin.sale-orders.store') }}"
            method="POST">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            {{-- Master Fields --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div>
                    <label class="block mb-1 font-medium">Customer Name</label>
                    <!-- <input type="text" name="CardCode" id="CardCode" class="form-input w-full" readonly onclick="openCustomerModal()" placeholder="Click to select Customer"> -->
                    <input type="text" name="CardCode" id="CardCode" class="form-input w-full"
                        value="{{ old('CardCode', $saleOrder->CardCode ?? '') }}" readonly onclick="openCustomerModal()"
                        placeholder="Click to select Customer">

                </div>
                <div>
                    <label class="block mb-1 font-medium">Order Date</label>
                    <!-- <input type="date" name="DocDueDate" class="form-input w-full" required> -->
                    <input type="date" name="DocDueDate" class="form-input w-full" required
                        value="{{ old('DocDueDate', isset($saleOrder) ? \Carbon\Carbon::parse($saleOrder->DocDueDate)->toDateString() : '') }}">

                </div>
                <div>
                    <label class="block mb-1 font-medium">Status</label>
                    <select name="status" class="form-select w-full">
                        <option value="pending">Pending</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
            </div>

            {{-- Detail Table --}}
            <div class="mb-4">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="font-semibold text-lg">Order Items</h3>
                    <button type="button" onclick="addNewRow()"
                        class="px-3 py-1 text-sm text-white bg-green-600 rounded hover:bg-green-700">+ Add Row</button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full border text-sm text-left">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-3 py-2 border">Item Code</th>
                                <th class="px-3 py-2 border">Description</th>
                                <th class="px-3 py-2 border">Qty</th>
                                <th class="px-3 py-2 border">Discount</th>
                                <th class="px-3 py-2 border">Tax Rate</th>
                                <th class="px-3 py-2 border">Price</th>
                                <th class="px-3 py-2 border">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="itemDetailsTable">
                            @if($isEdit)
                                @foreach($saleOrderItems as $index => $item)
                                    <tr>
                                        <td class="border px-2 py-1">
                                            <input type="text" name="items[{{ $index }}][ItemCode]"
                                                class="form-input w-full item-code-input" readonly value="{{ $item->ItemCode }}">
                                        </td>
                                        <td class="border px-2 py-1">
                                            <input type="text" name="items[{{ $index }}][ItemName]"
                                                class="form-input w-full item-desc-input" readonly value="{{ $item->Dscription }}">
                                        </td>
                                        <td class="border px-2 py-1">
                                            <input type="number" name="items[{{ $index }}][Quantity]" class="form-input w-full"
                                                min="1" required value="{{ $item->Quantity }}">
                                        </td>
                                        <td class="border px-2 py-1">
                                            <input type="text" name="items[{{ $index }}][Discount]"
                                                class="form-input w-full item-discount-input" readonly value="0">
                                        </td>
                                        <td class="border px-2 py-1">
                                            <input type="text" name="items[{{ $index }}][TaxRate]" class="form-input w-full"
                                                value="{{ $item->VatPrcnt }}">
                                        </td>
                                        <td class="border px-2 py-1">
                                            <input type="number" name="items[{{ $index }}][Price]" class="form-input w-full"
                                                required value="{{ $item->Price }}">
                                        </td>
                                        <td class="border px-2 py-1 text-center">
                                            <button type="button" onclick="removeRow(this)"
                                                class="text-red-500 hover:underline">Delete</button>
                                        </td>
                                    </tr>
                                @endforeach

                            @endif
                        </tbody>

                        <!-- <tbody id="itemDetailsTable">
                        </tbody> -->
                    </table>
                </div>
            </div>

            {{-- Hidden fields --}}
            <!-- <input type="hidden" name="CardName" id="CardName">
            <input type="hidden" name="address" id="address"> -->
            <input type="hidden" name="CardName" id="CardName" value="{{ old('CardName', $saleOrder->CardName ?? '') }}">
            <input type="hidden" name="address" id="address" value="{{ old('address', $saleOrder->address ?? '') }}">

            {{-- Modals --}}
            @include('components.popups.item-modal')
            @include('components.popups.customer-modal')

            <div class="flex justify-end mt-6">
                <!-- <button type="submit" class="bg-primary text-white px-6 py-2 rounded hover:opacity-90">Create</button> -->
                <button type="submit" class="bg-primary text-white px-6 py-2 rounded hover:opacity-90">
                    {{ $isEdit ? 'Update' : 'Create' }}
                </button>

            </div>
        </form>
    </div>

    {{-- Template row for duplication --}}
    <template id="itemRowTemplate">
        <tr>
            <td class="border px-2 py-1">
                <input type="text" name="items[][ItemCode]" class="form-input w-full item-code-input" readonly
                    onclick="openItemModalForRow(this)">
            </td>
            <td class="border px-2 py-1">
                <input type="text" name="items[][ItemName]" class="form-input w-full item-desc-input" readonly>
            </td>
            <td class="border px-2 py-1">
                <input type="number" name="items[][Quantity]" class="form-input w-full" min="1" required>
            </td>
            <td class="border px-2 py-1">
                <input type="text" name="items[][Discount]" class="form-input w-full item-discount-input" readonly>
            </td>
            <td class="border px-2 py-1">
                <input type="text" name="items[][TaxRate]" class="form-input w-full item-tax-input" readonly>
            </td>
            <td class="border px-2 py-1">
                <input type="text" name="items[][Price]" class="form-input w-full item-price-input" readonly>
            </td>

            <td><input type="hidden" name="items[][account_code]" class="item-account-code"></td>
            <td><input type="hidden" name="items[][VatGroup]" class="item-vat-group"></td>
            <td class="border px-2 py-1 text-center">
                <button type="button" onclick="removeRow(this)" class="text-red-600 hover:text-red-800 font-bold">×</button>
            </td>
        </tr>
    </template>

    <script>

        //     document.addEventListener('DOMContentLoaded', () => {
        //     addNewRow();
        // });
        let itemIndexsss = 0;

        document.addEventListener('DOMContentLoaded', () => {
            const existingRowsCount = document.querySelectorAll('#itemDetailsTable tr').length;
            itemIndexsss = existingRowsCount;

            if (existingRowsCount === 0) {
                addNewRow();
            }
        });


        let currentInputElement = null;

        function updateRowTotal(row) {
            const quantityInput = row.querySelector('input[name^="items"][name$="[Quantity]"]');
            const priceInput = row.querySelector('.item-price-input');

            const quantity = parseFloat(quantityInput.value) || 0;
            const unitPrice = parseFloat(priceInput.dataset.originalPrice) || 0;

            const total = quantity * unitPrice;
            priceInput.value = total.toFixed(2);
        }


        let itemIndex = 0;

        function addNewRow() {
            const template = document.getElementById('itemRowTemplate').content.cloneNode(true);
            const newRow = template.querySelector('tr');

            // Update all inputs in this row to have indexed names
            const inputs = newRow.querySelectorAll('input');
            inputs.forEach(input => {
                const name = input.getAttribute('name'); // e.g. items[][Quantity]
                if (name) {
                    // replace [] with [itemIndex]
                    const newName = name.replace('[]', `[${itemIndex}]`);
                    input.setAttribute('name', newName);
                }
            });

            document.getElementById('itemDetailsTable').appendChild(newRow);

            reindexRows();

            const quantityInput = newRow.querySelector(`input[name="items[${itemIndex}][Quantity]"]`);
            quantityInput.addEventListener('input', () => updateRowTotal(newRow));

            document.getElementById('itemDetailsTable').appendChild(newRow);

            itemIndex++;
        }

        function reindexRows() {
            const rows = document.querySelectorAll('#itemDetailsTable tr');
            rows.forEach((row, index) => {
                const inputs = row.querySelectorAll('input');
                inputs.forEach(input => {
                    const name = input.getAttribute('name');
                    if (!name) return;

                    // Replace the current index inside [] with the new index
                    const newName = name.replace(/items\[\d+\]/, `items[${index}]`);
                    input.setAttribute('name', newName);
                });
            });

            // Update the global itemIndex variable as well to avoid index collisions when adding new rows
            itemIndex = rows.length;
        }


        function removeRow(btn) {
            btn.closest('tr').remove();
        }

        function openItemModalForRow(input) {
            currentInputElement = input.closest('tr');
            openItemModal();
        }

        function selectItem(code, name, price, discount, tax, account_code, VatGroup) {
            console.log(code, name, price, discount, tax, account_code, VatGroup);
            if (!currentInputElement) return;

            currentInputElement.querySelector('.item-code-input').value = code;
            currentInputElement.querySelector('.item-desc-input').value = name;
            currentInputElement.querySelector('.item-discount-input').value = discount;
            currentInputElement.querySelector('.item-tax-input').value = tax;

            const priceInput = currentInputElement.querySelector('.item-price-input');
            priceInput.dataset.originalPrice = price; // store actual unit price
            priceInput.value = price;

            const quantityInput = currentInputElement.querySelector('input[name^="items"][name$="[Quantity]"]');
            quantityInput.value = 1;
            currentInputElement.querySelector('.item-account-code').value = account_code;
            currentInputElement.querySelector('.item-vat-group').value = VatGroup;
            updateRowTotal(currentInputElement);

            quantityInput.addEventListener('input', () => updateRowTotal(currentInputElement));

            closeItemModal();
        }

    </script>
@endsection