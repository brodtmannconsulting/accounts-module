<?php

namespace Modules\Accounts\Transformers\Auth;

use Illuminate\Http\Resources\Json\JsonResource;

class TokenResource extends JsonResource
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
            'data' => [
                'user_id' => $this->user ()->first ()->user->id,
                'company_id' => $this->user ()->first ()->user->company_id,
                'revoked' => $this->revoked,
            ]
        ];
    }
}
