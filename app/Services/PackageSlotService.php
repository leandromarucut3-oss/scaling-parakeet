<?php

namespace App\Services;

use App\Models\PackageSlotCapacity;
use App\Models\Purchase;
use InvalidArgumentException;

class PackageSlotService
{
    public function getPlanCapacity(string $planKey): int
    {
        $record = PackageSlotCapacity::query()
            ->where('plan_key', $planKey)
            ->first();

        if ($record) {
            return $record->slot_capacity;
        }

        $plans = config('investment_plans', []);

        return $plans[$planKey]['slot_capacity'] ?? 0;
    }

    public function getRemainingSlots(string $planKey): int
    {
        $capacity = $this->getPlanCapacity($planKey);
        $takenSlots = Purchase::query()
            ->where('plan_key', $planKey)
            ->count();

        return max(0, $capacity - $takenSlots);
    }

    public function getPackageSlotDetails(): array
    {
        $plans = config('investment_plans', []);
        $planKeys = array_keys($plans);

        $planCounts = Purchase::query()
            ->whereIn('plan_key', $planKeys)
            ->selectRaw('plan_key, count(*) as count')
            ->groupBy('plan_key')
            ->pluck('count', 'plan_key')
            ->all();

        return collect($plans)
            ->mapWithKeys(function (array $plan, string $planKey) use ($planCounts) {
                $capacity = $this->getPlanCapacity($planKey);
                $taken = (int) ($planCounts[$planKey] ?? 0);

                return [$planKey => [
                    'plan_key' => $planKey,
                    'name' => $plan['name'] ?? ucfirst($planKey),
                    'slot_capacity' => $capacity,
                    'taken_slots' => $taken,
                    'remaining_slots' => max(0, $capacity - $taken),
                ]];
            })
            ->all();
    }

    public function setRemainingSlots(string $planKey, int $remainingSlots): PackageSlotCapacity
    {
        $plans = config('investment_plans', []);

        if (! isset($plans[$planKey])) {
            throw new InvalidArgumentException('Invalid plan key: '.$planKey);
        }

        $remainingSlots = max(0, $remainingSlots);
        $takenSlots = Purchase::query()
            ->where('plan_key', $planKey)
            ->count();

        $capacity = $takenSlots + $remainingSlots;

        return PackageSlotCapacity::query()->updateOrCreate(
            ['plan_key' => $planKey],
            ['slot_capacity' => $capacity]
        );
    }
}
