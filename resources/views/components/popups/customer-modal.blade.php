<div id="customerModal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-60">
    <div class="relative mx-auto my-20 w-full sm:w-4/5 lg:w-3/5 max-h-[90vh] overflow-y-auto bg-white rounded-lg shadow-xl">
        <div class="flex justify-between items-center p-4 border-b">
            <h2 class="text-lg font-semibold">Select Customer</h2>
            <button onclick="closeCustomerModal()" class="text-2xl text-red-600 font-bold">×</button>
        </div>

        <div class="p-4">
            <input type="text" id="customerSearch" onkeyup="filterCustomers()" placeholder="Search by customer name..."
                class="mb-4 w-full rounded border p-2 focus:outline-none focus:ring">

            <div class="overflow-x-auto">
                <table class="w-full table-auto border-collapse">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-3 py-2 border">Card Code</th>
                            <th class="px-3 py-2 border">Name</th>
                            <th class="px-3 py-2 border">Contact</th>
                        </tr>
                    </thead>
                    <tbody id="customerTableBody">
                        @foreach($customers as $cust)

                        <tr onclick="selectCustomer('{{ $cust->cardcode }}',
                        '{{ $cust->cardname }}', 
                        '{{ $cust->address }}')"
                         class="cursor-pointer hover:bg-gray-100">

                            <!-- <tr onclick="selectCustomer('{{ $cust->cardcode }}')" class="cursor-pointer hover:bg-gray-100"> -->
                                <td class="px-3 py-2 border">{{ $cust->cardcode }}</td>
                                <td class="px-3 py-2 border">{{ $cust->cardname }}</td>
                                <td class="px-3 py-2 border">{{ $cust->phone1 }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4" id="customerPagination">
                {!! $customers->withQueryString()->links() !!}
            </div>
        </div>
    </div>
</div>

<script>
    function openCustomerModal() {
        document.getElementById("customerModal").classList.remove("hidden");
    }

    function closeCustomerModal() {
        document.getElementById("customerModal").classList.add("hidden");
    }

    function selectCustomer(cardCode,cardName,address) {
        console.log(address);
        console.log(cardName);
        console.log(cardCode);
        document.getElementById("CardCode").value = cardCode;

        document.getElementById("CardName").value = cardName;
        document.getElementById("address").value = address;
        closeCustomerModal();
    }

  function filterCustomers(page = 1) {
    const search = document.getElementById("customerSearch").value;

    fetch(`{{ route('admin.sale-orders.create') }}?customer_search=${search}&page=${page}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.text())
    .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');

        const newTableBody = doc.querySelector('#customerTableBody');
        const newPagination = doc.querySelector('#customerPagination');

        const tableBody = document.querySelector('#customerTableBody');
        const pagination = document.querySelector('#customerPagination');

        if (newTableBody && tableBody) {
            tableBody.innerHTML = newTableBody.innerHTML;
        }

        if (newPagination && pagination) {
            pagination.innerHTML = newPagination.innerHTML;
        }
    });
}


    document.addEventListener('click', function (e) {
        if (e.target.closest('#customerPagination a')) {
            e.preventDefault();
            const url = new URL(e.target.href);
            const page = url.searchParams.get("page");
            filterCustomers(page);
        }
    });
</script>
