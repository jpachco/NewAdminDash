/* Haber Holding Design System v1.0 — Chart.js theme
 * Apply this options object to every Chart.js instance for visual consistency.
 *
 * USAGE:
 *   import { hhChartTheme, hhVizColors } from './chart-theme.js';
 *   new Chart(canvas, { type: 'bar', data: {...}, options: hhChartTheme });
 *
 * CRITICAL: always wrap the canvas in a parent div with explicit pixel height,
 * otherwise the chart will grow infinitely. See SKILL.md for details.
 */

export const hhChartTheme = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      labels: {
        color: '#999',
        font: { size: 10, family: 'Inter' },
        usePointStyle: true,
        pointStyleWidth: 8,
      },
    },
    tooltip: {
      backgroundColor: '#1a1a1a',
      borderColor: 'rgba(255,255,255,0.1)',
      borderWidth: 1,
      titleColor: '#fff',
      bodyColor: '#ccc',
      titleFont: { size: 11, family: 'Inter' },
      bodyFont: { size: 11, family: 'Inter' },
      padding: 8,
      displayColors: true,
    },
  },
  scales: {
    x: {
      grid: { color: 'rgba(255,255,255,0.04)', drawBorder: false },
      ticks: { color: '#999', font: { size: 10, family: 'Inter' } },
      border: { display: false },
    },
    y: {
      grid: { color: 'rgba(255,255,255,0.04)', drawBorder: false },
      ticks: { color: '#999', font: { size: 10, family: 'Inter' } },
      border: { display: false },
    },
  },
};

/* Standard categorical color sequence for series.
 * Use in order; cycle if you have more than 8 series. */
export const hhVizColors = [
  '#41768d',  // viz-1 brand blue
  '#80a4b3',  // viz-2 light blue
  '#44aa99',  // viz-3 teal
  '#ddaa44',  // viz-4 gold
  '#e08a3c',  // viz-5 orange
  '#c07a8a',  // viz-6 dusty rose
  '#8a6fa0',  // viz-7 muted violet
  '#6b8e9b',  // viz-8 slate blue
];

/* Helper for stacked bar charts */
export const hhStackedScales = {
  x: { ...hhChartTheme.scales.x, stacked: true },
  y: { ...hhChartTheme.scales.y, stacked: true },
};

/* MXN currency formatter for tooltip callbacks */
export const fmtMXN = (n) =>
  new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN', maximumFractionDigits: 0 }).format(n);

/* Compact MXN formatter for KPI displays */
export const fmtMXNCompact = (n) => {
  if (Math.abs(n) >= 1e6) return '$' + (n / 1e6).toFixed(2) + 'M';
  if (Math.abs(n) >= 1e3) return '$' + (n / 1e3).toFixed(1) + 'K';
  return '$' + n.toFixed(0);
};
