document.addEventListener('DOMContentLoaded', function () {
    const canvasRef = document.getElementById('chart-fill-rate');
    if (!canvasRef) return;

    const rawData = JSON.parse(canvasRef.dataset.json);
    const colors = { 'Highlife': '#0d6efd', 'Mensfashion': '#198754', 'Roberts': '#dc3545' };
    
    // Obtenemos las etiquetas Mes/Año (asumiendo que Highlife tiene la serie completa)
   // const labels = Object.values(rawData)[0].map(d => `${d.Mes}/${d.Año}`);
   const labels =[ '1/2026', '2/2026', '3/2026', '4/2026', '5/2026', '6/2026', '7/2026', '8/2026', '10/2026', '11/2026', '12/2026'];
    // --- GRÁFICA 1: FILL RATE (LÍNEAS) ---
    new Chart(document.getElementById('chart-fill-rate'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: Object.keys(rawData).map(emp => ({
                label: emp,
                data: rawData[emp].map(d => d.fill_rate_pzas),
                borderColor: colors[emp],
                backgroundColor: colors[emp] + '22',
                tension: 0.3
            }))
        },
        options: { maintainAspectRatio: false, scales: { y: { max: 100 } } }
    });

    // --- GRÁFICA 2: Nº PEDIDOS (BARRAS) ---
    new Chart(document.getElementById('chart-pedidos'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: Object.keys(rawData).map(emp => ({
                label: emp,
                data: rawData[emp].map(d => d.num_pedidos),
                backgroundColor: colors[emp]
            }))
        },
        options: { maintainAspectRatio: false }
    });

    // --- GRÁFICA 3: VOLUMEN PEDIDO VS FACTURADO POR EMPRESA ---
    const volDatasets = [];
    Object.keys(rawData).forEach(emp => {
        // Línea punteada para lo pedido
        volDatasets.push({
            label: emp + ' (Pedido)',
            data: rawData[emp].map(d => d.pzas_pedidas),
            borderColor: colors[emp],
            borderDash: [5, 5],
            fill: false,
            tension: 0.3
        });
        // Línea sólida para lo facturado
        volDatasets.push({
            label: emp + ' (Facturado)',
            data: rawData[emp].map(d => d.pzas_facturadas),
            borderColor: colors[emp],
            backgroundColor: colors[emp],
            borderWidth: 3,
            fill: false,
            tension: 0.3
        });
    });

    new Chart(document.getElementById('chart-volumen-empresas'), {
        type: 'line',
        data: { labels: labels, datasets: volDatasets },
        options: { 
            maintainAspectRatio: false, 
            plugins: { legend: { position: 'bottom' } } 
        }
    });
});