
export default function initChartViewer()  {
    const chartCanvas = document.getElementById('crypto-chart');
    const tabContainer = document.getElementById('crypto-tabs');
    const rangeSelector = document.getElementById('data-range');
    let chart = null;
    let activeId = null;

    const loadTabs = async () => {
        try {
            const res = await fetch('/?route=api/prices/live');
            const json = await res.json();

            console.log("Received from /?route=api/prices/live:", json);

            const { data } = json;
            const cryptos = Object.values(data);

            tabContainer.innerHTML = '';

            cryptos.forEach(crypto => {
                const btn = document.createElement('button');
                btn.textContent = crypto.symbol;
                btn.classList.add('tab-btn');
                btn.onclick = () => {
                    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    activeId = crypto.id;
                    loadChart(crypto.id);
                };
                tabContainer.appendChild(btn);
            });

            if (cryptos.length > 0) {
                const firstBtn = tabContainer.querySelector('button');
                firstBtn.classList.add('active');
                activeId = cryptos[0].id;
                await loadChart(cryptos[0].id);
            }
        } catch (err) {
            console.error('Failed to load crypto tabs:', err);
        }
    };


    const loadChart = async (cryptoId) => {
        try {
            const limit = rangeSelector.value;
            const res = await fetch(`/?route=api/prices/chart&crypto_id=${cryptoId}&limit=${limit}`);
            const { data } = await res.json();

            const labels = data.map(p =>
                new Date(p.created_at).toLocaleString([], {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                })
            ).reverse();

            const prices = data.map(p => p.price).reverse();

            if (chart) chart.destroy();

            chart = new Chart(chartCanvas, {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        label: 'Price',
                        data: prices,
                        fill: false,
                        borderColor: '#4bc0c0',
                        borderWidth: 2,
                        tension: 0.3,
                        pointRadius: 0
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: context => `$${context.formattedValue}`
                            }
                        }
                    },
                    scales: {
                        x: {
                            title: { display: true, text: 'Time' },
                            ticks: { maxTicksLimit: 10 }
                        },
                        y: {
                            title: { display: true, text: 'Price (USD)' },
                            beginAtZero: false
                        }
                    }
                }
            });
        } catch (err) {
            console.error('Failed to load chart data:', err);
        }
    };

    rangeSelector.addEventListener('change', () => {
        if (activeId !== null) {
            loadChart(activeId);
        }
    });

    loadTabs();

    // Auto-refresh every 60 seconds
    setInterval(() => {
        if (activeId !== null) {
            loadChart(activeId);
        }
    }, 60000);
};

