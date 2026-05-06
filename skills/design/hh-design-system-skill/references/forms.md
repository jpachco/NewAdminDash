# Forms & Inputs

Compact, dark inputs with sharp borders. Labels are uppercase microcopy ABOVE each field — never inline.

## Text input

```html
<div class="hh-form-group">
  <label class="hh-label">Código de Tienda</label>
  <input class="hh-input" placeholder="ej. R048" />
  <div class="hh-help">Identificador de tienda de 3–5 caracteres</div>
</div>
```

## Error state

```html
<div class="hh-form-group">
  <label class="hh-label">Email</label>
  <input class="hh-input hh-input--error" value="not-an-email" />
  <div class="hh-help hh-help--error">Por favor ingresa un email válido</div>
</div>
```

## Select

```html
<div class="hh-form-group">
  <label class="hh-label">Cluster</label>
  <select class="hh-select">
    <option>Premium</option>
    <option>Standard</option>
    <option>Express</option>
  </select>
</div>
```

## Textarea

```html
<div class="hh-form-group">
  <label class="hh-label">Notas</label>
  <textarea class="hh-textarea" placeholder="Comentarios opcionales..."></textarea>
</div>
```

## Checkbox & radio

```html
<label class="hh-checkbox">
  <input type="checkbox" checked> Activo
</label>

<label class="hh-radio">
  <input type="radio" name="periodo" checked> Mensual
</label>
<label class="hh-radio">
  <input type="radio" name="periodo"> Trimestral
</label>
```

## Two-column form layout

For create/edit screens, group fields in a 2-column grid with section headers:

```html
<div style="font-size:var(--hh-fs-label);color:var(--hh-text-3);text-transform:uppercase;letter-spacing:0.05em;margin:var(--hh-space-12) 0 var(--hh-space-6);padding-bottom:var(--hh-space-3);border-bottom:1px solid var(--hh-border-1)">
  Identificación
</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--hh-space-12)">
  <div class="hh-form-group">...</div>
  <div class="hh-form-group">...</div>
</div>
```

See `examples/form.html` for a complete pattern.

## Do

- Label above the field, microcopy below for guidance
- Placeholder = example value, not instructions
- Group related fields under section headers
- Labels in Spanish, uppercase, with letter-spacing

## Don't

- Don't use inline labels with colons ("Nombre:")
- Don't write long sentence-case placeholders ("Por favor ingresa el nombre completo de la tienda aquí")
- Don't use floating labels — they're a different aesthetic
