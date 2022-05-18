<?php

namespace App\Http\Controllers\MutasiController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Mutasi;

use App\Http\Transformer\DatatablesTransformers\DatatablesTransformers;
use App\Traits\Datatables;

class MutasiBrowseController extends Controller
{
    use Datatables;
    
    public function get(Request $request){
        $this->datatables($request);
        $searchValue = $this->searchValue;

        $response['count'] =  Mutasi::select('count(*) as allcount')->where("kode_reseller",$this->kode)->orderBy("kode","desc")
        ->count();
        $response['totalRecordswithFilter'] = Mutasi::where("kode_reseller",$this->kode)
                    ->where(function ($query) use ($searchValue) {
                        $query->where("tanggal","like","%".$searchValue."%")
                            ->orWhere("jumlah","like","%".$searchValue."%")
                            ->orWhere("keterangan","like","%".$searchValue."%");
                    })
                    ->orderBy("kode","desc")
                    ->count();
        
        $response['records'] = Mutasi::orderBy($this->columnName,$this->columnSortOrder)
                    ->where("kode_reseller",$this->kode)
                    ->where(function ($query) use ($searchValue) {
                        $query->where("tanggal","like","%".$searchValue."%")
                            ->orWhere("jumlah","like","%".$searchValue."%")
                            ->orWhere("keterangan","like","%".$searchValue."%");
                    })
                    ->skip($this->start)
                    ->take($this->rowperpage)
                    ->get()->toArray();
                    
        return fractal()
            ->item($response)
            ->transformWith(new DatatablesTransformers); 
    }
}
