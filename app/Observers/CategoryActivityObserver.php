<?php

namespace App\Observers;

use App\Models\CategoryActivityLog;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\Auth;

class CategoryActivityObserver
{
    public function created(ProductCategory $category): void
    {
        $this->log($category, 'created');
    }

    public function updated(ProductCategory $category): void
    {
        $changes = $this->diff($category);

        if (empty($changes)) {
            return;
        }

        $this->log($category, 'updated', $changes);
    }

    public function deleted(ProductCategory $category): void
    {
        $this->log($category, 'deleted');
    }

    private function diff(ProductCategory $category): array
    {
        $changes = [];

        foreach ($category->getChanges() as $field => $new) {
            if (in_array($field, ['created_at', 'updated_at'], true)) {
                continue;
            }

            $changes[$field] = [
                'old' => $category->getOriginal($field),
                'new' => $new,
            ];
        }

        return $changes;
    }

    private function log(ProductCategory $category, string $action, ?array $changes = null): void
    {
        CategoryActivityLog::create([
            'category_id' => $category->id,
            'category_name' => $category->name,
            'user_id' => Auth::id(),
            'user_name' => Auth::user()?->name ?? 'System',
            'action' => $action,
            'changes' => $changes,
        ]);
    }
}
