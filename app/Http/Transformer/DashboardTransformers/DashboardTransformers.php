<?php
namespace App\Http\Transformer\DashboardTransformers;
use League\Fractal\TransformerAbstract;

class DashboardTransformers extends TransformerAbstract
{
    public function transform($model){
        return [
            $model
        ];
    }
}