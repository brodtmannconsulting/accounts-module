<?php

namespace Modules\Accounts\Transformers\Company;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
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
        if ($this->avatar_url) {
            $avatar = Storage::url($this->avatar_url);
        } else {
            $avatar = null;
        }
        return [
            'data' => [
                'company_id' => $this->id,
                'name' => $this->name,
                'description' => $this->description,
                'company_website' => $this->company_website,
                'roles' => RoleResource::collection ($this->roles),
                'created_at' => $this->created_at->format('d-m-Y H:i'),
                'avatar' => $avatar
            ],
            'links' => [
                'self' => $this->path()
            ]
        ];
    }
}
