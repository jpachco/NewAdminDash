# Feedback Components

Tooltips, dropdown menus, breadcrumbs, pagination, empty states, and skeleton loaders.

## Tooltip

For brief, supplemental info on hover. Never for critical content.

```html
<div class="hh-tooltip-wrap">
  <button class="hh-btn hh-btn--icon hh-btn--secondary">
    <svg class="hh-icon hh-icon--sm">...</svg>
  </button>
  <div class="hh-tooltip">Exportar a CSV</div>
</div>
```

Use for: icon-only buttons, abbreviations, derived metric explanations, truncated cell values.

## Dropdown menu

Action menus that float above their trigger. The CSS gives you the visual; opening/closing is your framework's job.

```html
<div class="hh-menu">
  <div class="hh-menu-label">Acciones</div>
  <button class="hh-menu-item">
    <svg class="hh-icon hh-icon--sm">...</svg>
    Editar registro
  </button>
  <button class="hh-menu-item">
    <svg class="hh-icon hh-icon--sm">...</svg>
    Exportar a CSV
  </button>
  <div class="hh-menu-divider"></div>
  <button class="hh-menu-item danger">
    <svg class="hh-icon hh-icon--sm">...</svg>
    Eliminar registro
  </button>
</div>
```

Position with JS (use Floating UI in JS apps for free viewport-aware positioning).

## Breadcrumbs

For 3+ levels of nesting only.

```html
<nav class="hh-breadcrumb">
  <a href="/operaciones">Operaciones</a>
  <span class="hh-breadcrumb-sep">/</span>
  <a href="/operaciones/tiendas">Tiendas</a>
  <span class="hh-breadcrumb-sep">/</span>
  <span class="hh-breadcrumb-current">R048 · Fashion Drive</span>
</nav>
```

Alternative chevron separator: use `›` instead of `/`.

## Pagination

```html
<div class="hh-pagination">
  <button class="hh-page-btn" disabled>‹</button>
  <button class="hh-page-btn active">1</button>
  <button class="hh-page-btn">2</button>
  <button class="hh-page-btn">3</button>
  <button class="hh-page-btn">4</button>
  <span class="hh-page-ellipsis">…</span>
  <button class="hh-page-btn">12</button>
  <button class="hh-page-btn">›</button>
  <span class="hh-pagination-info">Mostrando 1–25 de 287</span>
</div>
```

Pair with sortable tables on data list screens. Default page size: 25 rows.

## Empty state

Always offer a next action.

```html
<div class="hh-empty">
  <svg class="hh-empty-icon">...</svg>
  <div class="hh-empty-title">Aún sin reglas de comisión</div>
  <div class="hh-empty-msg">Crea tu primera regla para empezar a calcular nómina automáticamente.</div>
  <div class="hh-empty-actions">
    <button class="hh-btn hh-btn--primary">Crear Primera Regla</button>
    <button class="hh-btn hh-btn--secondary">Leer Docs</button>
  </div>
</div>
```

Two common variants:
- **No data yet** — primary CTA to create the first record
- **No filter results** — secondary action to clear filters

## Skeleton loader

Use when the layout shape is known. Spinners only for unknown-shape loads.

```html
<!-- KPI loading state -->
<div class="hh-kpi">
  <div class="hh-skeleton hh-skeleton--text" style="width:60%"></div>
  <div class="hh-skeleton hh-skeleton--kpi"></div>
  <div class="hh-skeleton hh-skeleton--text" style="width:80%;margin:0"></div>
</div>

<!-- Table row loading -->
<tr>
  <td><div class="hh-skeleton hh-skeleton--line" style="width:140px"></div></td>
  <td class="right"><div class="hh-skeleton hh-skeleton--line" style="width:80px;margin-left:auto"></div></td>
</tr>

<!-- Variants: --text, --title, --kpi, --line, --block -->
```

## Skeleton vs spinner

| Use skeletons when | Use spinners when |
|---|---|
| Layout shape is known | Result shape is unknown |
| Loading a table, KPI grid, card | Search query, chat response |
| Initial page load | Action interrupting user (saving, submitting) |
