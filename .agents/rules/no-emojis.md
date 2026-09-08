# Rule: No Emojis in Code or User Interfaces

## Absolute Constraint
- **Never use Unicode emojis** (e.g., 🏷️, ✏️, 🏢, 🧪, ⚠️, ⏳, ✓, ☀️, 🌙, ✕, 📄, 📈, etc.) in HTML, Blade templates, Vue/React components, stylesheets, UI labels, buttons, or console outputs.
- Always use vector SVG icons (e.g., `<x-ui.icon name="tag" />`, `<x-ui.icon name="pencil" />`, `<x-ui.icon name="trash" />`, `<x-ui.icon name="sun" />`), dedicated icon libraries (Lucide / Heroicons), or clean, semantic text badges.
- When indicating status (e.g., overdue, warning, success), use design system color tokens, borders, and `<x-ui.badge variant="...">` components instead of emoji symbols.
