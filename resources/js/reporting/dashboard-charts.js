import {
    Chart,
    ArcElement,
    Tooltip,
    Legend,
    CategoryScale,
    LinearScale,
    BarElement,
    LineElement,
    PointElement,
    DoughnutController,
    BarController,
    LineController,
} from 'chart.js';

Chart.register(
    ArcElement,
    Tooltip,
    Legend,
    CategoryScale,
    LinearScale,
    BarElement,
    LineElement,
    PointElement,
    DoughnutController,
    BarController,
    LineController,
);

window.initOperatorDashboard = function (typeDistribution, timeEvolution) {
    if (!typeDistribution || typeDistribution.length === 0) return;
    if (!timeEvolution || timeEvolution.length === 0) return;

    const doughnutCtx = document.getElementById('typeDistributionChart');
    if (doughnutCtx) {
        new Chart(doughnutCtx, {
            type: 'doughnut',
            data: {
                labels: typeDistribution.map(item => item.name),
                datasets: [{
                    data: typeDistribution.map(item => parseFloat(item.total_amount)),
                    backgroundColor: [
                        '#4e79a7', '#f28e2b', '#e15759', '#76b7b2', '#59a14f',
                        '#edc948', '#b07aa1', '#ff9da7', '#9c755f', '#bab0ac',
                    ],
                }],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                const value = parseFloat(ctx.parsed).toFixed(2);
                                return `${ctx.label}: S/ ${value}`;
                            },
                        },
                    },
                },
            },
        });
    }

    const lineCtx = document.getElementById('timeEvolutionChart');
    if (lineCtx) {
        new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: timeEvolution.map(item => item.date_label),
                datasets: [
                    {
                        label: 'Monto bruto operado',
                        data: timeEvolution.map(item => parseFloat(item.total_amount)),
                        borderColor: '#4e79a7',
                        backgroundColor: 'rgba(78, 121, 167, 0.1)',
                        fill: true,
                        tension: 0.3,
                    },
                    {
                        label: 'Cantidad de operaciones',
                        data: timeEvolution.map(item => parseInt(item.count)),
                        borderColor: '#f28e2b',
                        backgroundColor: 'rgba(242, 142, 43, 0.1)',
                        fill: true,
                        tension: 0.3,
                        yAxisID: 'y1',
                    },
                ],
            },
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                if (ctx.dataset.label === 'Monto bruto operado') {
                                    return `Monto bruto operado: S/ ${parseFloat(ctx.parsed.y).toFixed(2)}`;
                                }
                                return `${ctx.dataset.label}: ${ctx.parsed.y}`;
                            },
                        },
                    },
                },
                scales: {
                    y: {
                        type: 'linear',
                        position: 'left',
                        title: { display: true, text: 'Monto bruto operado (S/)' },
                    },
                    y1: {
                        type: 'linear',
                        position: 'right',
                        title: { display: true, text: 'Cantidad de operaciones' },
                        grid: { drawOnChartArea: false },
                    },
                },
            },
        });
    }
};

window.initAdminDashboard = function (typeDistribution, timeEvolution) {
    window.initOperatorDashboard(typeDistribution, timeEvolution);
};

window.initOperatorComparison = function (operators) {
    if (!operators || operators.length === 0) return;

    const barCtx = document.getElementById('comparisonBarChart');
    if (barCtx) {
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: operators.map(op => op.username_normalized),
                datasets: [{
                    label: 'Monto bruto operado',
                    data: operators.map(op => parseFloat(op.gross_amount)),
                    backgroundColor: '#4e79a7',
                    borderColor: '#396a93',
                    borderWidth: 1,
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                return `Monto bruto operado: S/ ${parseFloat(ctx.parsed.x).toFixed(2)}`;
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        title: { display: true, text: 'Monto bruto operado (S/)' },
                    },
                },
            },
        });
    }
};
