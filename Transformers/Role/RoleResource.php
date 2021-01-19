<?php

namespace Modules\Accounts\Transformers\Role;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Accounts\Transformers\Scope\ScopeResource;

class RoleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @param $company
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => [
                'role_id' => $this->id,
                'name' => $this->name,
                'description' =>  $this->description,
                'is_custom' => $this->is_custom,
                'role_created_at_for_humans' => $this->created_at->format('d-m-Y H:i'),
                'role_created_at' => $this->created_at->format('d-m-Y H:i'),
                'scopes' => ScopeResource::collection ($this->scopes),
            ],
            'links' => [
                'self' => $this->path()
            ]
        ];
    }
}
