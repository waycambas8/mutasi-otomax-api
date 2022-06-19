<?php

namespace App\Http\Controllers\DashboardController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Users;
use App\Models\Transaksi;
use App\Models\Ticket;
use App\Models\Mutasi;

use App\Traits\Browse;

use DB;
use Carbon\Carbon;

use App\Http\Transformer\DashboardTransformers\DashboardTransformers;

class DashboardController extends Controller
{

    use Browse;

    public function saldo(Request $request){
        $kode = getallheaders()['kode'];
        $status = $request->get("status");
        $date_from = Carbon::parse($request->get('date_from'))->startOfDay();
        $date_end = Carbon::parse($request->get('date_end'))->endOfDay();

        $response = Users::where("kode",$kode)->first();
        // Ticket graph
        $response['ticket'] = Ticket::select("status")->select(DB::raw('sum(jumlah) as total'))
            ->where("kode_reseller",$kode)
            ->where("status","S")
            ->whereBetween("tgl_status",[$date_from,$date_end])->first();
            
        // Mutasi Mutasi
        $response['mutasi'] = Mutasi::select(DB::raw('sum(jumlah) as total'))
            ->where("kode_reseller",$kode)
            ->where("jenis","T")
            ->whereBetween("tanggal",[$date_from,$date_end])
            ->first();

        // Transaction graph
        $response['graph'] =  Transaksi::select("status", DB::raw('count(*) as total'))
            ->where("kode_reseller",$kode)
            ->whereBetween("tgl_status",[$date_from,$date_end]);
        
        if(!empty($status)){
            if($status == 1){
                $response['graph'] = $response['graph']->where("status",20);
            }else{
                $response['graph'] = $response['graph']->where("status","!=",20);
            }
        }
        
        $response['graph'] = $response['graph']->groupBy("status")->get()->toArray();
        
        $response['status'] = $this->status()->status;

        return fractal()
            ->item($response)
            ->transformWith(new DashboardTransformers)
            ->serializeWith(new \Spatie\Fractalistic\ArraySerializer()); 
    }
}
