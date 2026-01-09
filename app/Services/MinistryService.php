<?php

namespace App\Services;

use App\DAO\MinistryDAO;
use App\Models\Employee;
use App\Models\Ministry;
use App\Traits\Loggable;
use Exception;
use Illuminate\Support\Facades\DB;

class MinistryService
{
    use Loggable;
    public function __construct(
        protected MinistryDAO $ministryDAO,
        protected CacheManagerService $cacheManager
    ) {}

    public function store(array $data)
    {
        $ministryData = [
            'abbreviation' => $data['abbreviation'],
            'status'       => true
        ];

        $translations = $data['translations'];

        return DB::transaction(function () use ($ministryData, $translations) {
            $ministry = $this->ministryDAO->store($ministryData, $translations);
            $this->cacheManager->clearMinistries();
            return $ministry;
        });
    }

    public function read()
    {
        return $this->cacheManager->getMinistries(
            fn() => $this->ministryDAO->read()
        );
    }

    public function readTrashed()
    {
        return $this->cacheManager->getTrashedMinstries(
            fn() => $this->ministryDAO->readTrashed()
        );
    }

    public function readOne(Ministry $ministry)
    {
        return $this->cacheManager->getMinistry(
            $ministry->id,
            fn() => $ministry
        );
    }

    public function update(Ministry $ministry, $data)
    {
        $ministryData = collect($data)
            ->only(['status', 'abbreviation'])
            ->filter(fn($value) => $value !== null)
            ->toArray();

        $translations = $data['translations'] ?? [];

        return DB::transaction(function () use ($ministry, $ministryData, $translations) {

            $updatedMinistry =  $this->ministryDAO->update($ministry, $ministryData, $translations);

            $this->cacheManager->clearMinistries();

            return $updatedMinistry;
        });
    }

    public function assignManager($ministry, $employee)
    {
        if ($ministry->id != $employee->ministry_id)
            throw new Exception(__('messages.ministry_manager_assignment_failed'), 409);

        return DB::transaction(function () use ($ministry, $employee) {
            $this->ministryDAO->assignManager($ministry, $employee->id);

            $employee->user->syncRoles(['employee', 'ministry_manager']);
            $employee->user->update(['role' => 'ministry_manager']);

            $this->cacheManager->clearMinistries();
            $this->cacheManager->clearMinistry($ministry->id);

            return $ministry->fresh();
        });
    }

    public function removeManager($ministry)
    {
        if (!$ministry->manager_id) {
            throw new Exception(__('messages.manager_removed_failed'), 409);
        }

        return DB::transaction(function () use ($ministry) {
            $employee = Employee::find($ministry->manager_id);

            if ($employee) {
                $employee->user->update(['role' => 'employee']);
                $employee->user->syncRoles(['employee']);
            }

            $this->ministryDAO->removeManager($ministry);

            $this->cacheManager->clearMinistries();
            $this->cacheManager->clearMinistry($ministry->id);

            return $ministry->fresh();
        });
    }

    public function delete(Ministry $ministry)
    {
        $this->cacheManager->clearMinistries();
        $this->cacheManager->clearMinistry($ministry->id);
        $this->cacheManager->clearMinistriesTrashed();
        return $this->ministryDAO->delete($ministry);
    }
}
