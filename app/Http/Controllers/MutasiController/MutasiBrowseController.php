<?php

namespace App\Http\Controllers\MutasiController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Mutasi;
use Illuminate\Support\Facades\Validator;

use App\Http\Transformer\DatatablesTransformers\DatatablesTransformers;
use App\Http\Transformer\DetailTransformers\DetailTransformers;

use App\Traits\Datatables;
use App\Traits\Browse;

use Carbon\Carbon;

class MutasiBrowseController extends Controller
{
    use Datatables, Browse;
    
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

        $response['status'] = $this->status()->status;


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

    public function detail(Request $request){
        $kode = (!empty($request->get("kode")))?$request->get("kode"):1111111;

        $validator = Validator::make($request->all(), [
            'kode' => 'required|integer|digits_between:1,9',
        ]);
 
        if ($validator->fails()) {
            $msg = array(
                    "msg" => $validator->messages()->toArray(),
                    "response" => 404
                );

            return fractal()
                ->item($msg)
                ->transformWith(new DetailTransformers)
                ->serializeWith(new \Spatie\Fractalistic\ArraySerializer()); 
        }

        if(!Mutasi::where("kode",$kode)->where("kode_reseller",getallheaders()['kode'])->exists()){
            $response['msg'] = "Tidak memiliki akses untuk data ini";
            $response['response'] = 404;

            return fractal()
                ->item($response)
                ->transformWith(new DetailTransformers)
                ->serializeWith(new \Spatie\Fractalistic\ArraySerializer()); 
        }

        if(Mutasi::where("kode",$kode)->where("kode_transaksi","!=",null)->exists()){
            $response = Mutasi::select(
                "mutasi.kode as kode",
                "mutasi.kode_reseller as kode_reseller",
                "mutasi.tanggal as tanggal",
                "mutasi.jumlah as jumlah",
                "mutasi.jenis as jenis",
                "mutasi.kode_transaksi as kode_transaksi",
                "mutasi.saldo_akhir as saldo_akhir",
                "transaksi.tgl_entri as tgl_entri",
                "transaksi.kode_produk as kode_produk",
                "transaksi.tujuan as tujuan",
                "transaksi.pengirim as pengirim",
                "transaksi.perintah as perintah",
                "transaksi.kode_area as kode_area",
                "transaksi.ref_id as ref_id",
                "transaksi.saldo_awal as saldo_awal"
            )->join("transaksi","mutasi.kode_transaksi","transaksi.kode")->where("mutasi.kode",$kode)->first();
        } else{
            $response = Mutasi::select(
                "mutasi.kode as kode",
                "mutasi.kode_reseller as kode_reseller",
                "mutasi.tanggal as tanggal",
                "mutasi.jumlah as jumlah",
                "mutasi.jenis as jenis",
                "mutasi.kode_transaksi as kode_transaksi",
                "mutasi.saldo_akhir as saldo_akhir"
            )->where("mutasi.kode",$kode)->first();
        }
        
        $response['response'] = 200;
        return fractal()
            ->item($response)
            ->transformWith(new DetailTransformers)
            ->serializeWith(new \Spatie\Fractalistic\ArraySerializer()); 


    }

    
}
