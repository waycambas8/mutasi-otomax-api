<?php
namespace App\Http\Transformer\UserTransformers;
use League\Fractal\TransformerAbstract;

class UserTransformerMiddleware extends TransformerAbstract
{
    public function transform($model){
        return [
            "msg" => $model['msg'],
            "response" => $model['response']
        ];
    }
}