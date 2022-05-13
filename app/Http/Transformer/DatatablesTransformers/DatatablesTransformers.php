<?php
namespace App\Http\Transformer\DatatablesTransformers;
use League\Fractal\TransformerAbstract;

class DatatablesTransformers extends TransformerAbstract
{
    public function transform($model){
        return [
            $model
        ];
    }
}