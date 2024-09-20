<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use DateTime;

class EnrollPaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'fecha_pago' => DateTime::createFromFormat('Ymd', $this->fecha_pago)->format('Y-m-d'),
            //'cajero' => $this->cajero,
            'monto_pagado' => $this->monto_pagado,          
        ];
    }
}
