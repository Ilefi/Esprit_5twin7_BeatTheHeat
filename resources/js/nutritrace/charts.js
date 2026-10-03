import { Chart, registerables } from 'chart.js';
import { prefersReducedMotion, tokenColor, tokenValue } from './tokens';

Chart.register(...registerables);

/**
 * Usage: <canvas x-data="ntChart(@js($config))"></canvas>
 * $config = ['type' => 'bar', 'labels' => [...], 'datasets' => [['label' => '…', 'data' => [...], 'colors' => ['primary', 'gold']]]]
 * Colors are token names (without --nt-), resolved to HSL at runtime.
 */
export default function registerCharts(Alpine) {
    Alpine.data('ntChart', (config) => ({
        chart: null,

        init() {
            const font = tokenValue('font-body') || 'Inter, sans-serif';
            const isCircular = ['doughnut', 'pie', 'polarArea'].includes(config.type);

            Chart.defaults.font.family = font;
            Chart.defaults.color = tokenColor('muted-foreground');

            const datasets = config.datasets.map((dataset) => {
                const names = dataset.colors ?? [dataset.color ?? 'primary'];
                const solid = names.map((name) => tokenColor(name));
                const soft = names.map((name) => tokenColor(name, config.type === 'line' ? 0.12 : 0.85));
                const many = names.length > 1;

                return {
                    label: dataset.label,
                    data: dataset.data,
                    backgroundColor: many ? soft : soft[0],
                    borderColor: isCircular ? tokenColor('surface') : (many ? solid : solid[0]),
                    borderWidth: isCircular ? 3 : 2,
                    borderRadius: config.type === 'bar' ? 6 : 0,
                    fill: config.type === 'line',
                    tension: 0.35,
                    pointRadius: config.type === 'line' ? 3 : 0,
                    pointBackgroundColor: solid[0],
                    maxBarThickness: 42,
                };
            });

            this.chart = new Chart(this.$el, {
                type: config.type,
                data: { labels: config.labels, datasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: prefersReducedMotion() ? false : { duration: 700 },
                    indexAxis: config.horizontal ? 'y' : 'x',
                    cutout: config.type === 'doughnut' ? '68%' : undefined,
                    plugins: {
                        legend: {
                            display: config.legend ?? (isCircular || datasets.length > 1),
                            position: 'bottom',
                            labels: { usePointStyle: true, boxWidth: 8, padding: 16 },
                        },
                        tooltip: {
                            backgroundColor: tokenColor('foreground'),
                            titleColor: tokenColor('surface'),
                            bodyColor: tokenColor('surface'),
                            padding: 10,
                            cornerRadius: 8,
                            callbacks: config.unit
                                ? { label: (ctx) => ` ${ctx.dataset.label ?? ''} ${ctx.formattedValue} ${config.unit}` }
                                : {},
                        },
                    },
                    scales: isCircular ? {} : {
                        x: { grid: { display: false }, border: { color: tokenColor('border') }, ticks: { precision: config.decimals ?? 0 } },
                        y: {
                            beginAtZero: true,
                            ticks: { precision: config.decimals ?? 0 },
                            grid: { color: tokenColor('border', 0.6) },
                            border: { display: false },
                        },
                    },
                },
            });
        },

        destroy() {
            this.chart?.destroy();
        },
    }));
}
