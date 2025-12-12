<?php

namespace App\Services;

class ProfitCalculatorService
{
    public function calculateJobProfit(
        float $invoiceAmount,
        float $laborCost,
        array $materials
    ): array {
        $materialCost = $this->sumMaterialCosts($materials);
        $totalCost = $laborCost + $materialCost;
        $grossProfit = $invoiceAmount - $totalCost;
        $profitMargin = $invoiceAmount > 0
            ? ($grossProfit / $invoiceAmount) * 100
            : 0;

        return [
            'invoice_amount' => $this->round($invoiceAmount),
            'labor_cost' => $this->round($laborCost),
            'material_cost' => $this->round($materialCost),
            'total_cost' => $this->round($totalCost),
            'gross_profit' => $this->round($grossProfit),
            'profit_margin' => $this->round($profitMargin)
        ];
    }

    public function calculateAverageMargin(array $jobs): float
    {
        if (empty($jobs)) {
            return 0;
        }

        $totalMargin = array_sum(array_column($jobs, 'profit_margin'));
        $averageMargin = $totalMargin / count($jobs);

        return $this->round($averageMargin);
    }

    private function sumMaterialCosts(array $materials): float
    {
        return array_sum(array_column($materials, 'cost'));
    }

    private function round(float $value): float
    {
        return round($value, 2);
    }
}
