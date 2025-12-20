<?php

namespace Tests\Unit;

use App\Services\ProfitCalculatorService;
use PHPUnit\Framework\TestCase;

class ProfitCalculatorServiceTest extends TestCase
{
    private ProfitCalculatorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ProfitCalculatorService();
    }

    public function test_calculates_job_profit_with_no_materials(): void
    {
        $result = $this->service->calculateJobProfit(
            invoiceAmount: 1000.00,
            laborCost: 300.00,
            materials: []
        );

        $this->assertEquals(1000.00, $result['invoice_amount']);
        $this->assertEquals(300.00, $result['labor_cost']);
        $this->assertEquals(0.00, $result['material_cost']);
        $this->assertEquals(300.00, $result['total_cost']);
        $this->assertEquals(700.00, $result['gross_profit']);
        $this->assertEquals(70.00, $result['profit_margin']);
    }

    public function test_calculates_job_profit_with_materials(): void
    {
        $result = $this->service->calculateJobProfit(
            invoiceAmount: 1000.00,
            laborCost: 300.00,
            materials: [
                ['cost' => 150.00]
            ]
        );

        $this->assertEquals(1000.00, $result['invoice_amount']);
        $this->assertEquals(300.00, $result['labor_cost']);
        $this->assertEquals(150.00, $result['material_cost']);
        $this->assertEquals(450.00, $result['total_cost']);
        $this->assertEquals(550.00, $result['gross_profit']);
        $this->assertEquals(55.00, $result['profit_margin']);
    }

    public function test_calculates_job_profit_with_multiple_materials(): void
    {
        $result = $this->service->calculateJobProfit(
            invoiceAmount: 1000.00,
            laborCost: 300.00,
            materials: [
                ['cost' => 150.00],
                ['cost' => 250.00],
                ['cost' => 100.00]
            ]
        );

        $this->assertEquals(1000.00, $result['invoice_amount']);
        $this->assertEquals(300.00, $result['labor_cost']);
        $this->assertEquals(500.00, $result['material_cost']);
        $this->assertEquals(800.00, $result['total_cost']);
        $this->assertEquals(200.00, $result['gross_profit']);
        $this->assertEquals(20.00, $result['profit_margin']);
    }

    public function test_handles_zero_invoice_amount(): void
    {
        $result = $this->service->calculateJobProfit(
            invoiceAmount: 0.00,
            laborCost: 300.00,
            materials: [
                ['cost' => 150.00]
            ]
        );

        $this->assertEquals(0.00, $result['invoice_amount']);
        $this->assertEquals(300.00, $result['labor_cost']);
        $this->assertEquals(150.00, $result['material_cost']);
        $this->assertEquals(450.00, $result['total_cost']);
        $this->assertEquals(-450.00, $result['gross_profit']);
        $this->assertEquals(0.00, $result['profit_margin']); // Should not divide by zero
    }

    public function test_rounds_values_to_two_decimals(): void
    {
        $result = $this->service->calculateJobProfit(
            invoiceAmount: 1000.123,
            laborCost: 333.333,
            materials: [
                ['cost' => 150.556],
                ['cost' => 99.999]
            ]
        );

        // Verify all values are rounded to 2 decimals
        $this->assertEquals(1000.12, $result['invoice_amount']);
        $this->assertEquals(333.33, $result['labor_cost']);

        // material_cost is sum then rounded: (150.556 + 99.999) = 250.555, rounded to 250.56
        $this->assertEquals(250.56, $result['material_cost']);

        // All calculations use already-rounded values
        // total_cost = laborCost + materialCost (before rounding) = 333.333 + 250.555 = 583.888, rounds to 583.89
        $this->assertEquals(583.89, $result['total_cost']);

        // gross_profit = invoiceAmount - totalCost (before rounding) = 1000.123 - 583.888 = 416.235, rounds to 416.24
        $this->assertEquals(416.24, $result['gross_profit']);

        // profit_margin = (gross_profit_before_round / invoice_before_round) * 100 = (416.235 / 1000.123) * 100 = 41.621377, rounds to 41.62
        $this->assertEquals(41.62, $result['profit_margin']);
    }

    public function test_calculates_average_margin_for_multiple_jobs(): void
    {
        $jobs = [
            ['profit_margin' => 25.00],
            ['profit_margin' => 30.00],
            ['profit_margin' => 20.00]
        ];

        $result = $this->service->calculateAverageMargin($jobs);

        $this->assertEquals(25.00, $result);
    }

    public function test_returns_zero_average_margin_for_empty_jobs(): void
    {
        $result = $this->service->calculateAverageMargin([]);

        $this->assertEquals(0.00, $result);
    }

    public function test_profit_margin_calculation_is_percentage(): void
    {
        // Test that margin is calculated as (gross_profit / invoice) * 100
        $result = $this->service->calculateJobProfit(
            invoiceAmount: 500.00,
            laborCost: 200.00,
            materials: [
                ['cost' => 100.00]
            ]
        );

        // gross_profit = 500 - (200 + 100) = 200
        // profit_margin = (200 / 500) * 100 = 40%
        $this->assertEquals(200.00, $result['gross_profit']);
        $this->assertEquals(40.00, $result['profit_margin']);
    }
}
