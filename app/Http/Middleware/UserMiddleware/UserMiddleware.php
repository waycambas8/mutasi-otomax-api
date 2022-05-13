<?php

namespace App\Http\Middleware\UserMiddleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Middleware\BaseMiddleware;
use Illuminate\Support\Facades\Validator;
use App\Http\Transformer\UserTransformers\UserTransformerMiddleware;
use Carbon\Carbon;

use App\Models\Users;

class UserMiddleware extends BaseMiddleware
{
    private function Instantiate(){
        $this->Model->user = Users::where("kode",$this->_Request->kode)->first();
        if($this->Model->user){
            $this->Model->kode = $this->_Request->kode;
            $this->Model->nama = $this->Model->user->nama;
            $this->Model->pin = $this->Model->user->pin;
            $this->Model->tgl_daftar = $this->Model->user->tgl_daftar;
            $this->Model->token = bcrypt(base64_encode($this->Model->kode.":".$this->Model->pin.":".Carbon::now()));
            $this->Model->token_date = Carbon::now()->format("Y-m-d h:i:s");
            $this->Model->expired_token = Carbon::now()->addDay(1)->format("Y-m-d h:i:s");
            $this->Model->ip = $this->_Request->ip();
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
            $this->Payload->put('Model', $this->Model);
            $this->_Request->merge(['Payload' => $this->Payload]);

            return $next($this->_Request);
        }else{
            return response()->json(fractal()->collection($this->msg)->transformWith(new UserTransformerMiddleware),$this->HttpCode);
        }
    }
}
