# Design System Master File — Portal Karyawan Terpadu PT MSN

> **LOGIC:** When building a specific page, first check `design-system/portal-karyawan-pt-msn/pages/[page-name].md`.
> If that file exists, its rules **override** this Master file.
> If not, strictly follow the rules below (aligned with **PRD v2.0 §11** + **Tactile Light** craftsmanship).

---

**Project:** Portal Karyawan Terpadu PT MSN  
**Generated:** 2026-11-04  
**Style Direction:** Minimalism & Swiss Enterprise + Tactile Light Craftsmanship

---

## Global Rules (PRD v2.0 §11)

### Color Palette

| Role | Hex | CSS Variable | Usage |
|------|-----|--------------|-------|
| Primary | `#1E40AF` | `--color-primary` | Main brand actions, active navigation, headers |
| Secondary | `#3B82F6` | `--color-secondary` | Interactive highlights, secondary actions, links |
| Accent / CTA | `#16A34A` | `--color-accent` | Clock-in CTA, approval actions, positive status |
| Background | `#EFF6FF` | `--color-background` | Main app canvas background |
| Card | `#FFFFFF` | `--color-card` | Elevated surfaces, panels, modals, tables |
| Foreground | `#1E3A8A` | `--color-foreground` | Primary headings and high-emphasis text |
| Muted | `#475569` | `--color-muted` | Body secondary text, captions, table labels (≥4.5:1 contrast) |
| Border | `#BFDBFE` | `--color-border` | Hairline card borders, dividers, form inputs |
| Danger | `#DC2626` | `--color-danger` | SLA breach (`LEBIH`/OVERDUE), rejection, destructive actions |
| Warning | `#D97706` | `--color-warning` | Ticket `PROSES`/`PENDING_VERIFIKASI`, interval warning |

### Mobile Ticket Status Stripes (§11)
- **Merah (`#DC2626`)**: SLA `LEBIH` / `OVERDUE`
- **Biru (`#3B82F6`)**: `OPEN`
- **Kuning (`#EAB308`)**: `PROSES` / `PENDING_VERIFIKASI`
- **Hijau (`#16A34A`)**: `CLOSE`
- **Abu-abu (`#64748B`)**: `CANCELLED`

### Typography (§11)
- **Heading Font:** `Outfit` (600/700, tight tracking `-0.02em`, self-hosted)
- **Body Font:** `Work Sans` (400/500/600, base 16px, line-height 1.5, self-hosted)
- **Monospace Font:** `JetBrains Mono` / `ui-monospace` (for ticket numbers `BDG-YYYYMMDD-XXX`, timestamps, coordinates, and currency)

### Touch, Accessibility & Motion (§10.3, §11)
- Minimum touch target: **44 × 44 px** (`min-h-[44px] min-w-[44px]`)
- Minimum contrast ratio: **4.5:1**
- Status indicators **never rely on color alone** — always pair with an SVG icon + descriptive text label
- Transitions: **150–250 ms** (`cubic-bezier(0.22, 1, 0.36, 1)`), respecting `prefers-reduced-motion`
- Buttons never shift layout on hover (no translate/scale on hover; use light/glow/border/brightness changes only)
