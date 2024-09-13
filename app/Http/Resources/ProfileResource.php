<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
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
            'cui' => $this->cui,
            'apellidos' => $this->apellidos,          
            'nombres' => $this->nombres, 
            'tipo_documento' => $this->tipo_documento,
            'nro_documento' => $this->nro_documento,
            'programas' => $this->programs,
            /* 'programas' => $this->programs->transform(function($item) {
                return [
                    'programa' => $item->nombre,
                    //'facultad' => $item->faculty,                               
                ];
            }), */
        ];
    }
}
