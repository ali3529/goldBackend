<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'fullname' => $this->fullname,
            'phone_number' => $this->phone_number,
            'national_code' => $this->national_code,
            'status' => $this->status,
            'address' => $this->address,
            'card_number' => $this->card_number,
            'tell_number' => $this->tell_number,
            'wallet_t' => $this->wallet_t,
            'wallet_g' => $this->wallet_g,
        ];
    }
}
