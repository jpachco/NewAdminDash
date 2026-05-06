---
name: hh-design-system
description: Build internal Haber Holding (HH) applications using the official HH Design System v1.0 — dashboards, KPI views, data tables, forms, admin panels, and operational consoles. Use this skill whenever the user mentions Haber Holding, HH, an HH app or project (CEDIS Dashboard, Compensaciones, Procurement Assistant, Pre-ERP Diagnostic, Knowledge Base, VTEX brands, or any internal HH tool), or asks for any internal dashboard, KPI view, data table, admin panel, or business app — even if they don't explicitly mention "design system." This skill provides the complete token system, component library, charting theme, and copy-paste patterns to ensure every HH app looks and feels like part of the same product family. Always activate this skill when designing or coding any UI for HH, regardless of how casually the request is phrased.
---

# Haber Holding Design System v1.0

This skill provides everything needed to build internal applications for Haber Holding (HH) that conform to the official HH Design System. Every UI you produce for HH should use the tokens and components from this skill — no exceptions, no one-offs.

## When you've activated this skill, do this FIRST

Before writing any HTML, CSS, or component code, **read these two files into context**:

1. **`assets/tokens.css`** — the complete `:root` block with all HH design tokens (colors, typography, spacing, elevation, motion). Every value in your output must reference one of these tokens via `var(--hh-*)`. Never hardcode hex colors, pixel values, or font sizes.

2. **`assets/components.css`** — the complete component layer (`.hh-btn`, `.hh-table`, `.hh-kpi`, etc.). Always use these classes; do not invent new ones.

For specific component questions or when you need a copy-paste pattern, also read the relevant file in `references/` (one file per component category).

## Core principles (memorize these)

The HH design system is built on four principles that you must respect in every output:

1. **Information density.** HH apps serve operators and analysts who need to see a lot at once. Optimize for information per square inch. Tight padding, compact type, minimal chrome. Whitespace is not a virtue here.

2. **Quiet, then loud.** The interface is mostly grayscale. Color is reserved for meaning: brand blue for action, green/yellow/orange/red for status. Never decorate with color.

3. **Sharp & geometric.** No rounded corners. No drop shadows on every card. Closer to Bloomberg Terminal than to consumer SaaS — precise, technical, unsentimental.

4. **Consistent across HH.** A user moving between Compensaciones, the CEDIS Dashboard, and the Procurement Assistant should feel like they're inside one product. Same nav, same buttons, same colors, same spacing.

## Language

**All user-facing text in HH apps is written in Spanish (Mexican Spanish, "es-MX").** Labels, buttons, headings, error messages, empty states, modals — all Spanish. Code identifiers (CSS classes, JavaScript variables, file names) stay in English. Currency formatting uses MXN.

If the user gives you a feature description in English, translate the labels to Spanish in your output unless they explicitly ask otherwise.

## Required tech stack

When building HH apps, default to this stack unless the user specifies otherwise:

- **HTML/CSS:** Vanilla HTML with `--hh-*` CSS variables and `.hh-*` classes from this skill
- **Charts:** Chart.js 4 (use the theme object in `assets/chart-theme.js` — copy it verbatim)
- **Icons:** Lucide (inline SVG, sized via `.hh-icon` / `.hh-icon--sm` / `.hh-icon--lg`)
- **Fonts:** Inter (300, 400, 500, 600 weights only) + JetBrains Mono for code/numbers
- **Framework (if requested):** Next.js + React + Supabase + Vercel (the HH standard stack used in THE MANAGER)
- **Tables/forms:** Use the patterns in `examples/data-list.html` and `examples/form.html` as starting templates

## How to build a new HH screen

Follow this sequence every time:

1. **Identify the screen archetype.** Most HH screens fall into one of three patterns:
   - **Dashboard** — header, KPI strip, chart row(s), summary table → use `examples/dashboard.html`
   - **Data list** — filter bar, sortable table, pagination → use `examples/data-list.html`
   - **Form (create/edit)** — section headers, two-column inputs, sticky footer → use `examples/form.html`

   If the screen is a hybrid, compose them. If it's none of the above, ask the user before inventing a new pattern — the answer is usually "use one of the three."

2. **Start from the matching example file.** Copy the example and replace the demo data with the user's domain. Keep the structure, spacing, and class names identical.

3. **Use only the components in `references/`.** If you find yourself wanting a component that doesn't exist (a stepper, a date range picker, a kanban column), tell the user it's not in v1 of the system and either propose adding it via the contributing process or use the closest existing pattern.

4. **Validate before delivering:**
   - Every color is a `var(--hh-*)` token, not a hex.
   - Every spacing value is a `var(--hh-space-*)` token.
   - Every text size is a `var(--hh-fs-*)` token.
   - Every chart canvas is wrapped in a `<div style="position:relative;height:NNNpx">` (the chart container fix — see below).
   - All UI labels are in Spanish.

## Critical bug to avoid: Chart.js infinite growth

When using Chart.js with `responsive: true` and `maintainAspectRatio: false`, you **must** wrap the canvas in a parent element with an explicit pixel height. Otherwise the chart will grow infinitely on every render frame.

```html
<!-- ✗ WRONG — chart will grow infinitely -->
<div class="hh-chart-box">
  <canvas id="my-chart" height="200"></canvas>
</div>

<!-- ✓ CORRECT — wrap in fixed-height container -->
<div class="hh-chart-box">
  <div class="hh-chart-title">Ventas por Tienda</div>
  <div style="position:relative;height:220px">
    <canvas id="my-chart"></canvas>
  </div>
</div>
```

Recommended container heights: **220px** for primary charts, **160px** for trend lines, **140px** for sparklines / mini charts inside dashboards.

## Component reference (load on demand)

Each file in `references/` covers one component category with HTML examples, do/don't guidance, and prop notes. Read only the file(s) relevant to the current task — don't load them all upfront.

- `references/buttons.md` — Primary, secondary, ghost, danger; sm/md/lg sizes; with icons
- `references/forms.md` — Inputs, selects, textareas, checkboxes, radios, validation states
- `references/tables.md` — Sortable headers, tabular numerals, status badges in cells, total rows
- `references/kpis.md` — KPI grids, value formatting, trend indicators, sub-text patterns
- `references/badges.md` — Five semantic variants, status dots, when to use which
- `references/navigation.md` — App header, top nav, inline tabs, sub-tabs
- `references/modals.md` — Modal dialogs, inline alerts, confirmation patterns
- `references/charts.md` — Chart.js theme, color sequence, container sizing, library choices
- `references/feedback.md` — Tooltips, dropdown menus, breadcrumbs, pagination, empty states, skeleton loaders

## Patterns (full screen examples)

The `examples/` folder contains complete, working screens you can copy and adapt:

- `examples/dashboard.html` — Full operational dashboard (header → KPIs → charts → table)
- `examples/data-list.html` — Filter bar + sortable table + pagination
- `examples/form.html` — Two-column create/edit form with sections and sticky footer

## Things you MUST NOT do

- **Never invent local CSS classes** for things that already exist in the system. If you need a button, use `.hh-btn`, not your own `.my-button`.
- **Never hardcode colors, spacing, or font sizes.** Always use `var(--hh-*)` tokens.
- **Never use rounded corners** (`border-radius` other than 0) unless using `--hh-radius-pill` for a true pill/circle shape.
- **Never add drop shadows to static UI.** Shadows are reserved for floating elements (modals, dropdowns, tooltips) and use the `--hh-elev-*` tokens.
- **Never use emojis as icons.** Use Lucide SVG icons.
- **Never use English for user-facing labels** in HH apps. The system is Spanish-first.
- **Never stack multiple primary buttons** in the same group. One primary action per screen/section.
- **Never use status colors (green/yellow/red) for non-status taxonomies** like clusters or categories. Use the default neutral badge.
- **Never produce a "compact" or "modern" or "friendly" variant** of the system on a whim. The look is fixed; consistency is the point.

## Things you SHOULD do

- **Translate domain terms naturally:** "store" → "tienda", "sales" → "ventas", "budget" → "presupuesto", "achievement" → "alcance", "manager" → "gerente", "seller" → "vendedor", "rule" → "regla".
- **Use Mexican peso formatting** for currency: `new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(value)`
- **Use tabular numerals** for any column or KPI containing numbers: `font-variant-numeric: tabular-nums` (already on `.hh-table .num` and `.hh-kpi-value`).
- **Format large numbers compactly** in KPIs: `$3.99M` instead of `$3,994,231.50`. Keep precision for table cells where exact values matter.
- **Always include context with a KPI:** a delta vs. previous period, a target, or a comparison. Never ship a context-free number.
- **Use empty states** when a list might be empty — copy the pattern from `references/feedback.md`. Always offer a next action.
- **Use skeleton loaders** (not spinners) when the layout shape is known ahead of time. Spinners only for unknown-shape loads.

## Quick reference: the most-used tokens

When you're moving fast, these are the tokens you'll reach for constantly:

```css
/* Backgrounds */
var(--hh-bg)         /* page background */
var(--hh-surface-1)  /* cards, panels */
var(--hh-surface-2)  /* table headers, raised */

/* Text */
var(--hh-text-1)     /* primary white */
var(--hh-text-2)     /* secondary gray */
var(--hh-text-3)     /* tertiary / labels */

/* Brand */
var(--hh-brand-500)  /* primary action color */
var(--hh-brand-300)  /* accent text on dark */

/* Status */
var(--hh-success)    /* #44aa99 */
var(--hh-warning)    /* #ddaa44 */
var(--hh-danger)     /* #dd5555 */

/* Spacing (4px base) */
var(--hh-space-4)    /* 8px  — most common */
var(--hh-space-6)    /* 12px — card padding */
var(--hh-space-8)    /* 16px — section gaps */

/* Type */
var(--hh-fs-body)    /* 13px */
var(--hh-fs-data)    /* 12px — table cells */
var(--hh-fs-label)   /* 10px — uppercase labels */
var(--hh-fs-kpi)     /* 22px — KPI values */
```

## When in doubt

Default to matching the **Compensaciones** app aesthetic (the original reference for this system). If you're unsure whether something belongs, ask: *"Would this look at home in Compensaciones?"* If no, don't add it.

If the user asks for something the system can't easily express, propose two options: (a) the closest existing pattern, or (b) adding a new component to v1.1 via the contributing process. Don't silently invent a new pattern.
