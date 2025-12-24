<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * FootprintCalculatorService
 * 
 * This service calculates environmental footprints for surveys focused on wine production.
 * It's designed for environmental research projects to measure water usage impact.
 * 
 * The calculation is based on water consumption data collected from wine producers,
 * considering factors like:
 * - Monthly water consumption for wine production
 * - Monthly wine production volume
 * - Water reuse practices
 * - Waste water discharge methods
 * 
 * @package App\Services
 * @author Environmental Research Project
 * @version 1.0.0
 */
class FootprintCalculatorService
{
    /**
     * Water footprint calculation constants
     * Based on research standards for wine production water footprint
     */
    const AVERAGE_WATER_PER_LITER_WINE = 1.5; // liters of water per liter of wine (industry average)
    const REUSE_REDUCTION_FACTOR_FULL = 0.4; // 40% reduction if water is fully reused
    const REUSE_REDUCTION_FACTOR_PARTIAL = 0.2; // 20% reduction if water is partially reused
    const WASTE_DISCHARGE_IMPACT_FACTOR = 1.1; // 10% increase if discharge is to surface water
    
    /**
     * Configuration for answer pattern matching
     * These patterns are used to identify answer types from survey responses.
     * Note: Currently configured for Spanish language surveys.
     * TODO: Implement internationalization or question metadata-based approach
     */
    const WATER_REUSE_PATTERNS = [
        'full' => ['totalmente', 'Sí, totalmente', 'completely', 'yes, completely'],
        'partial' => ['parcialmente', 'Sí, parcialmente', 'partially', 'yes, partially']
    ];
    
    const DISCHARGE_METHOD_PATTERNS = [
        'surface_water' => ['superficial', 'Cuerpo de agua superficial', 'surface water']
    ];
    
    /**
     * Heuristic thresholds for numeric answer detection
     * These values help distinguish between different types of numeric answers.
     * Note: This is a fallback approach when question metadata is not available.
     * 
     * WATER_CONSUMPTION_MIN_THRESHOLD: The minimum value (in liters) to identify 
     * an answer as water consumption rather than wine production. This threshold
     * is based on typical production scales where:
     * - Wine production is typically measured in hundreds of liters
     * - Water consumption is typically measured in thousands of liters
     * 
     * Limitation: This heuristic may fail if:
     * - Small producers have water consumption < 1000 liters
     * - Large producers have wine production > 1000 liters
     * 
     * TODO: Consider making this configurable via environment variables or
     * implementing a question metadata/tagging system for more reliable identification.
     */
    const WATER_CONSUMPTION_MIN_THRESHOLD = 1000; // Minimum value to identify as water consumption (liters)
    
    /**
     * Calculate the water footprint based on survey answers
     * 
     * This method analyzes survey responses to determine the water consumption
     * impact of wine production. The calculation considers:
     * 
     * 1. Direct water consumption (Question about monthly water usage)
     * 2. Wine production volume (Question about monthly wine production)
     * 3. Water reuse practices (Question about water reuse)
     * 4. Waste water discharge method (Question about discharge location)
     * 
     * @param array $answers Array of survey question answers
     * @return float|null The calculated water footprint value in cubic meters, or null if insufficient data
     */
    public function calculateWaterFootprint(array $answers): ?float
    {
        try {
            // Initialize calculation variables
            $waterConsumption = null; // liters per month
            $wineProduction = null;   // liters per month
            $waterReuse = 'No';       // Default: no reuse
            $dischargeMethod = null;
            
            // Extract relevant values from survey answers
            // Note: This uses heuristic-based detection. For production use, consider:
            // - Using question metadata/tags to identify question types
            // - Configuring specific question IDs for each data point
            // - Implementing a question type system in the database
            foreach ($answers as $questionId => $answer) {
                // Process numeric answers
                if (is_numeric($answer)) {
                    $numericValue = floatval($answer);
                    
                    // Use threshold-based heuristic to distinguish answer types
                    // Values above threshold are likely water consumption (in liters)
                    // Values below threshold are likely wine production (in liters)
                    if ($waterConsumption === null && $numericValue >= self::WATER_CONSUMPTION_MIN_THRESHOLD) {
                        $waterConsumption = $numericValue;
                    } elseif ($wineProduction === null && $numericValue > 0 && $numericValue < self::WATER_CONSUMPTION_MIN_THRESHOLD) {
                        $wineProduction = $numericValue;
                    }
                }
                
                // Process string answers for water reuse and discharge method
                if (is_string($answer)) {
                    // Check water reuse patterns
                    foreach (self::WATER_REUSE_PATTERNS['full'] as $pattern) {
                        if (stripos($answer, $pattern) !== false) {
                            $waterReuse = 'Full';
                            break;
                        }
                    }
                    if ($waterReuse === 'No') {
                        foreach (self::WATER_REUSE_PATTERNS['partial'] as $pattern) {
                            if (stripos($answer, $pattern) !== false) {
                                $waterReuse = 'Partial';
                                break;
                            }
                        }
                    }
                    
                    // Check discharge method patterns
                    foreach (self::DISCHARGE_METHOD_PATTERNS['surface_water'] as $pattern) {
                        if (stripos($answer, $pattern) !== false) {
                            $dischargeMethod = 'surface_water';
                            break;
                        }
                    }
                }
            }
            
            // If we have insufficient data, return null
            if ($waterConsumption === null && $wineProduction === null) {
                Log::info('FootprintCalculator: Insufficient data for calculation');
                return null;
            }
            
            // Calculate base footprint
            // Start with actual consumption if available, otherwise estimate from production
            if ($waterConsumption !== null) {
                $baseFootprint = $waterConsumption / 1000; // Convert to cubic meters
            } elseif ($wineProduction !== null) {
                $baseFootprint = ($wineProduction * self::AVERAGE_WATER_PER_LITER_WINE) / 1000; // Convert to cubic meters
            } else {
                return null;
            }
            
            // Apply reuse reduction
            $footprint = $baseFootprint;
            if ($waterReuse === 'Full') {
                $footprint *= (1 - self::REUSE_REDUCTION_FACTOR_FULL);
            } elseif ($waterReuse === 'Partial') {
                $footprint *= (1 - self::REUSE_REDUCTION_FACTOR_PARTIAL);
            }
            
            // Apply discharge impact
            if ($dischargeMethod === 'surface_water') {
                $footprint *= self::WASTE_DISCHARGE_IMPACT_FACTOR;
            }
            
            Log::info('FootprintCalculator: Calculated footprint', [
                'water_consumption' => $waterConsumption,
                'wine_production' => $wineProduction,
                'water_reuse' => $waterReuse,
                'discharge_method' => $dischargeMethod,
                'footprint' => $footprint
            ]);
            
            return round($footprint, 4);
            
        } catch (\Exception $e) {
            Log::error('FootprintCalculator: Error calculating footprint', [
                'error' => $e->getMessage(),
                'answers' => $answers
            ]);
            return null;
        }
    }
    
    /**
     * Get a human-readable interpretation of the footprint value
     * 
     * This provides context for the calculated footprint value based on
     * industry benchmarks for wine production water usage.
     * 
     * @param float $footprint The calculated footprint in cubic meters
     * @return string A human-readable interpretation
     */
    public function getFootprintInterpretation(float $footprint): string
    {
        // Benchmarks based on research (cubic meters per month)
        // These are approximate values for small to medium wine producers
        if ($footprint < 5) {
            return 'Low water footprint - Excellent water management';
        } elseif ($footprint < 15) {
            return 'Moderate water footprint - Good water management';
        } elseif ($footprint < 30) {
            return 'Average water footprint - Room for improvement';
        } elseif ($footprint < 50) {
            return 'High water footprint - Significant improvement needed';
        } else {
            return 'Very high water footprint - Urgent action required';
        }
    }
    
    /**
     * Get recommendations based on the calculated footprint
     * 
     * @param float $footprint The calculated footprint in cubic meters
     * @param array $answers Survey answers for context-specific recommendations
     * @return array An array of recommendation strings
     */
    public function getRecommendations(float $footprint, array $answers): array
    {
        $recommendations = [];
        
        // Check if water reuse is implemented
        $hasWaterReuse = false;
        foreach ($answers as $answer) {
            if (is_string($answer) && (stripos($answer, 'Sí') !== false || stripos($answer, 'totalmente') !== false || stripos($answer, 'parcialmente') !== false)) {
                $hasWaterReuse = true;
                break;
            }
        }
        
        if (!$hasWaterReuse) {
            $recommendations[] = 'Implement water reuse systems to reduce fresh water consumption';
        }
        
        if ($footprint > 15) {
            $recommendations[] = 'Consider installing water-efficient cleaning equipment';
            $recommendations[] = 'Monitor and reduce water usage in bottling and cleaning processes';
        }
        
        if ($footprint > 30) {
            $recommendations[] = 'Conduct a comprehensive water audit to identify major consumption points';
            $recommendations[] = 'Invest in closed-loop water systems for temperature control';
        }
        
        $recommendations[] = 'Continue monitoring water usage to track improvements over time';
        
        return $recommendations;
    }
}
