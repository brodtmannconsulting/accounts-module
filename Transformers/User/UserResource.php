<?php

namespace Modules\Accounts\Transformers\User;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Accounts\Transformers\Company\CompanyResource;
use Modules\Accounts\Transformers\Credential\CredentialResource;
use Modules\Accounts\Transformers\Role\RoleResource;
use Modules\Accounts\Transformers\Scope\ScopeResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if($this->allow_log_in == 1){
            $allow_log_in_for_humans = 'Ja';
        }else{
            $allow_log_in_for_humans = 'Nein';
        }

        return [
            'data' => [
                'user_id' => $this->id,
                'first_name' => decrypt ($this->first_name),
                'last_name' => decrypt ($this->last_name),
                'language' => $this->language,
                'allow_log_in' => $this->allow_log_in,
                'allow_log_in_for_humans' => $allow_log_in_for_humans,
                'user_created_at_for_humans' => $this->created_at->format('d-m-Y H:i'),
                'user_created_at' => $this->created_at,
                'credentials' => CredentialResource::collection ($this->credentials),
                'company' => new CompanyResource($this->company),
                'roles' =>  RoleResource::collection($this->roles),
                'scopes' => $this->getScopes(),
                'scopes_collection' => ScopeResource::collection ($this->getScopesCollection())
                ],
            'links' => [
                'self' => $this->path()
            ]
        ];

    }
}
