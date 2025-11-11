# New Features Documentation

This document provides detailed information about the new features added to the Laravel-Vue Survey application for environmental research.

## Table of Contents

1. [GPS Location Collection](#gps-location-collection)
2. [Environmental Footprint Calculator](#environmental-footprint-calculator)
3. [Research Data Collection](#research-data-collection)
4. [User Privacy and Consent](#user-privacy-and-consent)
5. [Data Analysis Capabilities](#data-analysis-capabilities)

---

## GPS Location Collection

### Overview

The application now automatically collects GPS location data when surveys are submitted, enabling geographic analysis of environmental impact patterns. This feature is specifically designed for environmental research projects.

### How It Works

1. **User Interface**: When a user starts filling out a survey, they see a consent banner explaining why location data is being collected
2. **Consent Required**: Users must explicitly allow or deny location access
3. **Browser API**: Uses the browser's native Geolocation API for accurate positioning
4. **Optional Feature**: Surveys can be completed without providing location data

### User Experience

#### Permission Banner
When users open a survey, they see:

```
┌─────────────────────────────────────────────────────┐
│ 📍 Location Data for Environmental Research        │
│                                                     │
│ This survey is part of an environmental research   │
│ project. Would you like to share your location?    │
│ This helps us analyze regional environmental       │
│ impact patterns.                                    │
│                                                     │
│ [Allow Location Access] [Continue Without Location]│
└─────────────────────────────────────────────────────┘
```

#### Status Feedback
Users receive clear feedback:
- ⏳ "Requesting location..."
- ✓ "Location captured successfully for environmental research"
- ⚠️ "Unable to retrieve location" (with error details)
- ℹ️ "Continuing without location data"

### Technical Details

#### Frontend Implementation
- **File**: `vue/src/views/SurveyPublicView.vue`
- **API**: Browser Geolocation API
- **Configuration**:
  ```javascript
  {
    enableHighAccuracy: true,  // Best accuracy possible
    timeout: 10000,            // 10 second timeout
    maximumAge: 0              // No cached positions
  }
  ```

#### Backend Storage
- **Database Fields**:
  - `latitude` (decimal 10,8) - Valid range: -90 to 90
  - `longitude` (decimal 11,8) - Valid range: -180 to 180
- **Validation**: Server-side validation ensures coordinates are valid
- **Storage**: Coordinates stored in `survey_answers` table

### Privacy Considerations

✅ **Explicit Consent**: Users must actively choose to share location  
✅ **Clear Purpose**: Explanation of why data is collected  
✅ **Optional**: Surveys work without location data  
✅ **Secure**: Data transmitted over HTTPS  
✅ **Transparent**: Users informed of data usage  

### Usage for Researchers

Researchers can access GPS data to:
- Create geographic heat maps of water usage
- Analyze regional patterns
- Identify areas with high environmental impact
- Study correlation between location and practices

**Example Query**:
```php
// Get all survey responses with GPS data
$responses = SurveyAnswer::whereNotNull('latitude')
    ->whereNotNull('longitude')
    ->with('survey_question_answers')
    ->get();
```

---

## Environmental Footprint Calculator

### Overview

The application includes an automated water footprint calculator specifically designed for wine production surveys. It analyzes survey responses and calculates environmental impact metrics.

### Calculation Methodology

The FootprintCalculatorService implements a research-based methodology:

#### 1. Data Extraction
Analyzes survey responses to identify:
- Monthly water consumption (liters)
- Monthly wine production (liters)
- Water reuse practices (none, partial, full)
- Waste water discharge methods

#### 2. Base Calculation
```
Base Footprint = Water Consumption / 1000  (cubic meters)

OR (if only production volume available)

Base Footprint = (Production Volume × 1.5) / 1000
```

Where 1.5 is the industry average liters of water per liter of wine.

#### 3. Adjustments

**Water Reuse Factor**:
- No reuse: 1.0 (no change)
- Partial reuse: 0.8 (20% reduction)
- Full reuse: 0.6 (40% reduction)

**Discharge Impact Factor**:
- Other discharge methods: 1.0
- Surface water discharge: 1.1 (10% increase for environmental impact)

#### 4. Final Calculation
```
Adjusted Footprint = Base Footprint × Reuse Factor × Discharge Factor
```

### Interpretation Benchmarks

The system provides human-readable interpretations:

| Footprint (m³/month) | Interpretation | Recommendation |
|---------------------|----------------|----------------|
| 0 - 5 | Low | Excellent water management |
| 5 - 15 | Moderate | Good water management |
| 15 - 30 | Average | Room for improvement |
| 30 - 50 | High | Significant improvement needed |
| 50+ | Very High | Urgent action required |

### Automatic Calculation

The footprint is calculated automatically when a survey is submitted:

```php
// In SurveyController::storeAnswer()
$footprintService = new FootprintCalculatorService();
$footprint = $footprintService->calculateWaterFootprint($answers);

if ($footprint !== null) {
    $surveyAnswer->calculated_footprint = $footprint;
    $surveyAnswer->save();
}
```

### Service Methods

#### `calculateWaterFootprint(array $answers): ?float`
Calculates and returns the water footprint in cubic meters.

**Returns**: 
- `float` - Calculated footprint
- `null` - If insufficient data

#### `getFootprintInterpretation(float $footprint): string`
Returns human-readable interpretation.

**Example**:
```php
$interpretation = $service->getFootprintInterpretation(12.5);
// Returns: "Moderate water footprint - Good water management"
```

#### `getRecommendations(float $footprint, array $answers): array`
Provides context-specific recommendations.

**Example**:
```php
$recommendations = $service->getRecommendations(35.0, $answers);
// Returns array of actionable recommendations
```

### Technical Details

**File**: `app/Services/FootprintCalculatorService.php`

**Constants**:
```php
const AVERAGE_WATER_PER_LITER_WINE = 1.5;
const REUSE_REDUCTION_FACTOR_FULL = 0.4;
const REUSE_REDUCTION_FACTOR_PARTIAL = 0.2;
const WASTE_DISCHARGE_IMPACT_FACTOR = 1.1;
```

**Logging**: All calculations are logged for research audit trail

---

## Research Data Collection

### Data Structure

Each survey submission creates:

1. **Survey Answer Record**:
   - Survey ID
   - Start/End timestamps
   - GPS coordinates (optional)
   - Calculated footprint (automatic)

2. **Question Answers**:
   - Question ID
   - Answer content
   - Linked to survey answer

### Supported Question Types

The application supports multiple question types for comprehensive data collection:

- `text` - Free text input
- `textarea` - Multi-line text
- `select` - Dropdown selection
- `radio` - Single choice
- `checkbox` - Multiple choice
- `int` - Numeric input
- `footprint` - Environmental impact rating (1-5)

### Example Survey Flow

```
User opens survey
    ↓
GPS permission request
    ↓ (if allowed)
Capture GPS coordinates
    ↓
User completes questions
    ↓
Submit survey
    ↓
Backend processes:
    - Store answers
    - Save GPS coordinates
    - Calculate footprint
    - Log transaction
    ↓
Data ready for analysis
```

### Data Validation

All data is validated before storage:

✓ GPS coordinates within valid ranges  
✓ Required questions answered  
✓ Data types match expectations  
✓ Answers linked to valid questions  

---

## User Privacy and Consent

### Privacy-First Design

The application follows privacy-by-design principles:

#### 1. Informed Consent
Users receive clear information about:
- What data is collected (GPS location)
- Why it's collected (environmental research)
- How it will be used (regional analysis)

#### 2. User Control
- ✓ Users can decline location sharing
- ✓ Surveys work without GPS data
- ✓ No hidden tracking
- ✓ No background location collection

#### 3. Data Minimization
Only essential data is collected:
- GPS coordinates (latitude, longitude only)
- Survey responses
- Timestamps

#### 4. Transparency
- Clear explanation in UI
- Documentation of data usage
- Research purpose stated upfront

### Compliance Considerations

The implementation considers:

**GDPR (European Union)**:
- ✓ Explicit consent required
- ✓ Clear purpose specification
- ✓ Data minimization principle
- ✓ Right to refuse

**CCPA (California)**:
- ✓ Disclosure of data collection
- ✓ Opt-in for sensitive data
- ✓ Clear privacy information

**Research Ethics**:
- ✓ Informed consent
- ✓ Voluntary participation
- ✓ Data security
- ✓ Research purpose declaration

### Recommended Privacy Policy

Projects using this feature should include in their privacy policy:

```
Location Data Collection:
- Purpose: Environmental research and regional analysis
- Method: Browser-based GPS with user consent
- Opt-out: Available (surveys work without location)
- Storage: Secure database with access controls
- Usage: Research analysis only, not shared with third parties
- Retention: [Specify retention period]
```

---

## Data Analysis Capabilities

### Built-in Analysis Features

#### 1. Footprint Statistics

```php
// Get average footprint
$avgFootprint = SurveyAnswer::whereNotNull('calculated_footprint')
    ->avg('calculated_footprint');

// Get footprint distribution
$distribution = SurveyAnswer::selectRaw('
    CASE 
        WHEN calculated_footprint < 5 THEN "Low"
        WHEN calculated_footprint < 15 THEN "Moderate"
        WHEN calculated_footprint < 30 THEN "Average"
        WHEN calculated_footprint < 50 THEN "High"
        ELSE "Very High"
    END as category,
    COUNT(*) as count
')->groupBy('category')->get();
```

#### 2. Geographic Analysis

```php
// Get responses by region (requires additional region classification)
$geoData = SurveyAnswer::whereNotNull('latitude')
    ->select('latitude', 'longitude', 'calculated_footprint')
    ->get();

// Export for GIS tools
$csvData = $geoData->map(function($answer) {
    return [
        'lat' => $answer->latitude,
        'lng' => $answer->longitude,
        'footprint' => $answer->calculated_footprint,
    ];
});
```

#### 3. Temporal Analysis

```php
// Footprint trends over time
$trends = SurveyAnswer::selectRaw('
    DATE(start_date) as date,
    AVG(calculated_footprint) as avg_footprint
')
    ->whereNotNull('calculated_footprint')
    ->groupBy('date')
    ->orderBy('date')
    ->get();
```

### Visualization Recommendations

**Geographic Visualization**:
- Heat maps using Leaflet.js or Mapbox
- Cluster maps for dense regions
- Choropleth maps for regional averages

**Statistical Visualization**:
- Bar charts for footprint distribution
- Line charts for trends over time
- Scatter plots for correlations

**Tools**:
- QGIS (open source GIS)
- Tableau or Power BI
- Python (Pandas, Matplotlib, Folium)
- R (ggplot2, leaflet)

### Export Formats

Data can be exported in various formats:

**CSV**: For Excel and statistical software
```php
$csv = SurveyAnswer::with('survey_question_answers')
    ->get()
    ->map(/* transform to flat structure */)
    ->toCsv();
```

**JSON**: For web applications
```php
$json = SurveyAnswer::with('survey_question_answers')
    ->get()
    ->toJson();
```

**GeoJSON**: For GIS applications
```php
$geojson = [
    'type' => 'FeatureCollection',
    'features' => $geoData->map(function($answer) {
        return [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [$answer->longitude, $answer->latitude]
            ],
            'properties' => [
                'footprint' => $answer->calculated_footprint,
                // other properties
            ]
        ];
    })
];
```

---

## Integration Guide

### Adding Footprint Calculation to Existing Surveys

If you have an existing survey and want to add footprint calculation:

1. **Ensure Questions Collect Required Data**:
   - Water consumption question (numeric)
   - Wine production question (numeric)
   - Water reuse question (yes/no or multiple choice)
   - Discharge method question (multiple choice)

2. **The Calculator Will Automatically**:
   - Detect relevant answers
   - Calculate footprint
   - Store result in database

3. **No Code Changes Required**: The service runs automatically on survey submission

### Adding GPS to Additional Survey Types

To enable GPS collection for other survey types:

1. The GPS feature works with any survey
2. Location permission is requested automatically
3. No survey-specific configuration needed

### Customizing Calculations

To modify the footprint calculation methodology:

**File**: `app/Services/FootprintCalculatorService.php`

**Adjust Constants**:
```php
// Change industry average
const AVERAGE_WATER_PER_LITER_WINE = 2.0;  // Your value

// Change reuse impact
const REUSE_REDUCTION_FACTOR_FULL = 0.5;  // 50% reduction

// Change discharge impact
const WASTE_DISCHARGE_IMPACT_FACTOR = 1.2;  // 20% increase
```

**Modify Logic**: Update `calculateWaterFootprint()` method

---

## API Reference

### Endpoints

#### Submit Survey with GPS and Footprint

**POST** `/api/survey/{survey}/answer`

**Request Body**:
```json
{
  "answers": {
    "1": "Producer Name",
    "2": "10000",
    "3": "Sí, totalmente"
  },
  "latitude": 40.7128,
  "longitude": -74.0060
}
```

**Response**: `201 Created`

**Storage**:
- Answers stored in `survey_question_answers`
- GPS coordinates stored in `survey_answers.latitude/longitude`
- Footprint automatically calculated and stored in `survey_answers.calculated_footprint`

#### Get Survey Answers with Footprint Data

**GET** `/api/survey-answers/{survey_id}`

**Response**:
```json
{
  "data": {
    "answers": {
      "1": {"1": "Answer 1", "2": "Answer 2"}
    },
    "questions": [...],
    "survey": {...}
  }
}
```

---

## Troubleshooting

### GPS Not Working

**Issue**: Location permission denied  
**Solution**: User must allow location in browser settings

**Issue**: GPS timeout  
**Solution**: Increase timeout in `SurveyPublicView.vue` (default: 10s)

**Issue**: Inaccurate location  
**Solution**: Ensure HTTPS (required for high accuracy mode)

### Footprint Not Calculated

**Issue**: Footprint is null  
**Reason**: Insufficient data in survey responses  
**Solution**: Ensure survey includes water consumption or production volume questions

**Issue**: Incorrect calculation  
**Solution**: Verify question formats match expected patterns (numeric values, specific keywords)

### Data Analysis Issues

**Issue**: Missing GPS data  
**Solution**: Users may have declined location sharing (this is expected behavior)

**Issue**: Footprint values seem incorrect  
**Solution**: Review calculation constants and methodology in `FootprintCalculatorService.php`

---

## Future Enhancements

Potential areas for expansion:

### 1. Enhanced Calculations
- Carbon footprint calculation
- Energy usage metrics
- Chemical input tracking
- Multi-factor environmental score

### 2. Advanced Analytics
- Machine learning predictions
- Anomaly detection
- Comparative benchmarking
- Regional baseline establishment

### 3. Visualization
- Built-in dashboard with maps
- Real-time statistics
- Producer comparison tools
- Progress tracking over time

### 4. Integration
- Export to research databases
- API for external analysis tools
- Automated report generation
- Integration with GIS platforms

---

## Support and Documentation

For additional information, see:

- **[ENVIRONMENTAL_FOOTPRINT.md](ENVIRONMENTAL_FOOTPRINT.md)** - Technical details
- **[RESEARCH_PROJECT.md](RESEARCH_PROJECT.md)** - Research methodology
- **[CI_CD.md](CI_CD.md)** - CI/CD pipeline documentation
- **[DEPLOYMENT.md](DEPLOYMENT.md)** - Deployment guide

For questions or issues:
1. Check existing documentation
2. Review the code in `app/Services/FootprintCalculatorService.php`
3. Check logs in `storage/logs/laravel.log`

---

**Last Updated**: November 2025  
**Version**: 1.0.0  
**Maintained By**: Development Team
