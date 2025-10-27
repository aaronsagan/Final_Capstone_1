# Updates Page - Visual Design Guide 🎨

## Layout Overview

```
┌─────────────────────────────────────────────────────────────────────────┐
│                                                                         │
│  ┌──────────┐          ┌──────────────────┐          ┌──────────┐     │
│  │          │          │                  │          │          │     │
│  │   LEFT   │          │      CENTER      │          │  RIGHT   │     │
│  │  PANEL   │          │       FEED       │          │  PANEL   │     │
│  │  (260px) │          │    (max 680px)   │          │  (280px) │     │
│  │          │          │                  │          │          │     │
│  │  STICKY  │          │   SCROLLABLE ↕   │          │  STICKY  │     │
│  │          │          │                  │          │          │     │
│  │          │          │                  │          │          │     │
│  └──────────┘          └──────────────────┘          └──────────┘     │
│                                                                         │
│                                    ┌───┐                               │
│                                    │ + │  ← Floating Action Button     │
│                                    └───┘                               │
└─────────────────────────────────────────────────────────────────────────┘
```

## Left Panel Structure

```
┌─────────────────────────┐
│   ┌─────────────┐       │
│   │   AVATAR    │       │  ← Large charity logo (24x24)
│   │   (24x24)   │       │
│   └─────────────┘       │
│                         │
│   Charity Name          │  ← Bold, xl text
│   Short mission...      │  ← Muted, sm text
│                         │
├─────────────────────────┤
│                         │
│  📝  Updates      123   │  ← Icon + Label + Count
│  ❤️   Likes       1.2k  │
│  💬  Comments     560   │
│  📍  Location     City  │
│                         │
├─────────────────────────┤
│                         │
│  → About                │  ← Action links
│  ✉  Contact             │
│                         │
└─────────────────────────┘
```

## Center Feed - Post Card

```
┌──────────────────────────────────────────────┐
│  ┌─┐  Charity Name          Pinned    ⋮     │  ← Header
│  │ │  9m ago                                 │
│  └─┘                                         │
│                                              │
│  Lorem ipsum dolor sit amet, consectetur     │  ← Content
│  adipiscing elit, sed do eiusmod tempor...  │
│                                              │
│  ┌──────────────┐  ┌──────────────┐         │  ← Media Grid
│  │    IMAGE     │  │    IMAGE     │         │
│  │              │  │              │         │
│  └──────────────┘  └──────────────┘         │
│                                              │
│  ❤️ 45 likes  💬 12 comments                 │  ← Engagement
│                                              │
├──────────────────────────────────────────────┤
│                                              │
│     ❤️ Like          💬 Comment              │  ← Actions
│                                              │
├──────────────────────────────────────────────┤
│  [Comments Section - Expandable]            │
│                                              │
│  ┌─┐  User Name                             │
│  │U│  Comment text here...                  │
│  └─┘  2h ago  Delete                        │
│                                              │
│  ┌─┐  ┌──────────────────────┐  ┌──┐       │
│  │C│  │ Write a comment...   │  │→ │       │
│  └─┘  └──────────────────────┘  └──┘       │
│                                              │
└──────────────────────────────────────────────┘
```

## Right Panel Structure

```
┌─────────────────────────┐
│  Insights          ⌃    │  ← Collapsible header
├─────────────────────────┤
│                         │
│  📈 This Month          │
│                         │
│  Total Likes      1.2k  │
│  Comments          560  │
│  Posts             123  │
│                         │
├─────────────────────────┤
│                         │
│  💬 Latest Activity     │
│                         │
│  Your supporters are    │
│  engaging with your     │
│  updates!               │
│                         │
├─────────────────────────┤
│                         │
│  👥 Community           │
│                         │
│  Building connections   │
│  with supporters...     │
│                         │
└─────────────────────────┘
```

## Create Post Modal

```
┌────────────────────────────────────────────┐
│  Create Update                             │
├────────────────────────────────────────────┤
│                                            │
│  ┌─┐  Charity Name                        │
│  │C│  Sharing with your supporters        │
│  └─┘                                       │
│                                            │
│  ┌────────────────────────────────────┐   │
│  │                                    │   │
│  │  Share an update with your         │   │
│  │  supporters...                     │   │
│  │                                    │   │
│  │                                    │   │
│  └────────────────────────────────────┘   │
│                                            │
│  ┌──────────┐  ┌──────────┐              │
│  │  IMAGE   │  │  IMAGE   │              │
│  │  PREVIEW │  │  PREVIEW │              │
│  └──────────┘  └──────────┘              │
│                                            │
├────────────────────────────────────────────┤
│  📷 Add Photos (2/4)    [Post Update]     │
└────────────────────────────────────────────┘
```

## Color Palette

### Light Mode
```
Background:       #ffffff
Panel BG:         #f8f9fb
Text:             #0a0a0a
Muted Text:       #6b7280
Primary:          #f4a300
Border:           rgba(0,0,0,0.1)
Card:             #ffffff
```

### Dark Mode
```
Background:       #0a0f24
Panel BG:         #0e1a32
Text:             #e6e6e6
Muted Text:       #9ca3af
Primary:          #f4a300
Border:           rgba(255,255,255,0.1)
Card:             rgba(255,255,255,0.05)
```

## Spacing System

```
Gap Sizes:
- gap-2  → 8px   (tight elements)
- gap-3  → 12px  (default spacing)
- gap-4  → 16px  (comfortable spacing)
- gap-6  → 24px  (section spacing)

Padding:
- p-3    → 12px  (compact cards)
- p-4    → 16px  (default cards)
- p-5    → 20px  (spacious cards)
- p-6    → 24px  (panel padding)

Margins:
- mb-4   → 16px  (post spacing)
- mt-8   → 32px  (section spacing)
```

## Typography Scale

```
Headings:
- 3xl    → 30px  (Page title)
- xl     → 20px  (Card titles)
- lg     → 18px  (Section headers)

Body:
- base   → 16px  (Default)
- sm     → 14px  (Secondary text)
- xs     → 12px  (Metadata)

Special:
- [15px] → Post content (custom size)
```

## Border Radius

```
- rounded-lg     → 8px   (Cards)
- rounded-xl     → 12px  (Media)
- rounded-2xl    → 16px  (Comments)
- rounded-full   → 9999px (Buttons, inputs)
```

## Shadow Levels

```
- shadow-sm      → Subtle elevation
- shadow-md      → Default cards
- shadow-lg      → Hover state
- shadow-xl      → Modals
- shadow-2xl     → Floating button
```

## Responsive Breakpoints

```
Mobile:   < 1024px  (lg)
  - Single column
  - Hide side panels
  - Full-width feed

Tablet:   1024px - 1280px
  - 2 columns
  - Left panel visible
  - Right panel collapsible

Desktop:  > 1280px
  - 3 columns
  - All panels visible
  - Optimal layout
```

## Animation Timing

```
Fast:     100-200ms  (Hover effects)
Normal:   200-300ms  (Transitions)
Slow:     300-500ms  (Modal open/close)

Easing:   ease-in-out (default)
```

## Interactive States

### Buttons
```
Default:  bg-primary
Hover:    bg-primary/90 + scale-105
Active:   bg-primary/80
Disabled: opacity-50 + cursor-not-allowed
```

### Cards
```
Default:  border-border/40
Hover:    shadow-lg + border-border/60
Active:   (no change)
```

### Inputs
```
Default:  border-border/60
Focus:    ring-2 ring-primary + border-transparent
Error:    border-destructive
```

## Icon Sizes

```
Small:    h-4 w-4  (16px)  - Inline icons
Medium:   h-5 w-5  (20px)  - Button icons
Large:    h-6 w-6  (24px)  - Feature icons
XLarge:   h-8 w-8  (32px)  - Empty states
```

## Z-Index Layers

```
Base:         z-0   (Content)
Sticky:       z-10  (Side panels)
Dropdown:     z-20  (Menus)
Modal:        z-30  (Dialogs)
Floating:     z-50  (FAB button)
```

## Accessibility Features

- ✅ Proper contrast ratios (WCAG AA)
- ✅ Keyboard navigation support
- ✅ Focus visible states
- ✅ Screen reader friendly
- ✅ Touch-friendly targets (44px min)
- ✅ Reduced motion support (via Tailwind)

## Performance Optimizations

- ✅ Lazy load comments
- ✅ Optimistic UI updates
- ✅ Efficient re-renders (React.memo potential)
- ✅ CSS transitions (GPU accelerated)
- ✅ Image lazy loading
- ✅ Virtualization ready (for large feeds)

---

**Design System**: Based on shadcn/ui + Tailwind CSS
**Icons**: Lucide React
**Fonts**: System font stack (optimal performance)
