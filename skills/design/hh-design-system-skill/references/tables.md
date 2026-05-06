# Tables

Dense, sortable tables are the workhorse of HH apps. Right-align numerics, use tabular numerals, and reserve row hover for interactivity.

## Default table

```html
<table class="hh-table">
  <thead>
    <tr>
      <th class="sortable">Tienda</th>
      <th>Cluster</th>
      <th class="right sortable">Ventas MTD</th>
      <th class="right">% Alcance</th>
      <th>Estado</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>R048 · Fashion Drive</td>
      <td><span class="hh-badge">Premium</span></td>
      <td class="right num">$1,247,580</td>
      <td class="right num" style="color:var(--hh-success)">113.4%</td>
      <td>
        <span class="hh-badge hh-badge--success">
          <span class="hh-dot hh-dot--success"></span>En meta
        </span>
      </td>
    </tr>
  </tbody>
</table>
```

## Important classes

- `.right` on `<th>` or `<td>` — right-align (always for numeric columns)
- `.num` on `<td>` — apply mono font and tabular numerals
- `.sortable` on `<th>` — adds sort arrow indicator and hover state
- `.total-row` on `<tr>` — bold + raised background for sum/total rows

## Total row

```html
<tr class="total-row">
  <td colspan="2">Total · 4 tiendas</td>
  <td class="right num">$3,993,340</td>
  <td class="right num">101.1%</td>
  <td></td>
</tr>
```

## Sticky header (for long lists)

Wrap the table in a scroll container and add inline `position:sticky;top:0` to thead:

```html
<div style="max-height:600px;overflow-y:auto">
  <table class="hh-table">
    <thead style="position:sticky;top:0;z-index:1">
      <tr>...</tr>
    </thead>
    <tbody>...</tbody>
  </table>
</div>
```

## Status colors in cells

When showing achievement %, use color to encode performance:
- ≥ 100% → `var(--hh-success)`
- 90–99% → `var(--hh-warning)`
- < 90% → `var(--hh-danger)`

## Do

- Right-align all numeric columns with `.right .num`
- Use tabular numerals so digits line up at the decimal
- One status badge per row in the rightmost column
- Reserve color for status, not decoration

## Don't

- Don't left-align numbers in proportional font
- Don't add zebra striping (it competes with the row hover)
- Don't put more than one badge per cell
