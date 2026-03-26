# SHADCN UI SYSTEM - LARAVEL BLADE CONTEXT

## 1. IDENTITY & CORE PRINCIPLES
- **Style:** Enterprise SaaS, Minimalist, High-density.
- **Palette:** Tailwind `Zinc` palette ONLY.
- **Surface:** Main background `zinc-50`, Card/Component background `white`.
- **Contrast:** High-contrast text (`zinc-950`) on subtle borders (`zinc-200`).

## 2. TYPOGRAPHIC SCALE
- **Page Titles:** `text-3xl font-bold tracking-tighter text-zinc-900`.
- **Section Headers:** `text-lg font-semibold tracking-tight`.
- **Table Headers:** `text-[10px] font-bold uppercase tracking-[0.2em] text-zinc-400`.
- **Data Text:** `text-sm font-medium text-zinc-600`.
- **Labels:** `text-[11px] font-black uppercase tracking-widest text-zinc-500`.

## 3. COMPONENT BLUEPRINTS (Tailwind Classes)

### Layout & Containers
- **Main Card:** `bg-white border border-zinc-200 rounded-xl shadow-sm overflow-hidden`.
- **Divider:** `border-t border-zinc-100`.
- **Inner Padding:** `p-4` (Standard) or `p-6` (Spacious).

### Navigation & Interactive
- **Breadcrumb (Arrow):** Use `clip-path: polygon(...)` for chevron-style nesting.
- **Primary Button:** `h-9 px-4 bg-zinc-900 text-zinc-50 rounded-md text-xs font-bold uppercase tracking-widest hover:bg-black transition-all`.
- **Secondary/Ghost:** `h-8 w-8 flex items-center justify-center rounded text-zinc-400 hover:text-zinc-900 hover:bg-zinc-100 transition-all`.
- **Sidebar Item:** `flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md text-zinc-400 hover:bg-zinc-800/50 hover:text-white`.

### Data Display
- **Table Row:** `group hover:bg-zinc-50/50 transition-colors`.
- **Status Badge (Positive):** `bg-emerald-50 border border-emerald-100 text-emerald-700 text-[10px] font-bold uppercase`.
- **Status Badge (Neutral):** `bg-zinc-50 border border-zinc-200 text-zinc-400 text-[10px] font-bold uppercase`.
- **Empty State:** `border-2 border-dashed border-zinc-100 rounded-xl py-20 flex flex-col items-center`.

## 4. FORM ELEMENTS
- **Inputs/Selects:** `h-10 rounded-md border-zinc-200 bg-white px-3 text-sm placeholder:text-zinc-400 focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 transition-all`.
- **Search Bar:** `relative` with icon at `absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400`.

## 5. REFINEMENT RULES
- NO `rounded-3xl` or `rounded-full` (except for profile avatars).
- NO `shadow-lg` or `shadow-xl`.
- NO bright blue/purple gradients. Use solid colors or subtle `zinc` tints.
- ALWAYS use `iconify-icon` (Lucide set) for iconography.