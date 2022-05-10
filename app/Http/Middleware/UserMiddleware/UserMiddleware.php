<?php

namespace App\Http\Middleware\UserMiddleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Middleware\BaseMiddleware;
use Illuminate\Support\Facades\Validator;
use App\Http\Transformer\UserTransformers\UserTransformerMiddleware;

use App\Models\Users;

class UserMiddleware extends BaseMiddleware
{
    private function Instantiate(){
        $this->Model->user = Users::where("kode",$this->_Request->kode)->first();
        if($this->Model->user){
            $this->Model->kode = $this->_Request->kode;
            $this->Model->nama = $this->Model->user->nama;
            $this->Model->daftar = $this->Model->user->tgl_daftar;
        }
    }

    public function validation(){
        $validator = Validator::make($this->_Request->all(), [
            'kode' => 'required|max:255',
            'pin' => 'required',
        ]);
 
        if ($validator->fails()) {
            $this->msg = array(array("msg" => $validator->messages()->toArray()));
            return false;
        }

        if(!$this->Model->user){
            $this->msg = [
                [
                    "msg" => "User not found"
                ]
            ];
            return false;
        }

        if(!$this->Model->login = Users::where("kode",$this->_Request->kode)->where("pin",$this->_Request->pin)->exists()){
            $this->msg = array(array("msg" => "Code or Pin is wrong"));
            return false;
        }
        return true;
    }

    public function handle(Request $request, Closure $next)
    {
        $this->Instantiate();
        if($this->validation()){
            return $next($request);
        }else{
            return response()->json(fractal()->collection($this->msg)->transformWith(new UserTransformerMiddleware)->toArray(),$this->HttpCode);
        }
    }
}
