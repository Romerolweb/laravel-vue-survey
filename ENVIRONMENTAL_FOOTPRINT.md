# Environmental Footprint Calculation Feature

## Overview

This document describes the environmental footprint calculation feature implemented for the Laravel-Vue Survey application. This feature is part of an environmental research project focused on measuring and analyzing the water footprint of wine production.

## Purpose

The primary purpose of this feature is to:
1. Collect detailed survey data from wine producers about their production processes
2. Automatically calculate their water footprint based on responses
3. Collect GPS location data (with user consent) to enable regional environmental analysis
4. Provide insights and recommendations for improving water usage efficiency

## Components

### 1. Database Schema

#### Migration: `add_gps_and_footprint_to_survey_answers`
- **Location**: `database/migrations/2025_11_11_000001_add_gps_and_footprint_to_survey_answers.php`
- **Purpose**: Adds fields to store GPS coordinates and calculated footprint values

**New Fields Added to `survey_answers` table:**
- `latitude` (decimal 10,8, nullable): Stores the latitude coordinate
- `longitude` (decimal 11,8, nullable): Stores the longitude coordinate  
- `calculated_footprint` (decimal 15,4, nullable): Stores the calculated water footprint in cubic meters

### 2. Backend Service

#### FootprintCalculatorService
- **Location**: `app/Services/FootprintCalculatorService.php`
- **Purpose**: Calculates environmental water footprint based on survey responses

**Key Methods:**

##### `calculateWaterFootprint(array $answers): ?float`
Calculates the water footprint based on survey responses.

**Calculation Methodology:**
1. **Data Extraction**: Analyzes survey answers to identify:
   - Monthly water consumption (in liters)
   - Monthly wine production volume (in liters)
   - Water reuse practices (none, partial, full)
   - Waste water discharge methods

2. **Base Calculation**: 
   - If water consumption is provided: Uses actual consumption
   - If only production volume is available: Estimates using industry average (1.5 L water per 1 L wine)
   - Converts result to cubic meters

3. **Adjustments**:
   - **Water Reuse Factor**: 
     - Full reuse: 40% reduction in footprint
     - Partial reuse: 20% reduction in footprint
   - **Discharge Impact Factor**:
     - Surface water discharge: 10% increase in footprint (environmental impact consideration)

**Constants Used:**
```php
const AVERAGE_WATER_PER_LITER_WINE = 1.5;
const REUSE_REDUCTION_FACTOR_FULL = 0.4;
const REUSE_REDUCTION_FACTOR_PARTIAL = 0.2;
const WASTE_DISCHARGE_IMPACT_FACTOR = 1.1;
```

##### `getFootprintInterpretation(float $footprint): string`
Returns a human-readable interpretation of the calculated footprint.

**Interpretation Benchmarks (cubic meters per month):**
- < 5: Low water footprint - Excellent water management
- 5-15: Moderate water footprint - Good water management
- 15-30: Average water footprint - Room for improvement
- 30-50: High water footprint - Significant improvement needed
- > 50: Very high water footprint - Urgent action required

##### `getRecommendations(float $footprint, array $answers): array`
Provides context-specific recommendations for reducing water footprint.

### 3. Backend Controller Updates

#### SurveyController Updates
- **Location**: `app/Http/Controllers/SurveyController.php`

**Modified Methods:**

##### `storeAnswer()`
Enhanced to:
1. Accept and validate GPS coordinates (latitude, longitude)
2. Store GPS coordinates with survey answers
3. Automatically calculate water footprint using FootprintCalculatorService
4. Log GPS data collection and footprint calculation results
5. Handle errors gracefully (doesn't fail if footprint calculation fails)

**Request Validation:**
- **Location**: `app/Http/Requests/StoreSurveyAnswerRequest.php`
- Added validation rules:
  - `latitude`: nullable, numeric, between -90 and 90
  - `longitude`: nullable, numeric, between -180 and 180

### 4. Frontend Implementation

#### GPS Location Collection
- **Location**: `vue/src/views/SurveyPublicView.vue`

**User Experience Flow:**
1. When user starts a survey, they see a permission request banner
2. User can choose to:
   - **Allow Location Access**: System requests GPS permission via browser Geolocation API
   - **Continue Without Location**: Proceeds without GPS data
3. If location is granted:
   - Coordinates are captured with high accuracy
   - Success message is displayed
   - Coordinates are included in survey submission
4. Location data is optional - surveys can be completed without it

**GPS Request Configuration:**
```javascript
{
  enableHighAccuracy: true,  // Request best accuracy
  timeout: 10000,            // 10 second timeout
  maximumAge: 0              // No cached positions
}
```

**Privacy Features:**
- User must explicitly consent to location sharing
- Clear explanation of why location is being collected (environmental research)
- Survey can be completed without providing location
- Location permission must be granted for each survey submission

#### Store Updates
- **Location**: `vue/src/store/index.js`
- Modified `saveSurveyAnswer` action to accept and pass GPS coordinates to backend

## Research Application

### Data Collection
This feature enables researchers to:
1. **Quantify Water Usage**: Get accurate measurements of water consumption in wine production
2. **Geographic Analysis**: Analyze regional patterns in water usage and environmental impact
3. **Identify Best Practices**: Compare producers to identify efficient water management strategies
4. **Track Improvements**: Monitor changes in water footprint over time

### Privacy and Ethics
- **Informed Consent**: Users are clearly informed about location data collection
- **Optional Participation**: Location sharing is optional, not required
- **Transparency**: Purpose of data collection is explicitly stated
- **Data Minimization**: Only essential location data (lat/long) is collected

### Data Analysis Potential
The collected data enables:
1. **Regional Footprint Mapping**: Visualize water footprint by geographic region
2. **Correlation Analysis**: Identify relationships between location, practices, and footprint
3. **Benchmark Development**: Create region-specific benchmarks for water usage
4. **Policy Recommendations**: Provide data-driven recommendations for environmental policy

## Usage Example

### For Researchers
1. Deploy the survey system
2. Create surveys focused on wine production practices
3. Include questions about:
   - Water consumption volumes
   - Production volumes
   - Water reuse practices
   - Waste water management
4. Users complete surveys with optional GPS data
5. System automatically calculates footprints
6. Export data for analysis including GPS coordinates and calculated footprints

### Survey Question Types
The system supports a "footprint" question type specifically for environmental impact assessment:
- Renders as radio buttons
- Typically used for subjective environmental impact ratings
- Example: "Rate the environmental impact of your process (1-5)"

## Technical Notes

### Logging
All GPS data collection and footprint calculations are logged for:
- Debugging purposes
- Research audit trail
- Data quality monitoring

**Log Events:**
- Survey answer creation with GPS data presence
- Successful footprint calculations with interpretation
- Footprint calculation failures (with error details)

### Error Handling
- GPS collection failures don't prevent survey submission
- Footprint calculation errors are logged but don't fail the request
- Users receive appropriate error messages for GPS-related issues

### Performance Considerations
- GPS requests have a 10-second timeout
- Footprint calculation is performed synchronously but is lightweight
- Failed calculations don't impact user experience

## Future Enhancements

Potential areas for expansion:
1. **Carbon Footprint**: Extend calculations to include carbon emissions
2. **Visualization Dashboard**: Create admin dashboard to visualize footprint data on maps
3. **Comparative Analytics**: Allow producers to compare their footprint to regional averages
4. **Automated Recommendations**: Generate personalized improvement plans
5. **Temporal Analysis**: Track footprint changes over multiple survey submissions
6. **Export Functionality**: Enable export of anonymized research data

## Maintenance

### Updating Calculation Constants
If industry standards change, update constants in `FootprintCalculatorService`:
```php
const AVERAGE_WATER_PER_LITER_WINE = 1.5;
const REUSE_REDUCTION_FACTOR_FULL = 0.4;
const REUSE_REDUCTION_FACTOR_PARTIAL = 0.2;
const WASTE_DISCHARGE_IMPACT_FACTOR = 1.1;
```

### Database Maintenance
GPS and footprint data can grow large. Consider:
- Regular database optimization
- Archiving old survey responses
- Implementing data retention policies

## Security Considerations

1. **Input Validation**: All GPS coordinates are validated server-side
2. **Data Privacy**: GPS coordinates should be handled according to privacy regulations
3. **Access Control**: Consider implementing access controls for viewing GPS data
4. **HTTPS Required**: GPS location API requires secure context (HTTPS)

## References

- Water Footprint Network: https://waterfootprint.org/
- Wine Industry Water Usage Standards (industry-specific research)
- Geolocation API Documentation: https://developer.mozilla.org/en-US/docs/Web/API/Geolocation_API

## Contact

For questions about this feature or the environmental research project, contact the development team.
