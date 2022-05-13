<?php

namespace App\Http\Controllers\UserController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Transformer\UserTransformers\UserTransformerController;
use App\Models\Users;

use League\Fractal;

class UserController extends Controller
{
    public function login(Request $request){
        $Model = $request->Payload->all()['Model'];
        return fractal()
            ->item($Model)
            ->transformWith(new UserTransformerController); 
    }
}
