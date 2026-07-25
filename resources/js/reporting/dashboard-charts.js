import Chart from 'chart.js/auto';

window.Chart = Chart;

const CHART_COLORS = {
    primary: '#000000',
    tertiaryFixed: '#4edea3',
    error: '#ba1a1a',
    primaryFixed: '#bec6e0',
    surfaceVariant: '#e4e2e4',
    onSurfaceVariant: '#45464d',
    secondary: '#505f76',
    surfaceContainerHigh: '#eae7e9',
    primaryFixedDim: '#bec6e0',
    secondaryContainer: '#d0e1fb',
    onSecondaryFixed: '#0b1c30',
};

Chart.defaults.font.family = "system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif";
Chart.defaults.color = CHART_COLORS.onSurfaceVariant;
Chart.defaults.scale.grid.color = CHART_COLORS.surfaceVariant;
Chart.defaults.plugins.tooltip.backgroundColor = '#1b1b1d';
Chart.defaults.plugins.tooltip.padding = 12;
Chart.defaults.plugins.tooltip.cornerRadius = 4;

export function initCharts(dashboardType, data) {
    if (dashboardType === 'operator') {
        initOperatorCharts(data);
    } else if (dashboardType === 'admin') {
        initAdminCharts(data);
    }
}

function initOperatorCharts(data) {
    const barCtx = document.getElementById('opsByHourChart');
    if (barCtx) {
        new Chart(barCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: data.evolution?.labels || [],
                datasets: [
                    {
                        label: 'Entradas',
                        data: data.evolution?.entradas || [],
                        backgroundColor: CHART_COLORS.tertiaryFixed,
                        borderRadius: 4,
                        barPercentage: 0.6,
                        categoryPercentage: 0.8,
                    },
                    {
                        label: 'Salidas',
                        data: data.evolution?.salidas || [],
                        backgroundColor: CHART_COLORS.error,
                        borderRadius: 4,
                        barPercentage: 0.6,
                        categoryPercentage: 0.8,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { stacked: true, grid: { display: false } },
                    y: { stacked: true, border: { display: false }, beginAtZero: true },
                },
            },
        });
    }

    const doughnutCtx = document.getElementById('opsByTypeChart');
    if (doughnutCtx) {
        new Chart(doughnutCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: (data.distribution || []).map(function(d) { return d.type; }),
                datasets: [{
                    data: (data.distribution || []).map(function(d) { return d.count; }),
                    backgroundColor: [CHART_COLORS.tertiaryFixed, CHART_COLORS.error, CHART_COLORS.primaryFixed],
                    borderWidth: 0,
                    hoverOffset: 4,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: { legend: { display: false } },
            },
        });
    }
}

function initAdminCharts(data) {
    const evolutionCtx = document.getElementById('evolutionChart');
    if (evolutionCtx) {
        new Chart(evolutionCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: data.evolution?.labels || [],
                datasets: [{
                    label: 'Volumen de Transacciones',
                    data: data.evolution?.data || [],
                    borderColor: CHART_COLORS.onSecondaryFixed,
                    backgroundColor: CHART_COLORS.secondaryContainer + '66',
                    borderWidth: 2,
                    pointBackgroundColor: CHART_COLORS.onSecondaryFixed,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    fill: true,
                    tension: 0.4,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, border: { display: false }, ticks: { maxTicksLimit: 6 } },
                    x: { grid: { display: false }, border: { display: false } },
                },
            },
        });
    }

    const doughnutCtx = document.getElementById('bankDoughnutChart');
    if (doughnutCtx) {
        new Chart(doughnutCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: (data.bankDistribution || []).map(function(d) { return d.bank; }),
                datasets: [{
                    data: (data.bankDistribution || []).map(function(d) { return d.percentage; }),
                    backgroundColor: [
                        CHART_COLORS.onSecondaryFixed,
                        CHART_COLORS.secondary,
                        CHART_COLORS.primaryFixedDim,
                        CHART_COLORS.surfaceContainerHigh,
                    ],
                    borderWidth: 0,
                    hoverOffset: 4,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: { legend: { display: false } },
            },
        });
    }

    const barCtx = document.getElementById('flowBarChart');
    if (barCtx) {
        new Chart(barCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: data.flowByRegion?.labels || [],
                datasets: [
                    {
                        label: 'Entradas',
                        data: data.flowByRegion?.cash_in || [],
                        backgroundColor: CHART_COLORS.tertiaryFixed,
                        borderRadius: 2,
                        barPercentage: 0.6,
                        categoryPercentage: 0.8,
                    },
                    {
                        label: 'Salidas',
                        data: data.flowByRegion?.cash_out || [],
                        backgroundColor: CHART_COLORS.surfaceContainerHigh,
                        borderRadius: 2,
                        barPercentage: 0.6,
                        categoryPercentage: 0.8,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', align: 'end', labels: { usePointStyle: true, boxWidth: 8, boxHeight: 8 } },
                },
                scales: {
                    y: { beginAtZero: true, border: { display: false }, ticks: { maxTicksLimit: 5 } },
                    x: { grid: { display: false }, border: { display: false } },
                },
            },
        });
    }
}
