<?php

namespace Modules\Accounts\Transformers\Scope;

use Illuminate\Http\Resources\Json\JsonResource;

class ScopeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
                'constants_scope_id' => $this->id,
                'name' => $this->name,
                'description' =>  $this->description,
                'scope_created_at_for_humans' => $this->created_at->format('d-m-Y H:i'),
                'scope_created_at' => $this->created_at,
                'links' => [
                    'self' => $this->path()
                ]
        ];
    }
}
