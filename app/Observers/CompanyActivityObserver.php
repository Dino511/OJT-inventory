<?php

namespace App\Observers;

use App\Models\Company;
use App\Models\CompanyActivityLog;
use Illuminate\Support\Facades\Auth;

class CompanyActivityObserver
{
    public function created(Company $company): void
    {
        $this->log($company, 'created');
    }

    public function updated(Company $company): void
    {
        $changes = $this->diff($company);

        if (empty($changes)) {
            return;
        }

        $this->log($company, 'updated', $changes);
    }

    public function deleted(Company $company): void
    {
        $this->log($company, 'deleted');
    }

    private function diff(Company $company): array
    {
        $changes = [];

        foreach ($company->getChanges() as $field => $new) {
            if (in_array($field, ['created_at', 'updated_at'], true)) {
                continue;
            }

            $changes[$field] = [
                'old' => $company->getOriginal($field),
                'new' => $new,
            ];
        }

        return $changes;
    }

    private function log(Company $company, string $action, ?array $changes = null): void
    {
        CompanyActivityLog::create([
            'company_id' => $company->company_id,
            'company_name' => $company->name,
            'user_id' => Auth::id(),
            'user_name' => Auth::user()?->name ?? 'System',
            'action' => $action,
            'changes' => $changes,
        ]);
    }
}
