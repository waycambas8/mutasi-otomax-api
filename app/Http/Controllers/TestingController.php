<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Support\Response\Json;
use App\Models\Mutasi;
use App\Http\Transformer\TestingTransformers;


class TestingController extends Controller
{
    public function testing(){
        $books = [
            ['id' => 1, 'title' => 'Hogfather', 'characters' => ["ada"]],
            ['id' => 2, 'title' => 'Game Of Kill Everyone', 'characters' => ["aadas"]]
         ];

        return fractal()
            ->collection($books)
            ->transformWith(new TestingTransformers)
            ->toArray();
    }
}
