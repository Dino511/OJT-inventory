<?php

namespace App\Observers;

use App\Models\Inventory;
use App\Models\InventoryActivityLog;
use Illuminate\Support\Facades\Auth;

class InventoryActivityObserver
{
    public function created(Inventory $inventory): void
    {
        $this->log($inventory, 'created');
    }

    public function updated(Inventory $inventory): void
    {
        $changes = $this->diff($inventory);

        if (empty($changes)) {
            return;
        }

        $this->log($inventory, 'updated', $changes);
    }

    public function deleted(Inventory $inventory): void
    {
        $this->log($inventory, 'deleted');
    }

    /**
     * Field-by-field old/new diff, skipping timestamps.
     */
    private function diff(Inventory $inventory): array
    {
        $changes = [];

        foreach ($inventory->getChanges() as $field => $new) {
            if (in_array($field, ['created_at', 'updated_at'], true)) {
                continue;
            }

            $changes[$field] = [
                'old' => $inventory->getOriginal($field),
                'new' => $new,
            ];
        }

        return $changes;
    }

    private function log(Inventory $inventory, string $action, ?array $changes = null): void
    {
        InventoryActivityLog::create([
            'inventory_id' => $inventory->id,
            'product_name' => $inventory->product->name ?? 'N/A',
            'product_code' => $inventory->product->code ?? null,
            'location_name' => $inventory->location->name ?? 'N/A',
            'user_id' => Auth::id(),
            'user_name' => Auth::user()?->name ?? 'System',
            'action' => $action,
            'changes' => $changes,
        ]);
    }
}
