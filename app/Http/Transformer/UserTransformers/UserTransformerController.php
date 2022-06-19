<?php
namespace App\Http\Transformer\UserTransformers;
use League\Fractal\TransformerAbstract;

class UserTransformerController extends TransformerAbstract
{
    public function transform($model){
        return [
            "kode" => $model->kode,
            "nama" => $model->nama,
            "daftar" => $model->tgl_daftar,
            "token" => $model->token,
            "token_date" => $model->token_date,
            "expired_token" => $model->expired_token,
            "ip" => $model->ip,
            "pin" => $model->pin,
            "phone" => $model->phone,
            "status" => $model->status,
            "response" => $model->response

        ];
    }
}