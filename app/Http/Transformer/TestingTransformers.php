<?php
namespace App\Http\Transformer;
use League\Fractal\TransformerAbstract;

class TestingTransformers extends TransformerAbstract
{
    public function transform($model){
        return [
            "id" => $model['id']
        ];
    }
}