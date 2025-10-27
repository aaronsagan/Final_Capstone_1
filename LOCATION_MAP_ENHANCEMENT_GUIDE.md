# 🗺️ Location Map & Filtering Enhancement - Implementation Guide

## ✅ **What Has Been Completed**

### **Backend Implementation** ✅

I've added three new API endpoints to support advanced location analytics:

---

### **1. GET /api/analytics/campaigns/by-location**

**Purpose:** Get campaigns grouped by full location hierarchy with optional filtering

**Query Parameters:**
- `charity_id` (optional): Filter by specific charity
- `region` (optional): Filter by region
- `province` (optional): Filter by province  
- `city` (optional): Filter by city

**Response Example:**
```json
[
  {
    "region": "NCR",
    "province": "Metro Manila",
    "city": "Quezon City",
    "barangay": "Commonwealth",
    "total": 10
  },
  {
    "region": "Region VII",
    "province": "Cebu",
    "city": "Cebu City",
    "barangay": "Lahug",
    "total": 6
  }
]
```

**Use Case:** Powers the filtered location view and detailed breakdowns

---

### **2. GET /api/analytics/campaigns/location-summary**

**Purpose:** Get high-level statistics about location coverage

**Query Parameters:**
- `charity_id` (optional): Filter by specific charity

**Response Example:**
```json
{
  "regions": 10,
  "provinces": 48,
  "cities": 120,
  "campaigns": 320
}
```

**Use Case:** Display summary stats cards above the map

---

### **3. GET /api/analytics/campaigns/location-filters**

**Purpose:** Get available filter options (cascading dropdowns)

**Query Parameters:**
- `charity_id` (optional): Filter by specific charity
- `region` (optional): Filter provinces by region
- `province` (optional): Filter cities by province

**Response Example:**
```json
{
  "regions": ["NCR", "Region III", "Region VII"],
  "provinces": ["Metro Manila", "Bulacan", "Cebu"],
  "cities": ["Quezon City", "Manila", "Cebu City"]
}
```

**Use Case:** Populate cascading filter dropdowns

---

## 📦 **Required Frontend Packages**

To implement the map visualization, you'll need to install:

```bash
npm install leaflet react-leaflet
npm install --save-dev @types/leaflet
```

**Package Details:**
- **leaflet**: Core mapping library (open-source, no API key needed)
- **react-leaflet**: React components for Leaflet
- **@types/leaflet**: TypeScript type definitions

---

## 🗺️ **Frontend Implementation Plan**

### **Step 1: Create Location Map Component**

**File:** `src/components/analytics/LocationMap.tsx`

```tsx
import { MapContainer, TileLayer, Marker, Popup, CircleMarker } from 'react-leaflet';
import { motion } from 'framer-motion';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';

// Fix leaflet default marker icon issue
delete (L.Icon.Default.prototype as any)._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon-2x.png',
  iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
  shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
});

interface LocationMapProps {
  data: Array<{
    city: string;
    province: string;
    region: string;
    total: number;
    lat?: number;
    lng?: number;
  }>;
  selectedCity?: string;
  onCityClick?: (city: string) => void;
}

export default function LocationMap({ data, selectedCity, onCityClick }: LocationMapProps) {
  // Philippine center coordinates
  const philippinesCenter: [number, number] = [12.8797, 121.774];

  // Helper: Get city coordinates (static mapping or geocoding API)
  const getCityCoordinates = (city: string): [number, number] | null => {
    const cityCoords: Record<string, [number, number]> = {
      'Quezon City': [14.6760, 121.0437],
      'Manila': [14.5995, 120.9842],
      'Cebu City': [10.3157, 123.8854],
      'Davao City': [7.1907, 125.4553],
      'Makati': [14.5547, 121.0244],
      'Pasig': [14.5764, 121.0851],
      'Taguig': [14.5176, 121.0509],
      'Caloocan': [14.6507, 120.9838],
      'Las Piñas': [14.4453, 120.9823],
      'Parañaque': [14.4793, 121.0198],
      // Add more cities as needed
    };
    return cityCoords[city] || null;
  };

  // Get marker size based on campaign count
  const getMarkerRadius = (count: number) => {
    const maxCount = Math.max(...data.map(d => d.total));
    return Math.max(8, Math.min(30, (count / maxCount) * 30));
  };

  return (
    <motion.div
      initial={{ opacity: 0 }}
      animate={{ opacity: 1 }}
      transition={{ duration: 0.8 }}
      className="h-[450px] rounded-2xl overflow-hidden shadow-[0_0_25px_rgba(59,130,246,0.15)] border border-slate-700"
    >
      <MapContainer
        center={philippinesCenter}
        zoom={6}
        className="h-full w-full"
        zoomControl={true}
      >
        <TileLayer
          attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
          url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
        />
        
        {data.map((location, index) => {
          const coords = getCityCoordinates(location.city);
          if (!coords) return null;

          const radius = getMarkerRadius(location.total);
          const isSelected = selectedCity === location.city;

          return (
            <CircleMarker
              key={`${location.city}-${index}`}
              center={coords}
              radius={radius}
              pathOptions={{
                fillColor: isSelected ? '#3B82F6' : '#0EA5E9',
                fillOpacity: isSelected ? 0.8 : 0.6,
                color: '#fff',
                weight: isSelected ? 3 : 2,
              }}
              eventHandlers={{
                click: () => onCityClick && onCityClick(location.city),
              }}
            >
              <Popup>
                <div className="text-sm p-2">
                  <strong className="text-base text-blue-600">{location.city}</strong>
                  <div className="text-xs text-gray-600 mt-1">{location.province}</div>
                  <div className="text-xs text-gray-600">{location.region}</div>
                  <div className="mt-2 text-base font-semibold text-primary">
                    {location.total} campaign{location.total !== 1 ? 's' : ''}
                  </div>
                </div>
              </Popup>
            </CircleMarker>
          );
        })}
      </MapContainer>
    </motion.div>
  );
}
```

---

### **Step 2: Create Location Filters Component**

**File:** `src/components/analytics/LocationFilters.tsx`

```tsx
import { motion } from 'framer-motion';
import { Filter, X } from 'lucide-react';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Button } from '@/components/ui/button';

interface LocationFiltersProps {
  regions: string[];
  provinces: string[];
  cities: string[];
  selectedRegion: string;
  selectedProvince: string;
  selectedCity: string;
  onRegionChange: (value: string) => void;
  onProvinceChange: (value: string) => void;
  onCityChange: (value: string) => void;
  onClearFilters: () => void;
}

export default function LocationFilters({
  regions,
  provinces,
  cities,
  selectedRegion,
  selectedProvince,
  selectedCity,
  onRegionChange,
  onProvinceChange,
  onCityChange,
  onClearFilters,
}: LocationFiltersProps) {
  const hasActiveFilters = selectedRegion || selectedProvince || selectedCity;

  return (
    <motion.div
      initial={{ opacity: 0, y: -10 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.4 }}
      className="flex flex-col lg:flex-row gap-4 p-6 bg-slate-800/40 rounded-xl border border-slate-700/50"
    >
      <div className="flex items-center gap-2 text-slate-300">
        <Filter className="h-5 w-5 text-blue-400" />
        <span className="font-medium text-sm">Filter by Location:</span>
      </div>

      <div className="flex flex-col sm:flex-row gap-3 flex-1">
        {/* Region Filter */}
        <Select value={selectedRegion} onValueChange={onRegionChange}>
          <SelectTrigger className="bg-slate-900 border-slate-700 text-slate-200">
            <SelectValue placeholder="All Regions" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="">All Regions</SelectItem>
            {regions.map((region) => (
              <SelectItem key={region} value={region}>
                {region}
              </SelectItem>
            ))}
          </SelectContent>
        </Select>

        {/* Province Filter */}
        <Select 
          value={selectedProvince} 
          onValueChange={onProvinceChange}
          disabled={!selectedRegion && provinces.length === 0}
        >
          <SelectTrigger className="bg-slate-900 border-slate-700 text-slate-200">
            <SelectValue placeholder="All Provinces" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="">All Provinces</SelectItem>
            {provinces.map((province) => (
              <SelectItem key={province} value={province}>
                {province}
              </SelectItem>
            ))}
          </SelectContent>
        </Select>

        {/* City Filter */}
        <Select 
          value={selectedCity} 
          onValueChange={onCityChange}
          disabled={!selectedProvince && cities.length === 0}
        >
          <SelectTrigger className="bg-slate-900 border-slate-700 text-slate-200">
            <SelectValue placeholder="All Cities" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="">All Cities</SelectItem>
            {cities.map((city) => (
              <SelectItem key={city} value={city}>
                {city}
              </SelectItem>
            ))}
          </SelectContent>
        </Select>
      </div>

      {/* Clear Filters Button */}
      {hasActiveFilters && (
        <Button
          variant="outline"
          size="sm"
          onClick={onClearFilters}
          className="border-slate-700 text-slate-300 hover:bg-slate-700"
        >
          <X className="h-4 w-4 mr-1" />
          Clear
        </Button>
      )}
    </motion.div>
  );
}
```

---

### **Step 3: Create Location Summary Cards**

**File:** `src/components/analytics/LocationSummaryCards.tsx`

```tsx
import { motion } from 'framer-motion';
import { MapPin, Building2, Map, Target } from 'lucide-react';

interface LocationSummaryCardsProps {
  regions: number;
  provinces: number;
  cities: number;
  campaigns: number;
}

export default function LocationSummaryCards({
  regions,
  provinces,
  cities,
  campaigns,
}: LocationSummaryCardsProps) {
  const stats = [
    { label: 'Regions', value: regions, icon: Map, color: 'text-blue-400', bg: 'bg-blue-500/10' },
    { label: 'Provinces', value: provinces, icon: Building2, color: 'text-cyan-400', bg: 'bg-cyan-500/10' },
    { label: 'Cities', value: cities, icon: MapPin, color: 'text-emerald-400', bg: 'bg-emerald-500/10' },
    { label: 'Total Campaigns', value: campaigns, icon: Target, color: 'text-amber-400', bg: 'bg-amber-500/10' },
  ];

  return (
    <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      {stats.map((stat, index) => {
        const Icon = stat.icon;
        return (
          <motion.div
            key={stat.label}
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: index * 0.1, duration: 0.4 }}
            className="bg-slate-800/40 rounded-xl p-4 border border-slate-700/50 hover:border-slate-600 transition-all"
          >
            <div className="flex items-center gap-3">
              <div className={`p-2 rounded-lg ${stat.bg}`}>
                <Icon className={`h-5 w-5 ${stat.color}`} />
              </div>
              <div>
                <p className="text-sm text-slate-400">{stat.label}</p>
                <p className="text-2xl font-bold text-slate-100">{stat.value}</p>
              </div>
            </div>
          </motion.div>
        );
      })}
    </div>
  );
}
```

---

### **Step 4: Update Analytics.tsx**

Add state management and API calls:

```typescript
// Add new state
const [locationSummary, setLocationSummary] = useState({ regions: 0, provinces: 0, cities: 0, campaigns: 0 });
const [locationFilters, setLocationFilters] = useState({ regions: [], provinces: [], cities: [] });
const [selectedRegion, setSelectedRegion] = useState('');
const [selectedProvince, setSelectedProvince] = useState('');
const [selectedCity, setSelectedCity] = useState('');
const [filteredLocationData, setFilteredLocationData] = useState<any[]>([]);

// Add fetch functions
const fetchLocationSummary = async () => {
  try {
    const token = getAuthToken();
    if (!token) return;
    
    const response = await fetch(
      buildApiUrl(`/analytics/campaigns/location-summary?charity_id=${user?.charity?.id}`),
      { headers: { Authorization: `Bearer ${token}` } }
    );
    
    if (response.ok) {
      const data = await response.json();
      setLocationSummary(data);
    }
  } catch (error) {
    console.error('Failed to load location summary:', error);
  }
};

const fetchLocationFilters = async () => {
  try {
    const token = getAuthToken();
    if (!token) return;
    
    const params = new URLSearchParams();
    if (user?.charity?.id) params.append('charity_id', user.charity.id.toString());
    if (selectedRegion) params.append('region', selectedRegion);
    if (selectedProvince) params.append('province', selectedProvince);
    
    const response = await fetch(
      buildApiUrl(`/analytics/campaigns/location-filters?${params}`),
      { headers: { Authorization: `Bearer ${token}` } }
    );
    
    if (response.ok) {
      const data = await response.json();
      setLocationFilters(data);
    }
  } catch (error) {
    console.error('Failed to load location filters:', error);
  }
};

const fetchFilteredLocationData = async () => {
  try {
    const token = getAuthToken();
    if (!token) return;
    
    const params = new URLSearchParams();
    if (user?.charity?.id) params.append('charity_id', user.charity.id.toString());
    if (selectedRegion) params.append('region', selectedRegion);
    if (selectedProvince) params.append('province', selectedProvince);
    if (selectedCity) params.append('city', selectedCity);
    
    const response = await fetch(
      buildApiUrl(`/analytics/campaigns/by-location?${params}`),
      { headers: { Authorization: `Bearer ${token}` } }
    );
    
    if (response.ok) {
      const data = await response.json();
      setFilteredLocationData(Array.isArray(data) ? data : []);
    }
  } catch (error) {
    console.error('Failed to load filtered location data:', error);
  }
};

// Add useEffects
useEffect(() => {
  fetchLocationSummary();
  fetchLocationFilters();
}, []);

useEffect(() => {
  fetchLocationFilters();
  fetchFilteredLocationData();
}, [selectedRegion, selectedProvince, selectedCity]);
```

---

## 🎨 **UI Integration Example**

Replace the current location section with:

```tsx
{/* Location Analysis - Enhanced with Map */}
<motion.div className="bg-gradient-to-br from-slate-900/60 to-slate-800/60 backdrop-blur-md rounded-2xl border border-slate-800 shadow-lg">
  {/* Header */}
  <div className="p-8 pb-6">
    <h2 className="text-2xl font-semibold text-foreground">Distribution by Location</h2>
    <p className="text-sm text-muted-foreground mt-1">Interactive map and location-based analytics</p>
  </div>

  {/* Content */}
  <div className="px-8 pb-8 space-y-6">
    {/* Summary Cards */}
    <LocationSummaryCards {...locationSummary} />

    {/* Filters */}
    <LocationFilters
      regions={locationFilters.regions}
      provinces={locationFilters.provinces}
      cities={locationFilters.cities}
      selectedRegion={selectedRegion}
      selectedProvince={selectedProvince}
      selectedCity={selectedCity}
      onRegionChange={setSelectedRegion}
      onProvinceChange={setSelectedProvince}
      onCityChange={setSelectedCity}
      onClearFilters={() => {
        setSelectedRegion('');
        setSelectedProvince('');
        setSelectedCity('');
      }}
    />

    {/* Map + Chart Grid */}
    <div className="grid xl:grid-cols-2 gap-8">
      {/* Left: Interactive Map */}
      <LocationMap
        data={filteredLocationData}
        selectedCity={selectedCity}
        onCityClick={setSelectedCity}
      />

      {/* Right: Chart + List (existing implementation) */}
      <div>
        {/* Your existing bar chart and ranked list */}
      </div>
    </div>
  </div>
</motion.div>
```

---

## 📊 **City Coordinates Mapping**

For production, consider these options:

### **Option 1: Static Mapping** (Quick, Free)
Create a coordinates file:

```typescript
// src/data/cityCoordinates.ts
export const CITY_COORDINATES: Record<string, [number, number]> = {
  // NCR
  'Quezon City': [14.6760, 121.0437],
  'Manila': [14.5995, 120.9842],
  'Makati': [14.5547, 121.0244],
  'Pasig': [14.5764, 121.0851],
  'Taguig': [14.5176, 121.0509],
  
  // Luzon
  'Baguio City': [16.4023, 120.5960],
  'Angeles City': [15.1450, 120.5887],
  
  // Visayas
  'Cebu City': [10.3157, 123.8854],
  'Iloilo City': [10.7202, 122.5621],
  
  // Mindanao
  'Davao City': [7.1907, 125.4553],
  'Cagayan de Oro': [8.4542, 124.6319],
  
  // Add more as needed...
};
```

### **Option 2: Geocoding API** (Dynamic, Requires API key)
Use Nominatim (free) or Google Maps Geocoding API:

```typescript
async function getCityCoordinates(city: string, province: string) {
  const query = `${city}, ${province}, Philippines`;
  const response = await fetch(
    `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(query)}&format=json&limit=1`
  );
  const data = await response.json();
  if (data[0]) {
    return [parseFloat(data[0].lat), parseFloat(data[0].lon)];
  }
  return null;
}
```

---

## 🎯 **Testing Checklist**

### **Backend:**
- [ ] Test `/api/analytics/campaigns/by-location` endpoint
- [ ] Test `/api/analytics/campaigns/location-summary` endpoint
- [ ] Test `/api/analytics/campaigns/location-filters` endpoint
- [ ] Verify filtering works (region → province → city)
- [ ] Check response format matches expectations

### **Frontend (After Implementation):**
- [ ] Map loads correctly with Philippine center
- [ ] Markers appear for cities with campaigns
- [ ] Marker size correlates with campaign count
- [ ] Clicking marker filters to that city
- [ ] Filters work cascading (region → province → city)
- [ ] Clear filters button resets everything
- [ ] Summary cards show correct counts
- [ ] Chart updates based on filters
- [ ] Mobile responsive (stacks vertically)
- [ ] Smooth animations throughout

---

## 🚀 **Next Steps**

1. **Install packages:**
   ```bash
   npm install leaflet react-leaflet @types/leaflet
   ```

2. **Create components:**
   - `LocationMap.tsx`
   - `LocationFilters.tsx`
   - `LocationSummaryCards.tsx`

3. **Add city coordinates mapping**

4. **Update Analytics.tsx** with state and API calls

5. **Test thoroughly**

6. **Optional enhancements:**
   - Heat map layer
   - Export functionality
   - Search by city name
   - Animated markers

---

## ✅ **Summary**

**Completed:**
- ✅ Backend endpoints for location analytics
- ✅ API routes configured
- ✅ Filtering logic implemented
- ✅ Summary statistics endpoint
- ✅ Cascading filter options endpoint

**Ready to Implement:**
- 📦 Install leaflet packages
- 🗺️ Create map component
- 🎛️ Add filter UI
- 📊 Add summary cards
- 🔗 Integrate with Analytics page

**Your location analytics infrastructure is ready! Follow the implementation steps to complete the map visualization.** 🗺️✨
