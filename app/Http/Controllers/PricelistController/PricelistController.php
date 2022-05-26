<?php

namespace App\Http\Controllers\PricelistController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Produk;

use App\Http\Transformer\DatatablesTransformers\DatatablesTransformers;
use App\Traits\Datatables;
use Carbon\Carbon;

class PricelistController extends Controller
{
    use Datatables;
    
    public function get(Request $request){

        $this->datatables($request);
        $searchValue = $this->searchValue;
        $kode_operator = $request->get("kode_operator");
        $response['count'] =  Produk::select('count(*) as allcount');

        $response['totalRecordswithFilter'] = Produk::select('count(*) as allcount')
                //->where("harga_jual",">",0)
                ->where(function ($query) use ($searchValue) {
                    $query->where("kode","like","%".$searchValue."%")
                        ->orWhere("nama","like","%".$searchValue."%")
                        ->orWhere("harga_jual","like","%".$searchValue."%");
                });
            

        $response['records'] =  Produk::where(function ($query) use ($searchValue) {
                $query->where("kode","like","%".$searchValue."%")
                    ->orWhere("nama","like","%".$searchValue."%")
                    ->orWhere("harga_jual","like","%".$searchValue."%");
                });

        if(empty($request->get("kode_operator"))||$request->get("kode_operator") == "all"){
            $response['count'] = $response['count']->count();
            $response['totalRecordswithFilter'] = $response['totalRecordswithFilter']->count();
            $response['records'] = $response['records'];

        }else{
            $response['count'] = $response['count']->where("kode","LIKE",$kode_operator."%")->count();
            $response['totalRecordswithFilter'] = $response['totalRecordswithFilter']->where("kode","LIKE",$kode_operator."%")->count();
            $response['records'] = $response['records']->where("kode","LIKE",$kode_operator."%");
        }

        $response['records'] = $response['records']->orderBy("harga_jual",$this->columnSortOrder);
        $response['records'] = $response['records']->skip($this->start)->take($this->rowperpage)->get();

        return fractal()
            ->item($response)
            ->transformWith(new DatatablesTransformers)
            ->serializeWith(new \Spatie\Fractalistic\ArraySerializer()); 

    }

    public function get_produk(Request $request){
        $searchValue = $request->get("search");
        $data = Produk::select("kode_operator")
            ->where(function ($query) use ($searchValue) {
                $query->where("kode_operator","like","%".$searchValue."%");
            })
            ->groupBy("kode_operator")->limit(10)->get();
        $arr[] = array(
            "jenis" => "all",
            "deskripsi" => "Semua"
        );
        foreach($data as $v){
            $arr[] = array(
                "jenis" => $v->kode_operator,
                "deskripsi" => $v->kode_operator
            );
        }
        return response()->json($arr);
    }
}
