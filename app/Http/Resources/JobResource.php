<?php

namespace App\Http\Resources;

use App\Services\ProfitCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $calculator = app(ProfitCalculatorService::class);

        $materials = $this->relationLoaded('materials')
            ? $this->materials->map(fn($m) => [
                'id' => $m->id,
                'name' => $m->name,
                'cost' => (float) $m->cost,
                'created_at' => $m->created_at,
                'updated_at' => $m->updated_at,
            ])->toArray()
            : [];

        $profitData = $calculator->calculateJobProfit(
            (float) $this->invoice_amount,
            $this->labor_cost,
            $materials
        );

        return array_merge([
            'id' => $this->id,
            'job_type' => $this->job_type,
            'client_name' => $this->client_name,
            'invoice_amount' => (float) $this->invoice_amount,
            'labor_hours' => $this->labor_hours,
            'labor_rate' => (float) $this->labor_rate,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'materials' => $materials,
        ], $profitData);
    }
}
