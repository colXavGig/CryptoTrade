document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.querySelector('#live-price-table tbody');
    let previousPrices = {};

    const loadLivePrices = async () => {
        try {
            const res = await fetch('/?route=api/prices/with_previous');
            const { data } = await res.json();

            tableBody.innerHTML = '';

            data.forEach(crypto => {
                const prev = crypto.previous ?? crypto.price;

                const trendIcon = crypto.price > prev
                    ? '<span class="trend-up">↑</span>'
                    : crypto.price < prev
                        ? '<span class="trend-down">↓</span>'
                        : '';

                const row = `
                <tr>
                    <td>${crypto.symbol}</td>
                    <td>${crypto.name}</td>
                    <td>$${crypto.price.toFixed(2)}</td>
                    <td class="price-prev">$${prev.toFixed(2)}</td>
                    <td>${trendIcon}</td>
                </tr>
            `;

                tableBody.innerHTML += row;
            });

        } catch (err) {
            console.error('Failed to fetch live prices:', err);
        }
    };


    loadLivePrices();
    setInterval(loadLivePrices, 60000);
});
