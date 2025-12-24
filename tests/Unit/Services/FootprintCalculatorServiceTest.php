<?php

namespace Tests\Unit\Services;

use App\Services\FootprintCalculatorService;
use Tests\TestCase;

class FootprintCalculatorServiceTest extends TestCase
{
    private FootprintCalculatorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FootprintCalculatorService();
    }

    /**
     * Test basic footprint calculation with water consumption data
     */
    public function test_calculate_footprint_with_water_consumption()
    {
        $answers = [
            1 => 'Test Producer',
            2 => '10000', // 10,000 liters of water per month
            3 => '5000',  // 5,000 liters of wine per month
        ];

        $footprint = $this->service->calculateWaterFootprint($answers);

        // 10,000 liters = 10 cubic meters
        $this->assertNotNull($footprint);
        $this->assertEquals(10.0, $footprint);
    }

    /**
     * Test footprint calculation with water reuse (full)
     */
    public function test_calculate_footprint_with_full_reuse()
    {
        $answers = [
            1 => 'Test Producer',
            2 => '10000', // 10,000 liters of water
            3 => 'Sí, totalmente', // Full water reuse
        ];

        $footprint = $this->service->calculateWaterFootprint($answers);

        // 10,000 liters = 10 cubic meters, with 40% reduction = 6 cubic meters
        $this->assertNotNull($footprint);
        $this->assertEquals(6.0, $footprint);
    }

    /**
     * Test footprint calculation with water reuse (partial)
     */
    public function test_calculate_footprint_with_partial_reuse()
    {
        $answers = [
            1 => 'Test Producer',
            2 => '10000', // 10,000 liters of water
            3 => 'Sí, parcialmente', // Partial water reuse
        ];

        $footprint = $this->service->calculateWaterFootprint($answers);

        // 10,000 liters = 10 cubic meters, with 20% reduction = 8 cubic meters
        $this->assertNotNull($footprint);
        $this->assertEquals(8.0, $footprint);
    }

    /**
     * Test footprint calculation with surface water discharge
     */
    public function test_calculate_footprint_with_surface_discharge()
    {
        $answers = [
            1 => 'Test Producer',
            2 => '10000', // 10,000 liters of water
            3 => 'Cuerpo de agua superficial', // Surface water discharge
        ];

        $footprint = $this->service->calculateWaterFootprint($answers);

        // 10,000 liters = 10 cubic meters, with 10% increase = 11 cubic meters
        $this->assertNotNull($footprint);
        $this->assertEquals(11.0, $footprint);
    }

    /**
     * Test footprint calculation with insufficient data
     */
    public function test_calculate_footprint_with_insufficient_data()
    {
        $answers = [
            1 => 'Test Producer',
            2 => 'Some text answer',
        ];

        $footprint = $this->service->calculateWaterFootprint($answers);

        $this->assertNull($footprint);
    }

    /**
     * Test footprint interpretation for low footprint
     */
    public function test_get_interpretation_low_footprint()
    {
        $interpretation = $this->service->getFootprintInterpretation(3.0);
        
        $this->assertStringContainsString('Low', $interpretation);
        $this->assertStringContainsString('Excellent', $interpretation);
    }

    /**
     * Test footprint interpretation for moderate footprint
     */
    public function test_get_interpretation_moderate_footprint()
    {
        $interpretation = $this->service->getFootprintInterpretation(10.0);
        
        $this->assertStringContainsString('Moderate', $interpretation);
        $this->assertStringContainsString('Good', $interpretation);
    }

    /**
     * Test footprint interpretation for high footprint
     */
    public function test_get_interpretation_high_footprint()
    {
        $interpretation = $this->service->getFootprintInterpretation(40.0);
        
        $this->assertStringContainsString('High', $interpretation);
        $this->assertStringContainsString('improvement', $interpretation);
    }

    /**
     * Test footprint interpretation for very high footprint
     */
    public function test_get_interpretation_very_high_footprint()
    {
        $interpretation = $this->service->getFootprintInterpretation(60.0);
        
        $this->assertStringContainsString('Very high', $interpretation);
        $this->assertStringContainsString('Urgent', $interpretation);
    }

    /**
     * Test recommendations generation
     */
    public function test_get_recommendations()
    {
        $answers = [
            1 => 'No', // No water reuse
        ];

        $recommendations = $this->service->getRecommendations(20.0, $answers);
        
        $this->assertIsArray($recommendations);
        $this->assertNotEmpty($recommendations);
        $this->assertStringContainsString('water reuse', $recommendations[0]);
    }

    /**
     * Test recommendations for high footprint
     */
    public function test_get_recommendations_for_high_footprint()
    {
        $answers = [
            1 => 'Sí', // Has water reuse
        ];

        $recommendations = $this->service->getRecommendations(35.0, $answers);
        
        $this->assertIsArray($recommendations);
        $this->assertGreaterThan(1, count($recommendations));
    }
}
