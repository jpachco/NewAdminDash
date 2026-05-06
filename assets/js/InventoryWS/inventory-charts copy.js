document.addEventListener('DOMContentLoaded', function () {
    const fillRateCharts = document.querySelectorAll('.chart-fillrate');
    
    const colors = {
        'Highlife': '#0d6efd',
        'Mensfashion': '#198754',
        'Roberts': '#212529'
    };

    fillRateCharts.forEach(canvas => {
        const empresa = canvas.dataset.empresa;
        const pct = parseFloat(canvas.dataset.pct);
        const color = colors[empresa] || '#6c757d';

        new Chart(canvas, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [pct, 100 - pct],
                    backgroundColor: [color, '#e9ecef'],
                    borderWidth: 0,
                    circumference: 180,
                    rotation: 270,
                    cutout: '85%'
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: { tooltip: { enabled: false }, legend: { display: false } }
            }
        });
    });
});