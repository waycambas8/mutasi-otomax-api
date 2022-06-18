<?php

namespace App\Http\Controllers\DashboardController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Users;
use App\Models\Transaksi;
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
