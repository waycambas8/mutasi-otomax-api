<?php
namespace App\Http\Transformer\DetailTransformers;
use League\Fractal\TransformerAbstract;

class DetailTransformers extends TransformerAbstract
{
    public function transform($model){
        return [
            $model
        ];
    }
}