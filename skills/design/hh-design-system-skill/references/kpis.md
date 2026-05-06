# KPI Cards

The signature of every HH dashboard. Flat, dense, separated by 1px borders.

## Standard grid

```html
<div class="hh-kpi-grid">
  <div class="hh-kpi">
    <div class="hh-kpi-label">Ventas MTD</div>
    <div class="hh-kpi-value">$3.99M</div>
    <div class="hh-kpi-sub">
      <span class="hh-kpi-trend up">▲ 12.4%</span> vs mes anterior
    </div>
  </div>
  <div class="hh-kpi">
    <div class="hh-kpi-label">% Alcance</div>
    <div class="hh-kpi-value">101.1%</div>
    <div class="hh-kpi-sub">Meta: 100%</div>
  </div>
  <!-- ... more KPIs ... -->
</div>
```

The grid auto-fits as many columns as fit (min 180px each) and uses 1px borders as dividers (no gaps).

## Trend indicators

```html
<span class="hh-kpi-trend up">▲ 12.4%</span>     <!-- green -->
<span class="hh-kpi-trend down">▼ 2.1%</span>    <!-- red -->
```

## Value formatting

**Currency (compact for KPIs):**
- `$3.99M` not `$3,994,231.50`
- `$847K` not `$847,392`
- `$284` for small amounts (no decimals)

**Use a helper:**
```javascript
const fmtMXNCompact = (n) => {
  if (Math.abs(n) >= 1e6) return '$' + (n / 1e6).toFixed(2) + 'M';
  if (Math.abs(n) >= 1e3) return '$' + (n / 1e3).toFixed(1) + 'K';
  return '$' + n.toFixed(0);
};
```

**Percentages:** one decimal place (`101.1%`, not `101.13%` or `101%`).

**Counts:** comma-separated thousands (`12,847`, not `12847` or `12K` for inventories).

## The context rule

Every KPI must include context. Pick at least one:
1. **Delta** vs prior period (`▲ 12.4% vs mes anterior`)
2. **Target** comparison (`Meta: 100%`)
3. **Related metric** (`Foot traffic: 70,478`)

Never ship a context-free number — it tells the user nothing.

## Loading state

Use skeletons while data loads:

```html
<div class="hh-kpi">
  <div class="hh-skeleton hh-skeleton--text" style="width:60%"></div>
  <div class="hh-skeleton hh-skeleton--kpi"></div>
  <div class="hh-skeleton hh-skeleton--text" style="width:80%;margin:0"></div>
</div>
```

## Do

- Format big numbers compactly ($3.99M)
- Always include context (delta, target, or related metric)
- One value per card; tabular numerals (built into `.hh-kpi-value`)
- Labels in uppercase Spanish

## Don't

- Don't show full precision in a KPI (`$3,994,231.50` competes with the label)
- Don't ship context-free numbers
- Don't mix multiple values in one card — split into two cards
