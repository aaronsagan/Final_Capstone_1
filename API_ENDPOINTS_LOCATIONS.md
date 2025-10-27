# Philippine Locations API Endpoints

## Quick Reference

### **Base URL**
```
http://localhost:8000/api
```

---

## Endpoints

### 1. Get All Location Data
```http
GET /locations
```

**Response:**
```json
{
  "regions": [
    {
      "code": "NCR",
      "name": "National Capital Region (NCR)",
      "provinces": [
        {
          "code": "NCR",
          "name": "Metro Manila",
          "cities": ["Manila", "Quezon City", "Makati City", ...]
        }
      ]
    },
    ...
  ]
}
```

---

### 2. Get All Regions
```http
GET /locations/regions
```

**Response:**
```json
{
  "regions": [
    {
      "code": "NCR",
      "name": "National Capital Region (NCR)"
    },
    {
      "code": "01",
      "name": "Region I (Ilocos Region)"
    },
    ...
  ]
}
```

---

### 3. Get Provinces by Region
```http
GET /locations/regions/{regionCode}/provinces
```

**Example:**
```http
GET /locations/regions/NCR/provinces
GET /locations/regions/03/provinces
```

**Response:**
```json
{
  "provinces": [
    {
      "code": "NCR",
      "name": "Metro Manila"
    }
  ]
}
```

---

### 4. Get Cities by Region and Province
```http
GET /locations/regions/{regionCode}/provinces/{provinceCode}/cities
```

**Example:**
```http
GET /locations/regions/NCR/provinces/NCR/cities
GET /locations/regions/03/provinces/BUL/cities
```

**Response:**
```json
{
  "cities": [
    "Caloocan City",
    "Las Piñas City",
    "Makati City",
    "Manila",
    "Quezon City",
    ...
  ]
}
```

---

## Region Codes Reference

| Code | Region Name |
|------|-------------|
| NCR | National Capital Region |
| 01 | Region I (Ilocos Region) |
| 02 | Region II (Cagayan Valley) |
| 03 | Region III (Central Luzon) |
| 04A | Region IV-A (CALABARZON) |
| 07 | Region VII (Central Visayas) |
| 11 | Region XI (Davao Region) |

---

## Sample Province Codes

| Province Code | Province Name | Region |
|---------------|---------------|--------|
| NCR | Metro Manila | NCR |
| ILN | Ilocos Norte | Region I |
| CAG | Cagayan | Region II |
| BUL | Bulacan | Region III |
| CAV | Cavite | Region IV-A |
| CEB | Cebu | Region VII |
| DAV | Davao del Norte | Region XI |

---

## Error Responses

### 404 - Not Found
```json
{
  "error": "Region not found"
}
```

```json
{
  "error": "Province not found"
}
```

```json
{
  "error": "Location data not found"
}
```

---

## Usage in Frontend

### Using Axios
```typescript
import axios from 'axios';

const API_URL = 'http://localhost:8000/api';

// Get regions
const regions = await axios.get(`${API_URL}/locations/regions`);

// Get provinces
const provinces = await axios.get(`${API_URL}/locations/regions/NCR/provinces`);

// Get cities
const cities = await axios.get(`${API_URL}/locations/regions/NCR/provinces/NCR/cities`);
```

### Using Fetch
```javascript
// Get regions
const response = await fetch('http://localhost:8000/api/locations/regions');
const data = await response.json();
console.log(data.regions);
```

---

## Testing with cURL

```bash
# Get all regions
curl http://localhost:8000/api/locations/regions

# Get provinces for Region III
curl http://localhost:8000/api/locations/regions/03/provinces

# Get cities for Bulacan
curl http://localhost:8000/api/locations/regions/03/provinces/BUL/cities
```

---

## Notes

- All endpoints are **public** (no authentication required)
- Responses are in **JSON format**
- Region and province codes are **case-sensitive**
- Data is loaded from `database/data/ph_locations.json`
