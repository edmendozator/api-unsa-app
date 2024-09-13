<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class EnrollCollection extends ResourceCollection
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
            'data' => $this->collection->map(function ($enroll) {
                return [
                    'codigo_asignatura' => $enroll->casi,
                    'asignatura' => $enroll->nasi,
                    'creditos' => $enroll->cred,
                    'grupo' => $enroll->grup,
                    'nro_matricula' => $enroll->matr,
                ];
            }),
            'meta' => [
                'total_matriculas' => $this->collection->count(),
            ],
        ];
    }
}
