<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use DateTime;


class GradeCollection extends ResourceCollection
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
            'data' => $this->collection->map(function ($grade) {
                return [
                    'asignatura' => $grade->nasi,
                    'nota' => $grade->nota,
                    'nro_matricula' => $grade->matr,
                    'periodo' => $grade->anoh . '-' . $grade->cicl,
                    'fecha' => DateTime::createFromFormat('Ymd', $grade->fech)->format('Y-m-d'),
                ];
            }),
            'meta' => [
                'total_grades' => $this->collection->count(),
            ],
        ];
    }
}
