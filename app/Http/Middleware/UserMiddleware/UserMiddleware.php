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
        $this->Model->user = Users::join("pengirim","pengirim.kode_reseller","reseller.kode")->where("reseller.kode",$this->_Request->kode)->orWhere('pengirim.pengirim',$this->_Request->kode)->first();
        if($this->Model->user){
            $this->Model->kode = $this->Model->user->kode;
            $this->Model->nama = $this->Model->user->nama;
            $this->Model->pin = $this->Model->user->pin;
            $this->Model->tgl_daftar = $this->Model->user->tgl_daftar;
            $this->Model->token = bcrypt(base64_encode($this->Model->kode.":".$this->Model->pin.":".Carbon::now()));
            $this->Model->token_date = Carbon::now()->format("Y-m-d h:i:s");
            $this->Model->expired_token = Carbon::now()->addDay(1)->format("Y-m-d h:i:s");
            $this->Model->ip = $this->_Request->ip;
            $this->Model->response = $this->code['success'];
            $this->Model->phone = $this->Model->user->pengirim;
            $this->Model->status = "ready";
        }
    }

    public function validation(){
        $validator = Validator::make($this->_Request->all(), [
            'kode' => 'required|max:255',
            'pin' => 'required',
            'ip' => 'required'
        ]);
 
        if ($validator->fails()) {
            $this->msg = array(
                array(
                    "msg" => $validator->messages()->toArray(),
                    "response" => $this->code['fail']
                    )
            );
            return false;
        }

        if(!$this->Model->user){
            $this->msg = [[
                    "msg" => array("User Not found"),
                    "response" => $this->code['fail']
                ]];
            return false;
        }

        $searchValue = $this->_Request->kode;

        if(!$this->Model->login = Users::join("pengirim","pengirim.kode_reseller","reseller.kode")->where("reseller.pin",$this->_Request->pin)->where(function ($query) use ($searchValue) {
                $query->where("pengirim.pengirim",$searchValue)->orWhere("reseller.kode",$searchValue);
            })->exists())
        {
            $this->msg = array(array("msg" => array("Code or Pin is wrong"),"response" => $this->code['fail']));
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
