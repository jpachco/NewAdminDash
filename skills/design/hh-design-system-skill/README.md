# HH Design System Skill

A Claude Skill that teaches Claude to build internal Haber Holding applications using the official HH Design System v1.0.

## What it does

When you ask Claude to build any internal HH app — a dashboard, data list, form, admin panel, or operational console — this skill activates automatically and ensures the output uses HH's design tokens, components, patterns, and Spanish-language UI copy. No more pasting the design system into every conversation.

## How to install

### Option 1 — Personal skill in claude.ai

1. Download the `hh-design-system.skill` file
2. In Claude.ai, go to **Settings → Capabilities → Skills**
3. Click **Upload skill** and select the file
4. The skill is now active in all your chats

### Option 2 — Claude Code (for developers)

Clone or copy this folder into one of these locations:

```bash
# User-level (available in all your projects)
mkdir -p ~/.claude/skills
cp -r hh-design-system ~/.claude/skills/

# OR project-level (only in this repo)
mkdir -p .claude/skills
cp -r hh-design-system .claude/skills/
```

Claude Code will detect the skill automatically the next time it runs in that directory.

## How to use it

Just ask Claude to build something for HH. Examples:

- *"Hazme un dashboard de inventario para CEDIS"*
- *"Necesito un formulario para crear una nueva regla de comisión"*
- *"Crea una tabla de procurement con filtros por proveedor"*
- *"Construye una pantalla de admin para gestionar usuarios de Compensaciones"*

The skill activates on its own when it detects HH context. You don't need to mention "design system" explicitly.

## What's inside

```
hh-design-system/
├── SKILL.md                    Main instructions for Claude
├── assets/
│   ├── tokens.css              The :root token block (source of truth)
│   ├── components.css          All .hh-* component classes
│   └── chart-theme.js          Chart.js theme matching HH
├── references/                 Component-specific docs (loaded on demand)
│   ├── buttons.md
│   ├── forms.md
│   ├── tables.md
│   ├── kpis.md
│   ├── badges.md
│   ├── navigation.md
│   ├── modals.md
│   ├── charts.md
│   ├── feedback.md             Tooltips, dropdowns, breadcrumbs, pagination, empty states, skeletons
│   └── full-gallery.html       Complete interactive gallery for reference
└── examples/                   Copy-paste starting templates
    ├── dashboard.html
    ├── data-list.html
    └── form.html
```

## Updating the skill

To update tokens, components, or examples:

1. Edit the relevant file in this folder
2. If installed as a personal skill in claude.ai, repackage and re-upload via Settings → Skills
3. If installed via Claude Code, just save the file — changes take effect on the next session

## Versioning

- **v1.0** (Apr 2026) — Initial release. Dark theme, 9 component categories, 3 patterns.
- **v1.1** (planned) — Light theme tokens, additional components based on real-app feedback.
- **v2.0** (planned) — React component library (`@hh/ui`), Tailwind preset.

## Owner

Equipo de Transformación · Haber Holding
Contact: Gabriel Gitlin Morgenstern
