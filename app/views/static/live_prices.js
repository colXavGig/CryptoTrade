export default function initLivePrices() {
    const tableBody = document.querySelector('#live-price-table tbody');

    console.log("initLivePrices called");
    if (!tableBody) {
        console.warn('initLivePrices(): #live-price-table tbody not found.');
        return;
    }

    const loadLivePrices = async () => {
        try {
            console.log("Fetching live prices...");
            const res = await fetch('/?route=api/prices/with_previous');
            const { data } = await res.json();

            console.log("Data received:", data);

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

                console.log("Appending row for", crypto.symbol);
                tableBody.innerHTML += row;
            });

        } catch (err) {
            console.error('Failed to fetch live prices:', err);
        }
    };

    loadLivePrices();
    setInterval(loadLivePrices, 60000);
}
