# Charts

We standardize on **Chart.js 4** for production. For React apps, **Recharts** with the same color tokens. For non-standard visualizations (network graphs, geo, sankey), reach for **D3** or **Plotly**.

## CRITICAL: Container sizing

When using Chart.js with `responsive: true` + `maintainAspectRatio: false`, you **must** wrap the canvas in a parent with explicit pixel height. Otherwise the chart will grow infinitely on every render frame.

```html
<!-- ✗ WRONG — chart grows infinitely -->
<div class="hh-chart-box">
  <canvas id="my-chart" height="200"></canvas>
</div>

<!-- ✓ CORRECT — wrap in fixed-height container -->
<div class="hh-chart-box">
  <div class="hh-chart-header">
    <div class="hh-chart-title">Ventas por Tienda · MTD</div>
  </div>
  <div style="position:relative;height:220px">
    <canvas id="my-chart"></canvas>
  </div>
</div>
```

Recommended heights:
- **220px** for primary charts
- **160px** for trend lines
- **140px** for sparklines / mini charts inside dashboards

## Theme object

Import from `assets/chart-theme.js` and apply to every chart:

```javascript
import { hhChartTheme, hhVizColors } from './chart-theme.js';

new Chart(canvas, {
  type: 'bar',
  data: {
    labels: ['R048', 'R032', 'H015'],
    datasets: [
      { label: 'Ventas', data: [1247, 985, 652], backgroundColor: hhVizColors[0], borderWidth: 0 },
      { label: 'Presupuesto', data: [1100, 1050, 800], backgroundColor: 'rgba(255,255,255,0.08)', borderWidth: 0 },
    ],
  },
  options: hhChartTheme,
});
```

## Stacked bars

```javascript
import { hhChartTheme, hhStackedScales, hhVizColors } from './chart-theme.js';

new Chart(canvas, {
  type: 'bar',
  data: {...},
  options: {
    ...hhChartTheme,
    scales: hhStackedScales,
  },
});
```

## Doughnut chart

```javascript
new Chart(canvas, {
  type: 'doughnut',
  data: {
    labels: ['Comisión Fija', 'Incentivo', 'Bono Tier'],
    datasets: [{
      data: [42, 28, 18],
      backgroundColor: hhVizColors.slice(0, 3),
      borderWidth: 0,
    }],
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        position: 'bottom',
        labels: { color: '#999', font: { size: 10, family: 'Inter' }, padding: 10, usePointStyle: true, pointStyleWidth: 8 },
      },
    },
  },
});
```

## Line chart with area fill

```javascript
new Chart(canvas, {
  type: 'line',
  data: {
    labels: days,
    datasets: [{
      label: 'Ventas',
      data: values,
      borderColor: hhVizColors[0],
      backgroundColor: 'rgba(65,118,141,0.15)',
      fill: true,
      tension: 0.3,
      borderWidth: 2,
      pointRadius: 0,
      pointHoverRadius: 4,
    }],
  },
  options: { ...hhChartTheme, plugins: { ...hhChartTheme.plugins, legend: { display: false } } },
});
```

## Color sequence

Use `hhVizColors` in order; cycle if you have more than 8 series:

1. `#41768d` brand blue (primary)
2. `#80a4b3` light blue
3. `#44aa99` teal
4. `#ddaa44` gold
5. `#e08a3c` orange
6. `#c07a8a` dusty rose
7. `#8a6fa0` muted violet
8. `#6b8e9b` slate blue

## Library choice

| Library | When to use |
|---|---|
| **Chart.js 4** | Default for bars, lines, doughnuts, area. Already in production at HH. |
| **Recharts** | React apps where declarative JSX is preferred. Same color tokens. |
| **D3** | Non-standard visualizations: network graphs, custom layouts. |
| **Plotly** | Geo maps, sankey, 3D, statistical plots. |
