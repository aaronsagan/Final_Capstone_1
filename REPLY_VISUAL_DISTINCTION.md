# Reply Visual Distinction - Facebook Style

## Overview
Replies now have a distinct visual appearance to differentiate them from regular comments, just like Facebook.

## Visual Differences

### Regular Comment
```
[Avatar 9x9]  ┌─────────────────────────────┐
              │ Name (text-sm, bold)        │
              │ Comment text (text-[15px])  │
              └─────────────────────────────┘
              12m ago  ❤ 5  💬 Reply
```

### Reply (starts with @)
```
              [Avatar 8x8]  ┌──────────────────────┐
                            │ Name (text-xs, bold) │
                            │ @User Reply text     │
                            │ (text-sm)            │
                            └──────────────────────┘
                            5m ago  ❤ 1  💬 Reply
              ↑
           Indented 48px (ml-12)
```

## Key Differences

### 1. Indentation
- **Regular Comment**: No indentation
- **Reply**: Indented 48px (`ml-12`) to the right

### 2. Avatar Size
- **Regular Comment**: 36px × 36px (`h-9 w-9`)
- **Reply**: 32px × 32px (`h-8 w-8`)

### 3. Bubble Size
- **Regular Comment**: 
  - Padding: `px-3.5 py-2`
  - Rounded: `rounded-2xl`
  - Background: `bg-muted/40`
- **Reply**:
  - Padding: `px-3 py-1.5` (more compact)
  - Rounded: `rounded-xl` (less rounded)
  - Background: `bg-muted/30` (lighter)

### 4. Text Size
- **Regular Comment**:
  - Name: `text-sm` (14px)
  - Content: `text-[15px]` (15px)
- **Reply**:
  - Name: `text-xs` (12px)
  - Content: `text-sm` (14px)

## Detection Logic

```typescript
const isReply = comment.content.startsWith('@');
```

Any comment starting with `@` is automatically styled as a reply.

## Visual Example

### Before (All Look the Same)
```
┌─────────────────────────────────┐
│ Hugs and Kisses                 │
│ Want to make a difference?...   │
└─────────────────────────────────┘
27m ago  ❤ 1  💬 Reply

┌─────────────────────────────────┐
│ Hugs and Kisses                 │
│ @Hugs and Kisses At Hugs and... │
└─────────────────────────────────┘
5m ago  ❤ 1  💬 Reply
```

### After (Clear Visual Hierarchy)
```
┌─────────────────────────────────┐
│ Hugs and Kisses                 │
│ Want to make a difference?...   │
└─────────────────────────────────┘
27m ago  ❤ 1  💬 Reply

        ┌──────────────────────────┐
        │ Hugs and Kisses          │
        │ @Hugs and Kisses At...   │
        └──────────────────────────┘
        5m ago  ❤ 1  💬 Reply
        ↑ Indented & Smaller
```

## Implementation Details

### Conditional Styling
```tsx
<div className={`group flex gap-2.5 ${isReply ? 'ml-12' : ''}`}>
  <Avatar className={`${isReply ? 'h-8 w-8' : 'h-9 w-9'} mt-0.5 flex-shrink-0`}>
  
  <div className={`${
    isReply 
      ? 'bg-muted/30 dark:bg-muted/20 rounded-xl px-3 py-1.5' 
      : 'bg-muted/40 dark:bg-muted/30 rounded-2xl px-3.5 py-2'
  } hover:bg-muted/60 dark:hover:bg-muted/50 transition-all duration-200`}>
    
    <p className={`font-semibold ${isReply ? 'text-xs' : 'text-sm'} text-foreground mb-0.5`}>
    
    <p className={`${isReply ? 'text-sm' : 'text-[15px]'} text-foreground leading-relaxed`}>
```

## Benefits

1. **Clear Visual Hierarchy**: Easy to see which comments are replies
2. **Space Efficiency**: Replies take up less space
3. **Facebook-like UX**: Familiar interaction pattern
4. **Better Readability**: Indentation shows conversation flow
5. **Automatic Detection**: No manual tagging needed

## How It Works

1. User clicks "Reply" on a comment
2. Comment box pre-fills with `@Username `
3. User types their reply
4. When posted, comment starts with `@`
5. System detects `@` at start → styles as reply
6. Reply appears indented and smaller

## Testing

Test these scenarios:
1. ✅ Regular comment - full size, no indent
2. ✅ Reply with @ - smaller, indented
3. ✅ Multiple replies - all indented
4. ✅ Mix of comments and replies - clear hierarchy
5. ✅ "View all comments" works with both types

## Future Enhancements

- [ ] True nested replies (threaded comments)
- [ ] "Show X replies" for each comment
- [ ] Collapsible reply threads
- [ ] Reply count indicator
- [ ] Direct reply-to-reply chains
