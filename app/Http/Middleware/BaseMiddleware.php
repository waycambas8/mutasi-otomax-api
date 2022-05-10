<?php

namespace App\Http\Middleware;
use App;
use Closure;
use Illuminate\Http\Request;

abstract class BaseMiddleware
{
    protected $_Request;
    protected $Model;
    
    public function __construct(Request $request){
        $this->_Request = $request;
        $this->Model = (object)[];
        $this->HttpCode = 401;
        $this->get_token_server();
    }

    public function get_token_server(){
        $host = md5(getallheaders()['Host']);
        $server = md5(env("APP_HOST"));
        if($host!==$server){
            abort(401);
        }
       
    }
}
