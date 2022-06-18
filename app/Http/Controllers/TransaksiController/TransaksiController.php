<?php

namespace App\Http\Controllers\TransaksiController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Transaksi;

use App\Http\Transformer\DatatablesTransformers\DatatablesTransformers;
use App\Traits\Datatables;
use App\Traits\Browse;

use DB;
use Carbon\Carbon;

class TransaksiController extends Controller
{
    use  Datatables,Browse;

    public function get(Request $request){
        $this->datatables($request);
        $searchValue = $this->searchValue;
        $status = $request->get('status');
        $date_from = Carbon::parse($request->get('date_from'))->startOfDay();
        $date_end = Carbon::parse($request->get('date_end'))->endOfDay();

        $response['count'] =  Transaksi::select('count(*) as allcount',"produk.nama as name_product","produk.kode as produk_code","transaksi.*")
            ->join("produk","transaksi.kode_produk","produk.kode")
            ->where(function ($query) use ($searchValue) {
                $query->where("transaksi.tgl_status","like","%".$searchValue."%")
                    ->orWhere("transaksi.kode_produk","like","%".$searchValue."%")
                    ->orWhere("transaksi.sn","like","%".$searchValue."%")
                    ->orWhere("transaksi.kode","like","%".$searchValue."%")
                    ->orWhere("transaksi.tujuan","like","%".$searchValue."%");
            })
            ->whereBetween("transaksi.tgl_status",[$date_from,$date_end]);
            

        $response['totalRecordswithFilter'] = Transaksi::select("produk.kode as produk_code","produk.nama as name_product","transaksi.*")->where("transaksi.kode_reseller",$this->kode)
            ->join("produk","transaksi.kode_produk","produk.kode")
            ->where(function ($query) use ($searchValue) {
                $query->where("transaksi.tgl_status","like","%".$searchValue."%")
                    ->orWhere("transaksi.kode_produk","like","%".$searchValue."%")
                    ->orWhere("transaksi.sn","like","%".$searchValue."%")
                    ->orWhere("transaksi.kode","like","%".$searchValue."%")
                    ->orWhere("transaksi.tujuan","like","%".$searchValue."%");
            })
            ->whereBetween("transaksi.tgl_status",[$date_from,$date_end]);
           
            

        $response['records'] = Transaksi::select("produk.kode as produk_code","produk.nama as name_product","transaksi.*")->orderBy($this->columnName,$this->columnSortOrder)
            ->join("produk","transaksi.kode_produk","produk.kode")
            ->where("transaksi.kode_reseller",$this->kode)
            ->where(function ($query) use ($searchValue) {
                $query->where("transaksi.tgl_status","like","%".$searchValue."%")
                    ->orWhere("transaksi.kode_produk","like","%".$searchValue."%")
                    ->orWhere("transaksi.sn","like","%".$searchValue."%")
                    ->orWhere("transaksi.kode","like","%".$searchValue."%")
                    ->orWhere("transaksi.tujuan","like","%".$searchValue."%");
            })
            ->whereBetween("transaksi.tgl_status",[$date_from,$date_end]);
            
            
        if($status !=0){
            if($status == 1){
                $response['count'] = $response['count']->where("transaksi.status",20);
                $response['totalRecordswithFilter'] = $response['totalRecordswithFilter']->where("transaksi.status",20);
                $response['records'] =  $response['records']->where("transaksi.status",20);
            }else{
                $response['count'] = $response['count']->where("transaksi.status","!=",20);
                $response['totalRecordswithFilter'] = $response['totalRecordswithFilter']->where("transaksi.status","!=",20);
                $response['records'] =  $response['records']->where("transaksi.status","!=",20);
            }
        }

        $response['count']  =  $response['count']->orderBy("transaksi.kode","desc")->count();
        $response['totalRecordswithFilter'] = $response['totalRecordswithFilter']->orderBy("transaksi.kode","desc")->count();
        $response['records'] = $response['records']->skip($this->start)->take($this->rowperpage)->get()->toArray();

        $response['status'] = $this->status()->status;

        
        return fractal()
            ->item($response)
            ->transformWith(new DatatablesTransformers)
            ->serializeWith(new \Spatie\Fractalistic\ArraySerializer()); 
    }
}
