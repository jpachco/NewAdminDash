# Modals & Alerts

Modals carry shadow `--hh-elev-5` and a backdrop blur. Inline alerts use the four semantic backgrounds.

## Inline alerts

```html
<div class="hh-alert-box hh-alert-box--success">
  <div>
    <div class="hh-alert-title">Guardado correctamente</div>
    <div class="hh-alert-msg">Tus cambios fueron escritos en la base de datos.</div>
  </div>
</div>

<div class="hh-alert-box hh-alert-box--warning">
  <div>
    <div class="hh-alert-title">Pendiente de revisión</div>
    <div class="hh-alert-msg">3 registros requieren aprobación manual antes de procesarse.</div>
  </div>
</div>

<div class="hh-alert-box hh-alert-box--danger">
  <div>
    <div class="hh-alert-title">Conexión fallida</div>
    <div class="hh-alert-msg">No se pudo alcanzar el endpoint de SAP. Reintentando en 30s.</div>
  </div>
</div>

<div class="hh-alert-box hh-alert-box--info">
  <div>
    <div class="hh-alert-title">¿Sabías que?</div>
    <div class="hh-alert-msg">Puedes presionar ⌘K para abrir la paleta de comandos.</div>
  </div>
</div>
```

## Modal dialog

```html
<div class="hh-modal-overlay">
  <div class="hh-modal">
    <div class="hh-modal-header">
      <div class="hh-modal-title">Confirmar eliminación</div>
      <button class="hh-btn hh-btn--ghost hh-btn--icon hh-btn--sm">✕</button>
    </div>
    <div class="hh-modal-body">
      Estás a punto de eliminar permanentemente la regla de comisión para
      <strong style="color:var(--hh-text-1)">R048 · Fashion Drive</strong>.
      Esta acción no se puede deshacer.
    </div>
    <div class="hh-modal-footer">
      <button class="hh-btn hh-btn--secondary">Cancelar</button>
      <button class="hh-btn hh-btn--danger">Eliminar</button>
    </div>
  </div>
</div>
```

## Confirmation message rules

Be specific. Always include:
1. **What** is being affected (name the exact entity)
2. **What happens** as a result
3. **Whether it can be undone**

The action button uses the **verb**, not "OK" or "Yes":
- "Eliminar regla" not "OK"
- "Publicar cambios" not "Sí"
- "Cancelar pedido" not "Confirmar"

## Do

- Name the exact entity affected ("R048 · Fashion Drive", not "this record")
- State the consequence ("no se puede deshacer")
- Use the verb in the action button ("Eliminar")
- Match the alert color to the meaning, not to the importance

## Don't

- Don't write generic "¿Estás seguro?" messages — they waste the modal
- Don't use modals for non-critical confirmations (a toast or inline alert is enough)
- Don't put long forms in modals — use a full page or drawer
