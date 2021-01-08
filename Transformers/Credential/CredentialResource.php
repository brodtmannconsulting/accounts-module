<?php

namespace Modules\Accounts\Transformers\Credential;

use Illuminate\Http\Resources\Json\JsonResource;

class CredentialResource extends JsonResource
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
                'credential_id' => $this->id,
                'username' => decrypt ($this->AES_256_username),
                'username_type' => $this->username_type,
                'valid_from_for_humans' => $this->valid_from->format('d-m-Y H:i'),
                'valid_from' => $this->valid_from->format('d-m-Y H:i'),
                'valid_until_for_humans' => $this->valid_until->format('d-m-Y H:i'),
                'valid_until' => $this->valid_until->format('d-m-Y H:i'),
                'created_at_for_humans' => $this->created_at->format('d-m-Y H:i'),
                'created_at' => $this->created_at->format('d-m-Y H:i'),
            ],
            'links' => [
                'self' => $this->path()
            ]
        ];
    }
}
