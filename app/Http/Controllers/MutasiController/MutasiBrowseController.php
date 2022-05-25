<?php

namespace App\Http\Controllers\MutasiController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Mutasi;

use App\Http\Transformer\DatatablesTransformers\DatatablesTransformers;
use App\Traits\Datatables;
use Carbon\Carbon;

class MutasiBrowseController extends Controller
{
    use Datatables;
    
    public function get(Request $request){
        $this->datatables($request);
        $type = $request->get("type");
        $searchValue = $this->searchValue;
        $date_from = Carbon::parse($request->get('date_from'))->startOfDay();
        $date_end = Carbon::parse($request->get('date_end'))->endOfDay();

        $response['count'] =  Mutasi::select('count(*) as allcount')
                ->where("kode_reseller",$this->kode)
                ->whereBetween("tanggal",[$date_from,$date_end])
                ->orderBy("kode","desc");
        
        $response['totalRecordswithFilter'] = Mutasi::where("kode_reseller",$this->kode)
                    ->where(function ($query) use ($searchValue) {
                        $query->where("tanggal","like","%".$searchValue."%")
                            ->orWhere("jumlah","like","%".$searchValue."%")
                            ->orWhere("keterangan","like","%".$searchValue."%");
                    })
                    ->whereBetween("tanggal",[$date_from,$date_end])
                    ->orderBy("kode","desc");
                    
        
        $response['records'] = Mutasi::orderBy($this->columnName,$this->columnSortOrder)
                    ->where("kode_reseller",$this->kode)
                    ->where(function ($query) use ($searchValue) {
                        $query->where("tanggal","like","%".$searchValue."%")
                            ->orWhere("jumlah","like","%".$searchValue."%")
                            ->orWhere("keterangan","like","%".$searchValue."%");
                    })
                    ->whereBetween("tanggal",[$date_from,$date_end])
                    ->skip($this->start)
                    ->take($this->rowperpage);

        if($type == 'all'){
            $response['count'] = $response['count']->count();
            $response['totalRecordswithFilter'] = $response['totalRecordswithFilter']->count();
            $response['records'] = $response['records']->get()->toArray();
        }elseif($type == "manual"){
            $response['count'] = $response['count']->where("jenis",null)->count();
            $response['totalRecordswithFilter'] = $response['totalRecordswithFilter']->where("jenis",null)->count();
            $response['records'] = $response['records']->where("jenis",null)->get()->toArray();
        }else{
            $response['count'] = $response['count']->where("jenis",$type)->count();
            $response['totalRecordswithFilter'] = $response['totalRecordswithFilter']->where("jenis",$type)->count();
            $response['records'] = $response['records']->where("jenis",$type)->get()->toArray();
        }

        return fractal()
            ->item($response)
            ->transformWith(new DatatablesTransformers)
            ->serializeWith(new \Spatie\Fractalistic\ArraySerializer()); 
    }

    public function get_type(Request $request){
        $data = Mutasi::select("jenis")->groupBy("jenis")->get();
        $arr[] = array(
            "jenis" => "all",
            "deskripsi" => "Semua"
        );
        foreach($data as $v){
            if($v->jenis == "G"){
                $arr[] = array(
                    "jenis" => "G",
                    "deskripsi" => "Refund"
                );
            }elseif($v->jenis == "T"){
                $arr[] = array(
                    "jenis" => "T",
                    "deskripsi" => "Transaksi"
                );
            }elseif($v->jenis == "B"){
                $arr[] = array(
                    "jenis" => "B",
                    "deskripsi" => "Ticket"
                );
            }elseif(empty($v->jenis)){
                $arr[] = array(
                    "jenis" => 'manual',
                    "deskripsi" => "Mutasi Manual"
                );
            }
        }
        return response()->json($arr);
    }

    
}
