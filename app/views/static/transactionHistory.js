export default function initTransactionHistory() {
    const tableBody = document.querySelector("#transactions-table tbody");
    let cryptoNameMap = {};
    let allTransactions = [];
    let currentPage = 1;
    const rowsPerPage = 10;

    const filters = {
        type: document.getElementById("filter-type"),
        crypto: document.getElementById("filter-crypto"),
        minPrice: document.getElementById("min-price"),
        maxPrice: document.getElementById("max-price"),
        minAmount: document.getElementById("min-amount"),
        maxAmount: document.getElementById("max-amount"),
        startDate: document.getElementById("start-date"),
        endDate: document.getElementById("end-date"),
    };

    function formatNumber(value, decimals = 2) {
        return parseFloat(value).toLocaleString(undefined, {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        });
    }

    function filterTransactions(transactions) {
        return transactions.filter(tx => {
            if (filters.type.value && tx.transaction_type !== filters.type.value) return false;
            if (filters.crypto.value && tx.crypto_id !== parseInt(filters.crypto.value)) return false;
            if (filters.minPrice.value && tx.price < parseFloat(filters.minPrice.value)) return false;
            if (filters.maxPrice.value && tx.price > parseFloat(filters.maxPrice.value)) return false;
            if (filters.minAmount.value && tx.amount < parseFloat(filters.minAmount.value)) return false;
            if (filters.maxAmount.value && tx.amount > parseFloat(filters.maxAmount.value)) return false;
            if (filters.startDate.value && new Date(tx.created_at.date) < new Date(filters.startDate.value)) return false;
            if (filters.endDate.value && new Date(tx.created_at.date) > new Date(filters.endDate.value)) return false;
            return true;
        });
    }

    function renderTablePage(transactions, page) {
        const start = (page - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        const pageData = transactions.slice(start, end);

        tableBody.innerHTML = pageData.length > 0
            ? pageData.map(tx => `
                    <tr>
                        <td>${tx.id}</td>
                        <td>${tx.transaction_type}</td>
                        <td>${cryptoNameMap[tx.crypto_id] || `ID ${tx.crypto_id}`}</td>
                        <td>${formatNumber(tx.amount, 8)}</td>
                        <td>${formatNumber(tx.price, 2)}</td>
                        <td>${formatNumber(tx.amount * tx.price, 2)}</td>
                        <td>${new Date(tx.created_at.date).toLocaleString()}</td>
                    </tr>
                `).join('')
            : `<tr><td colspan="7">No transactions found.</td></tr>`;

        const totalPages = Math.ceil(transactions.length / rowsPerPage);
        document.getElementById("page-info").textContent = `Page ${currentPage} of ${totalPages}`;
        document.getElementById("prev-page").disabled = currentPage === 1;
        document.getElementById("next-page").disabled = currentPage === totalPages;
    }

    function applyFiltersAndRender() {
        const filtered = filterTransactions(allTransactions);
        currentPage = 1;
        renderTablePage(filtered, currentPage);
    }

    function setupPaginationControls(filtered) {
        document.getElementById("prev-page").onclick = () => {
            if (currentPage > 1) {
                currentPage--;
                renderTablePage(filtered, currentPage);
            }
        };
        document.getElementById("next-page").onclick = () => {
            const maxPage = Math.ceil(filtered.length / rowsPerPage);
            if (currentPage < maxPage) {
                currentPage++;
                renderTablePage(filtered, currentPage);
            }
        };
    }

    // Step 1: Fetch crypto names THEN transactions
    fetch("/?route=api/prices/live")
        .then(res => res.json())
        .then(data => {
            const raw = data.data;
            cryptoNameMap = Object.values(raw).reduce((acc, c) => {
                acc[c.id] = `${c.name} (${c.symbol})`;
                return acc;
            }, {});

            // Now load transactions
            fetch("/?route=api/user/transactions", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: new URLSearchParams({ csrf_token: CSRF_TOKEN })
            })
                .then(res => res.json())
                .then(data => {
                    allTransactions = data.transactions || [];
                    const filtered = filterTransactions(allTransactions);
                    renderTablePage(filtered, currentPage);
                    setupPaginationControls(filtered);
                });
        })
        .catch(err => console.error("Failed to load crypto name map or transactions:", err));

    // Hook up filters
    Object.values(filters).forEach(input => {
        input.addEventListener("input", applyFiltersAndRender);
    });
};