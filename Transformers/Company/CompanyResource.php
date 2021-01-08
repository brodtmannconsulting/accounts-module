<?php

namespace Modules\Accounts\Transformers\Company;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Accounts\Transformers\Role\RoleResource;

class CompanyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => [
                'company_id' => $this->id,
                'name' => $this->name,
                'description' => $this->description,
                'company_website' => $this->company_website,
                'roles' => RoleResource::collection ($this->roles),
                'created_at' => $this->created_at->format('d-m-Y H:i'),
            ],
            'links' => [
                'self' => $this->path()
            ]
        ];
    }
}
