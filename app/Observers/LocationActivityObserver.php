<?php

namespace App\Observers;

use App\Models\Location;
use App\Models\LocationActivityLog;
use Illuminate\Support\Facades\Auth;

class LocationActivityObserver
{
    public function created(Location $location): void
    {
        $this->log($location, 'created');
    }

    public function updated(Location $location): void
    {
        $changes = $this->diff($location);

        if (empty($changes)) {
            return;
        }

        $this->log($location, 'updated', $changes);
    }

    public function deleted(Location $location): void
    {
        $this->log($location, 'deleted');
    }

    private function diff(Location $location): array
    {
        $changes = [];

        foreach ($location->getChanges() as $field => $new) {
            if (in_array($field, ['created_at', 'updated_at'], true)) {
                continue;
            }

            $changes[$field] = [
                'old' => $location->getOriginal($field),
                'new' => $new,
            ];
        }

        return $changes;
    }

    private function log(Location $location, string $action, ?array $changes = null): void
    {
        LocationActivityLog::create([
            'location_id' => $location->id,
            'location_name' => $location->name,
            'user_id' => Auth::id(),
            'user_name' => Auth::user()?->name ?? 'System',
            'action' => $action,
            'changes' => $changes,
        ]);
    }
}
