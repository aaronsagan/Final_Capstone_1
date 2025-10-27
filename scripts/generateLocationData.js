/**
 * Script to Generate Complete Philippine Location Data from PSGC API
 * Run with: node scripts/generateLocationData.js
 */

const fs = require('fs');
const path = require('path');

const API_BASE = 'https://psgc.gitlab.io/api';

// Add delay between requests to avoid rate limiting
const delay = (ms) => new Promise(resolve => setTimeout(resolve, ms));

async function fetchWithRetry(url, retries = 3) {
  for (let i = 0; i < retries; i++) {
    try {
      const response = await fetch(url);
      if (!response.ok) throw new Error(`HTTP ${response.status}`);
      return await response.json();
    } catch (error) {
      console.error(`Attempt ${i + 1} failed for ${url}:`, error.message);
      if (i === retries - 1) throw error;
      await delay(2000); // Wait 2 seconds before retry
    }
  }
}

async function generateCompleteLocationData() {
  console.log('🚀 Starting PSGC data fetch...\n');
  
  try {
    // Fetch all regions
    console.log('📍 Fetching regions...');
    const regions = await fetchWithRetry(`${API_BASE}/regions/`);
    console.log(`✅ Found ${regions.length} regions\n`);
    
    const completeData = [];
    
    for (const region of regions) {
      console.log(`📍 Processing ${region.name}...`);
      
      // Fetch provinces for this region
      const provinces = await fetchWithRetry(`${API_BASE}/regions/${region.code}/provinces/`);
      console.log(`  ✅ Found ${provinces.length} provinces`);
      await delay(500); // Be nice to the API
      
      const provincesData = [];
      
      for (const province of provinces) {
        console.log(`    📍 Processing ${province.name}...`);
        
        try {
          // Fetch cities/municipalities
          const cities = await fetchWithRetry(`${API_BASE}/provinces/${province.code}/cities-municipalities/`);
          console.log(`      ✅ Found ${cities.length} cities/municipalities`);
          await delay(500);
          
          const citiesData = [];
          
          for (const city of cities) {
            console.log(`        📍 Processing ${city.name}...`);
            
            try {
              // Fetch barangays
              const barangays = await fetchWithRetry(`${API_BASE}/cities-municipalities/${city.code}/barangays/`);
              console.log(`          ✅ Found ${barangays.length} barangays`);
              
              citiesData.push({
                name: city.name,
                barangays: barangays.map(b => b.name).sort()
              });
              
              await delay(300); // Smaller delay for barangays
            } catch (error) {
              console.error(`          ❌ Failed to fetch barangays for ${city.name}`);
              citiesData.push({
                name: city.name,
                barangays: []
              });
            }
          }
          
          provincesData.push({
            name: province.name,
            cities: citiesData.sort((a, b) => a.name.localeCompare(b.name))
          });
        } catch (error) {
          console.error(`    ❌ Failed to fetch cities for ${province.name}`);
          provincesData.push({
            name: province.name,
            cities: []
          });
        }
      }
      
      completeData.push({
        name: region.name,
        provinces: provincesData.sort((a, b) => a.name.localeCompare(b.name))
      });
      
      console.log(`✅ Completed ${region.name}\n`);
    }
    
    // Generate TypeScript file content
    console.log('\n📝 Generating TypeScript file...');
    
    const tsContent = `/**
 * Complete Philippine Location Data
 * Generated from PSGC API: https://psgc.gitlab.io/api/
 * Generated on: ${new Date().toISOString()}
 * 
 * Data includes:
 * - ${completeData.length} Regions
 * - ${completeData.reduce((sum, r) => sum + r.provinces.length, 0)} Provinces
 * - ${completeData.reduce((sum, r) => sum + r.provinces.reduce((s, p) => s + p.cities.length, 0), 0)} Cities/Municipalities
 * - ${completeData.reduce((sum, r) => sum + r.provinces.reduce((s, p) => s + p.cities.reduce((c, ci) => c + ci.barangays.length, 0), 0), 0)} Barangays
 */

export interface Barangay {
  name: string;
}

export interface City {
  name: string;
  barangays: string[];
}

export interface Province {
  name: string;
  cities: City[];
}

export interface Region {
  name: string;
  provinces: Province[];
}

export const philippineLocations: Region[] = ${JSON.stringify(completeData, null, 2)};

/**
 * Helper functions for location data
 */
export const getRegions = (): string[] => {
  return philippineLocations.map(r => r.name);
};

export const getProvinces = (regionName: string): string[] => {
  const region = philippineLocations.find(r => r.name === regionName);
  return region ? region.provinces.map(p => p.name) : [];
};

export const getCities = (regionName: string, provinceName: string): string[] => {
  const region = philippineLocations.find(r => r.name === regionName);
  const province = region?.provinces.find(p => p.name === provinceName);
  return province ? province.cities.map(c => c.name) : [];
};

export const getBarangays = (regionName: string, provinceName: string, cityName: string): string[] => {
  const region = philippineLocations.find(r => r.name === regionName);
  const province = region?.provinces.find(p => p.name === provinceName);
  const city = province?.cities.find(c => c.name === cityName);
  return city ? city.barangays : [];
};

/**
 * Get total counts
 */
export const getStats = () => ({
  regions: philippineLocations.length,
  provinces: philippineLocations.reduce((sum, r) => sum + r.provinces.length, 0),
  cities: philippineLocations.reduce((sum, r) => sum + r.provinces.reduce((s, p) => s + p.cities.length, 0), 0),
  barangays: philippineLocations.reduce((sum, r) => sum + r.provinces.reduce((s, p) => s + p.cities.reduce((c, ci) => c + ci.barangays.length, 0), 0), 0)
});
`;
    
    // Save to file
    const outputPath = path.join(__dirname, '..', 'capstone_frontend', 'src', 'data', 'philippineLocations.ts');
    fs.writeFileSync(outputPath, tsContent, 'utf8');
    
    console.log('\n✅ SUCCESS! File generated at:');
    console.log(`   ${outputPath}`);
    
    // Print statistics
    const stats = {
      regions: completeData.length,
      provinces: completeData.reduce((sum, r) => sum + r.provinces.length, 0),
      cities: completeData.reduce((sum, r) => sum + r.provinces.reduce((s, p) => s + p.cities.length, 0), 0),
      barangays: completeData.reduce((sum, r) => sum + r.provinces.reduce((s, p) => s + p.cities.reduce((c, ci) => c + ci.barangays.length, 0), 0), 0)
    };
    
    console.log('\n📊 Data Statistics:');
    console.log(`   Regions: ${stats.regions}`);
    console.log(`   Provinces: ${stats.provinces}`);
    console.log(`   Cities/Municipalities: ${stats.cities}`);
    console.log(`   Barangays: ${stats.barangays}`);
    
    const fileSize = fs.statSync(outputPath).size;
    console.log(`\n📦 File Size: ${(fileSize / 1024 / 1024).toFixed(2)} MB`);
    
    console.log('\n✨ Done! You can now use the updated philippineLocations.ts file.');
    
  } catch (error) {
    console.error('\n❌ Error generating location data:', error);
    process.exit(1);
  }
}

// Run the script
generateCompleteLocationData();
