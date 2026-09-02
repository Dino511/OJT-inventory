<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\ProductActivityLog;
use Illuminate\Support\Facades\Auth;

class ProductActivityObserver
{
    public function created(Product $product): void
    {
        $this->log($product, 'created');
    }

    public function updated(Product $product): void
    {
        $changes = $this->diff($product);

        if (empty($changes)) {
            return;
        }

        $this->log($product, 'updated', $changes);
    }

    public function deleted(Product $product): void
    {
        $this->log($product, 'deleted');
    }

    /**
     * Field-by-field old/new diff, skipping timestamps.
     */
    private function diff(Product $product): array
    {
        $changes = [];

        foreach ($product->getChanges() as $field => $new) {
            if (in_array($field, ['created_at', 'updated_at'], true)) {
                continue;
            }

            $changes[$field] = [
                'old' => $product->getOriginal($field),
                'new' => $new,
            ];
        }

        return $changes;
    }

    private function log(Product $product, string $action, ?array $changes = null): void
    {
        ProductActivityLog::create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_code' => $product->code,
            'user_id' => Auth::id(),
            'user_name' => Auth::user()?->name ?? 'System',
            'action' => $action,
            'changes' => $changes,
        ]);
    }
}
