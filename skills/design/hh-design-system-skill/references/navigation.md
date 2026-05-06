# Navigation

Two-tier navigation: 40px header (brand + global actions) + 36px tab bar (top-level sections). Sub-tabs live inside content areas.

## App header + top nav

```html
<div class="hh-app-header">
  <div class="hh-app-header-left">
    <div style="width:24px;height:24px;background:var(--hh-brand-500);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:10px">HH</div>
    <div>
      <span class="hh-app-header-title">DASHBOARD CEDIS</span>
      <span class="hh-app-header-sub">Onest · Invenova</span>
    </div>
  </div>
  <div style="display:flex;gap:var(--hh-space-4);align-items:center">
    <span class="hh-badge">v2.1</span>
    <button class="hh-btn hh-btn--ghost hh-btn--sm">Gabriel</button>
  </div>
</div>

<div class="hh-app-navbar">
  <button class="hh-app-nav-btn active">Dashboard</button>
  <button class="hh-app-nav-btn">Operaciones</button>
  <button class="hh-app-nav-btn">KPIs</button>
  <button class="hh-app-nav-btn">Reportes</button>
  <button class="hh-app-nav-btn">Admin</button>
</div>
```

## Inline tabs (sub-navigation inside content)

```html
<div class="hh-tabs">
  <button class="hh-tab active">Resumen</button>
  <button class="hh-tab">Tiendas</button>
  <button class="hh-tab">Por Región</button>
  <button class="hh-tab">Por Gerente</button>
</div>
```

## Breadcrumbs (deep hierarchies only)

```html
<nav class="hh-breadcrumb">
  <a href="/operaciones">Operaciones</a>
  <span class="hh-breadcrumb-sep">/</span>
  <a href="/operaciones/tiendas">Tiendas</a>
  <span class="hh-breadcrumb-sep">/</span>
  <span class="hh-breadcrumb-current">R048 · Fashion Drive</span>
</nav>
```

Use breadcrumbs only when nesting is 3+ levels deep. At 2 levels, the page title alone is enough.

## Do

- App title in uppercase with letter-spacing
- Sub-text (subtitle) for context, not decoration
- Active state: brand-300 text + brand-500 underline
- Top nav for orthogonal sections; tabs for views of the same data

## Don't

- Don't use a sidebar for primary nav in dense data apps — it eats horizontal space. Reserve sidebars for the design system gallery or admin tools where the data area is narrow.
- Don't create a third level of nav. If you need 3 levels, the IA is wrong.
