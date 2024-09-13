<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ProgramCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($program) {
                return [
                    'nues' => $program['nues'],
                    'espe' => $program['espe'],
                    'programa' => $program['nombre'],
                ];
            }),
            'meta' => [
                'total_programas' => $this->collection->count(),
            ],
        ];
    }
}
