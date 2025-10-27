# Donation Management - Component Map

## 🗺️ Component Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                    DonationManagement.tsx                       │
│                    (Main Entry Point)                           │
│                                                                 │
│  Simple wrapper that imports DonationsPage                      │
└────────────────────────────┬────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│                      DonationsPage.tsx                          │
│                  (Container & State Manager)                    │
│                                                                 │
│  • Manages all state (donations, filters, modals, pagination)   │
│  • Handles API calls (load, confirm, reject)                    │
│  • Calculates statistics                                        │
│  • Coordinates child components                                 │
│                                                                 │
│  State:                                                         │
│  ├─ donations: Donation[]                                       │
│  ├─ selectedRows: number[]                                      │
│  ├─ filters: DonationFilters                                    │
│  ├─ currentPage: number                                         │
│  └─ modal states (isOpen, selectedDonation)                     │
└───┬─────────────┬─────────────┬─────────────┬─────────────┬────┘
    │             │             │             │             │
    ▼             ▼             ▼             ▼             ▼
┌────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐
│Filters │  │  Table   │  │ Sidebar  │  │ Details  │  │  Export  │
│        │  │          │  │          │  │  Modal   │  │   Menu   │
└────────┘  └──────────┘  └──────────┘  └──────────┘  └──────────┘
     │            │             │             │             │
     ▼            ▼             ▼             ▼             ▼
┌─────────────────────────────────────────────────────────────────┐
│                    Child Components                             │
└─────────────────────────────────────────────────────────────────┘
```

---

## 📦 Component Details

### 1. DonationManagement.tsx
**Type:** Page Wrapper  
**Location:** `pages/charity/DonationManagement.tsx`  
**Purpose:** Main entry point, routes to DonationsPage  
**Size:** ~20 lines  

```tsx
export default function DonationManagement() {
  return <DonationsPage />;
}
```

**Props:** None  
**State:** None  
**Dependencies:** DonationsPage

---

### 2. DonationsPage.tsx
**Type:** Container Component  
**Location:** `components/charity/donations/DonationsPage.tsx`  
**Purpose:** Main logic and state management  
**Size:** ~230 lines  

**State Management:**
```tsx
const [donations, setDonations] = useState<Donation[]>([]);
const [loading, setLoading] = useState(true);
const [selectedDonation, setSelectedDonation] = useState<Donation | null>(null);
const [selectedRows, setSelectedRows] = useState<number[]>([]);
const [filters, setFilters] = useState<DonationFilters>({ status: 'all' });
const [currentPage, setCurrentPage] = useState(1);
const [totalPages, setTotalPages] = useState(1);
const [isDetailsModalOpen, setIsDetailsModalOpen] = useState(false);
const [isReconciliationModalOpen, setIsReconciliationModalOpen] = useState(false);
```

**Key Functions:**
- `loadDonations()` - Fetches from API
- `handleViewDetails()` - Opens detail modal
- `handleConfirmDonation()` - Confirms donation
- `handleRejectDonation()` - Rejects with reason
- `handleBulkAction()` - Processes bulk operations
- `handleExport()` - Exports data
- `handleFilterChange()` - Updates filters

**Child Components:**
- DonationFilters
- DonationsTable
- DonationStatsSidebar
- DonationDetailsModal
- ReconciliationModal
- ExportMenu

---

### 3. DonationFilters.tsx
**Type:** Presentational Component  
**Location:** `components/charity/donations/DonationFilters.tsx`  
**Purpose:** Search and filter controls  
**Size:** ~280 lines  

**Props:**
```tsx
interface DonationFiltersProps {
  filters: DonationFilters;
  onFilterChange: (filters: DonationFilters) => void;
  totalCount: number;
}
```

**Features:**
- Global search input
- Status dropdown
- Advanced filters popover
- Date range picker
- Quick date presets
- Amount range inputs
- Payment method selector
- Active filter badges
- Clear filters button

**State:**
```tsx
const [isAdvancedOpen, setIsAdvancedOpen] = useState(false);
const [localFilters, setLocalFilters] = useState<Filters>(filters);
```

**UI Elements:**
- Search bar with icon
- Select dropdown
- Popover with form
- Badge chips
- Buttons

---

### 4. DonationsTable.tsx
**Type:** Data Display Component  
**Location:** `components/charity/donations/DonationsTable.tsx`  
**Purpose:** Advanced data table with sorting and selection  
**Size:** ~380 lines  

**Props:**
```tsx
interface DonationsTableProps {
  donations: Donation[];
  loading: boolean;
  selectedRows: number[];
  onSelectRows: (ids: number[]) => void;
  onViewDetails: (donation: Donation) => void;
  onConfirm: (id: number) => void;
  onReject: (id: number) => void;
  currentPage: number;
  totalPages: number;
  onPageChange: (page: number) => void;
}
```

**Features:**
- Multi-column sorting
- Row selection (checkboxes)
- Expandable rows
- Status badges
- Inline actions
- Pagination controls
- Loading states
- Empty states

**State:**
```tsx
const [sortField, setSortField] = useState<SortField>('date');
const [sortDirection, setSortDirection] = useState<SortDirection>('desc');
const [expandedRows, setExpandedRows] = useState<number[]>([]);
```

**Table Columns:**
1. Checkbox
2. Transaction ID
3. Donor Name (sortable)
4. Campaign
5. Amount (sortable)
6. Date & Time (sortable)
7. Payment Method
8. Status (sortable)
9. Actions

---

### 5. DonationStatsSidebar.tsx
**Type:** Dashboard Widget Component  
**Location:** `components/charity/donations/DonationStatsSidebar.tsx`  
**Purpose:** Display KPIs and quick actions  
**Size:** ~220 lines  

**Props:**
```tsx
interface DonationStatsSidebarProps {
  stats: {
    totalReceived: number;
    totalThisMonth: number;
    pendingCount: number;
    confirmedCount: number;
    rejectedCount: number;
    averageDonation: number;
  };
  donations: Donation[];
  onOpenReconciliation: () => void;
  onRefresh: () => void;
}
```

**Features:**
- Key metrics cards
- Last 7 days bar chart
- Payment method distribution
- Quick action buttons
- Help links

**Calculations:**
- Aggregates donation data
- Calculates percentages
- Generates chart data

**UI Elements:**
- Multiple Card components
- Progress bars
- Bar chart (custom)
- Buttons
- Links

---

### 6. DonationDetailsModal.tsx
**Type:** Modal Component  
**Location:** `components/charity/donations/DonationDetailsModal.tsx`  
**Purpose:** View and manage individual donation  
**Size:** ~330 lines  

**Props:**
```tsx
interface DonationDetailsModalProps {
  donation: Donation | null;
  open: boolean;
  onClose: () => void;
  onConfirm: (id: number) => void;
  onReject: (id: number, reason: string) => void;
  onRefresh: () => void;
}
```

**Features:**
- Donor information display
- Donation details grid
- Proof of payment section
- Admin notes textarea
- Reject mode with inline form
- Action buttons

**State:**
```tsx
const [isRejectMode, setIsRejectMode] = useState(false);
const [rejectReason, setRejectReason] = useState("");
const [adminNote, setAdminNote] = useState("");
const [submitting, setSubmitting] = useState(false);
```

**Sections:**
1. Donor Info (name, email)
2. Donation Details (amount, date, method)
3. Proof of Payment (file display)
4. Admin Notes (textarea)
5. Actions (confirm, reject, download)

---

### 7. ReconciliationModal.tsx
**Type:** Complex Modal Component  
**Location:** `components/charity/donations/ReconciliationModal.tsx`  
**Purpose:** Match donations with bank transactions  
**Size:** ~280 lines  

**Props:**
```tsx
interface ReconciliationModalProps {
  open: boolean;
  onClose: () => void;
  donations: Donation[];
}
```

**Features:**
- Two-column layout
- Donation selection (left)
- Transaction selection (right)
- Match creation
- Matched pairs display
- Unmatch functionality
- Apply reconciliation

**State:**
```tsx
const [selectedDonation, setSelectedDonation] = useState<number | null>(null);
const [selectedTransaction, setSelectedTransaction] = useState<string | null>(null);
const [matchedPairs, setMatchedPairs] = useState<Array<{
  donationId: number;
  transactionId: string;
}>>([]);
```

**Mock Data:**
```tsx
const [bankTransactions] = useState<BankTransaction[]>([...]);
```

---

### 8. ExportMenu.tsx
**Type:** Dropdown Menu Component  
**Location:** `components/charity/donations/ExportMenu.tsx`  
**Purpose:** Export and bulk action controls  
**Size:** ~70 lines  

**Props:**
```tsx
interface ExportMenuProps {
  onExport: (format: 'csv' | 'excel' | 'pdf') => void;
  selectedCount: number;
  onBulkAction: (action: 'confirm' | 'reject' | 'export') => void;
}
```

**Features:**
- Export format options (CSV, Excel, PDF)
- Bulk actions (when rows selected)
- Selected count badge
- Dropdown menu

**Menu Items:**
1. Export as CSV
2. Export as Excel
3. Export as PDF
4. --- (separator if rows selected)
5. Confirm Selected
6. Reject Selected
7. Export Selected

---

## 🔄 Data Flow

### Loading Donations
```
User opens page
    ↓
DonationsPage.useEffect()
    ↓
loadDonations()
    ↓
donationsService.getCharityDonations(charityId, page)
    ↓
API: GET /api/charities/{id}/donations
    ↓
Response: PaginatedResponse<Donation>
    ↓
setDonations(response.data)
    ↓
DonationsTable receives donations prop
    ↓
Table renders rows
```

### Filtering
```
User types in search
    ↓
DonationFilters.onChange
    ↓
onFilterChange(newFilters)
    ↓
DonationsPage.handleFilterChange()
    ↓
setFilters(newFilters)
    ↓
setCurrentPage(1)
    ↓
useEffect triggers
    ↓
loadDonations() with new filters
    ↓
API call with filter params
    ↓
Table updates
```

### Confirming Donation
```
User clicks confirm icon
    ↓
DonationsTable.onConfirm(id)
    ↓
DonationsPage.handleConfirmDonation(id)
    ↓
donationsService.confirmDonation(id, 'completed')
    ↓
API: PATCH /api/donations/{id}/confirm
    ↓
Success response
    ↓
toast.success()
    ↓
loadDonations() to refresh
    ↓
Table updates with new status
```

### Bulk Action
```
User selects multiple rows
    ↓
DonationsTable checkboxes
    ↓
onSelectRows([ids])
    ↓
DonationsPage.setSelectedRows([ids])
    ↓
ExportMenu shows count badge
    ↓
User clicks "Confirm Selected"
    ↓
ExportMenu.onBulkAction('confirm')
    ↓
DonationsPage.handleBulkAction('confirm')
    ↓
Loop through selectedRows
    ↓
Confirm each donation
    ↓
toast.success with count
    ↓
loadDonations()
    ↓
Clear selection
```

---

## 🎨 Styling & Theme

### Color System
```tsx
// Status Colors
pending: 'bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300'
completed: 'bg-green-100 dark:bg-green-950 text-green-700 dark:text-green-300'
rejected: 'variant="destructive"'

// Primary Color
amount: 'text-primary' // Gold accent

// Interactive
hover: 'hover:bg-muted/50'
selected: 'bg-muted/30'
```

### Layout Classes
```tsx
// Container
'container px-4 py-6'

// Grid
'grid grid-cols-1 lg:grid-cols-4 gap-6'

// Sidebar
'lg:col-span-1 lg:sticky lg:top-20'

// Main Content
'lg:col-span-3 space-y-6'
```

### Responsive Breakpoints
```
sm: 640px   - Mobile landscape
md: 768px   - Tablet
lg: 1024px  - Desktop
xl: 1280px  - Large desktop
```

---

## 📊 Performance Metrics

### Component Sizes
```
DonationsPage:          ~230 lines
DonationsTable:         ~380 lines
DonationFilters:        ~280 lines
DonationStatsSidebar:   ~220 lines
DonationDetailsModal:   ~330 lines
ReconciliationModal:    ~280 lines
ExportMenu:             ~70 lines
───────────────────────────────────
Total:                  ~1,790 lines
```

### Render Optimization
- Memoize expensive calculations
- Use React.memo for pure components
- Debounce search input
- Lazy load modals
- Virtual scrolling for large lists (TODO)

---

## 🔌 API Integration Points

### Endpoints Used
```
GET    /api/charities/{id}/donations?page={page}
PATCH  /api/donations/{id}/confirm
PATCH  /api/donations/{id}/status
GET    /api/donations/{id}/receipt
POST   /api/donations/{id}/notes (TODO)
```

### Service Methods
```typescript
donationsService.getCharityDonations(charityId, page)
donationsService.confirmDonation(donationId, status)
donationsService.updateDonationStatus(donationId, status, reason)
donationsService.downloadReceipt(donationId)
```

---

## 🧪 Testing Strategy

### Unit Tests
- Test each component in isolation
- Mock props and callbacks
- Test state changes
- Test error handling

### Integration Tests
- Test component interactions
- Test data flow
- Test API calls (mocked)
- Test user workflows

### E2E Tests
- Test complete user journeys
- Test with real API
- Test responsive behavior
- Test dark mode

---

## 📝 Summary

The Donation Management system uses a **modular, component-based architecture** with:

- **1 Entry Point** (DonationManagement.tsx)
- **1 Container** (DonationsPage.tsx)
- **6 Child Components** (specialized features)
- **Clear separation of concerns**
- **Unidirectional data flow**
- **Reusable, maintainable code**

Each component has a **single responsibility** and communicates through **props and callbacks**, making the system easy to understand, test, and extend.
