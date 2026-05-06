# Buttons

Four variants × three sizes. One primary action per screen.

## Variants

```html
<button class="hh-btn hh-btn--primary">Acción Principal</button>
<button class="hh-btn hh-btn--secondary">Cancelar</button>
<button class="hh-btn hh-btn--ghost">Más opciones</button>
<button class="hh-btn hh-btn--danger">Eliminar</button>
```

- **primary** — the main action on a screen. Use ONE per visual group.
- **secondary** — cancel, back, or any non-primary action.
- **ghost** — low-priority actions, often inside table rows or menus.
- **danger** — destructive actions only (delete, archive, force-stop).

## Sizes

```html
<button class="hh-btn hh-btn--primary hh-btn--sm">Pequeño</button>
<button class="hh-btn hh-btn--primary">Predeterminado</button>
<button class="hh-btn hh-btn--primary hh-btn--lg">Grande</button>
```

- **sm** (26px) — for dense table rows, toolbars
- **md** (32px, default) — most contexts
- **lg** (40px) — primary CTAs on landing pages, hero areas

## With icons

```html
<button class="hh-btn hh-btn--primary">
  <svg class="hh-icon hh-icon--sm">...</svg>
  Nuevo Registro
</button>
```

Icon-only button:

```html
<button class="hh-btn hh-btn--icon hh-btn--secondary">
  <svg class="hh-icon hh-icon--sm">...</svg>
</button>
```

Always pair an icon-only button with a tooltip (`hh-tooltip-wrap`) to communicate its purpose.

## Disabled

```html
<button class="hh-btn hh-btn--primary" disabled>Guardar</button>
```

## Do

- One primary action per group ("Cancelar" + "Guardar")
- Verb-first labels: "Crear Tienda", "Eliminar Regla", "Exportar CSV"
- Spanish always

## Don't

- Don't stack two primaries side by side
- Don't use "OK" — use the actual verb ("Eliminar", "Guardar", "Confirmar")
- Don't use rounded corners or custom shadows
