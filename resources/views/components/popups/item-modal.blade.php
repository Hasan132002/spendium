<div id="itemModal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-60">
    <div
        class="relative mx-auto my-20 w-full sm:w-4/5 lg:w-3/5 max-h-[90vh] overflow-y-auto bg-white rounded-lg shadow-xl">
        <div class="flex justify-between items-center p-4 border-b">
            <h2 class="text-lg font-semibold">Select Item</h2>
            <button onclick="closeItemModal()" class="text-2xl text-red-600 font-bold">×</button>
        </div>

        <div class="p-4">
            <!-- 🔍 Search -->
            <input type="text" id="itemSearch" onkeyup="filterItems()" placeholder="Search by item name..."
                class="mb-4 w-full rounded border p-2 focus:outline-none focus:ring">

            <!-- 📄 Table -->
            <div class="overflow-x-auto">
                <table class="w-full table-auto border-collapse">
                    <thead class="bg-light">
    <tr>
        <th class="px-3 py-2 border">Item Code</th>
        <th class="px-3 py-2 border">Item Name</th>
        <th class="px-3 py-2 border">Qty</th>
        <th class="px-3 py-2 border">Brand</th>
        <th class="px-3 py-2 border">Price</th>
        <th class="px-3 py-2 border">Discount</th>
        <th class="px-3 py-2 border">Tax</th>
    </tr>
</thead>
<tbody id="itemTableBody">
    @foreach($items as $item)
        <tr onclick="selectItem(
                '{{ $item->ItemCode }}',
                '{{ $item->ItemName }}',
                '{{ $item->Price }}',
                '{{ $item->Discount ?? 0 }}',
                '{{ $item->TaxRate ?? 0 }}',
                '{{ $item->account_code }}',
                '{{ $item->VatGroup }}'
            )" class="cursor-pointer hover:bg-light">
            <td class="px-3 py-2 border">{{ $item->ItemCode }}</td>
            <td class="px-3 py-2 border">{{ $item->ItemName }}</td>
            <td class="px-3 py-2 border">{{ $item->OnHand }}</td>
            <td class="px-3 py-2 border">{{ $item->U_BrandCategory }}</td>
            <td class="px-3 py-2 border">{{ $item->Price }}</td>
            <td class="px-3 py-2 border">{{ $item->Discount ?? 0 }}</td>
            <td class="px-3 py-2 border">{{ $item->TaxRate ?? 0 }}</td>
        </tr>
    @endforeach
</tbody>

                </table>
            </div>

            <!-- Pagination at the bottom -->
            <div class="mt-4" id="itemPagination">
                {!! $items->withQueryString()->links() !!}
            </div>
        </div>
    </div>
</div>


<script>
    function openItemModal() {
        document.getElementById("itemModal").classList.remove("hidden");
    }

    function closeItemModal() {
        document.getElementById("itemModal").classList.add("hidden");
    }

    // function selectItem(code) {
    //     document.getElementById("ItemCode").value = code;
    //     closeItemModal();
    // }

    function selectItem(code, name, price, discount, tax,account_code,VatGroup) {
        document.getElementById("ItemCode").value = code;
        document.getElementById("ItemName").value = name;
        document.getElementById("Price").value = price;
        document.getElementById("Discount").value = discount;
        document.getElementById("TaxRate").value = tax;
         document.getElementById("account_code").value = account_code;
         document.getElementById("VatGroup").value = VatGroup;

        closeItemModal();
    }


    function filterItems(page = 1) {
        const search = document.getElementById("itemSearch").value;

        fetch(`{{ route('admin.sale-orders.create') }}?item_search=${search}&page=${page}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(response => response.text())
            .then(html => {
                // Create a temporary container to extract parts of response
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                // Replace table body
                const newTableBody = doc.querySelector('#itemTableBody');
                document.querySelector('#itemTableBody').innerHTML = newTableBody.innerHTML;

                // Replace pagination
                const newPagination = doc.querySelector('#itemPagination');
                document.querySelector('#itemPagination').innerHTML = newPagination.innerHTML;
            });
    }


    document.addEventListener('click', function (e) {
        if (e.target.closest('#itemPagination a')) {
            e.preventDefault();
            const url = new URL(e.target.href);
            const page = url.searchParams.get("page");
            filterItems(page);
        }
    });

</script>