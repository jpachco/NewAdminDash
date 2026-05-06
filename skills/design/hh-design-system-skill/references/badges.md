# Badges & Status

Compact status indicators in five semantic flavors. Pair with status dots for inline emphasis.

## Variants

```html
<span class="hh-badge">Default</span>
<span class="hh-badge hh-badge--success">Activo</span>
<span class="hh-badge hh-badge--warning">Pendiente</span>
<span class="hh-badge hh-badge--alert">Revisión</span>
<span class="hh-badge hh-badge--danger">Falló</span>
<span class="hh-badge hh-badge--info">Info</span>
```

## With status dots

```html
<span class="hh-badge hh-badge--success">
  <span class="hh-dot hh-dot--success"></span>En meta
</span>
<span class="hh-badge hh-badge--warning">
  <span class="hh-dot hh-dot--warning"></span>En riesgo
</span>
<span class="hh-badge hh-badge--danger">
  <span class="hh-dot hh-dot--danger"></span>Atrasado
</span>
```

## Standalone dots (for tables)

When the table is dense and a full badge is too noisy, use a dot alone:

```html
<span class="hh-dot hh-dot--success"></span>
<span class="hh-dot hh-dot--warning"></span>
<span class="hh-dot hh-dot--danger"></span>
<span class="hh-dot hh-dot--neutral"></span>
```

## Semantic mapping

- **success / verde** — healthy, active, on-target, completed
- **warning / amarillo** — needs attention, at-risk, pending
- **alert / naranja** — escalation, review required (between warning and danger)
- **danger / rojo** — failed, behind, error, critical
- **info / azul** — informational, neutral context

## Do

- Use color semantically: green for healthy, red for problems
- Keep labels to 1–2 words ("En meta", "Pendiente", "Falló")
- Spanish always

## Don't

- **Don't use status colors for non-status taxonomies.** A "cluster" or "category" isn't success/danger — use the default neutral badge for those.
  - Wrong: `<span class="hh-badge hh-badge--success">Premium</span>` for a cluster type
  - Right: `<span class="hh-badge">Premium</span>`
- Don't combine more than one status badge per row
