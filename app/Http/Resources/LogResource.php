<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LogResource extends JsonResource
{
    public function toArray($request)
    {
        $changes = [];
        if ($this->event === 'updated' && isset($this->properties['old'])) {
            $newAttr = $this->properties['attributes'] ?? [];
            $oldAttr = $this->properties['old'] ?? [];

            foreach ($newAttr as $key => $value) {
                if (isset($oldAttr[$key]) && $oldAttr[$key] !== $value) {
                    $changes[$key] = [
                        'from' => $oldAttr[$key],
                        'to' => $value
                    ];
                }
            }
        }

        return [
            'id' => $this->id,
            'timestamp' => $this->created_at->format('Y-m-d H:i:s'),
            'action' => $this->description,
            'subject' => [
                'type' => $this->subject_type,
                'id' => $this->subject_id,
                'reference' => $this->properties['attributes']['reference_number'] ?? 'N/A',
            ],
            'performed_by' => $this->causer ? [
                'name' => $this->causer->first_name . ' ' . $this->causer->last_name,
                'role' => $this->causer->role,
            ] : 'System',
            'changes' => $changes,
        ];
    }

    protected function getSubjectTypeNameAttribute()
    {
        return class_basename($this->subject_type);
    }
}
