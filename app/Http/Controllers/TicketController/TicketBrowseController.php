<?php
namespace App\Http\Controllers\TicketController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Ticket;

use App\Http\Transformer\DatatablesTransformers\DatatablesTransformers;
use App\Traits\Datatables;

class TicketBrowseController extends Controller
{
    use Datatables;
    
    public function get(Request $request){
        $this->datatables($request);
        $searchValue = $this->searchValue;

        $response['count'] = Ticket::select('count(*) as allcount')->where("kode_reseller",$this->kode)->orderBy("kode","desc")
        ->count();
        $response['totalRecordswithFilter'] = Ticket::where("kode_reseller",$this->kode)
                    ->where(function ($query) use ($searchValue) {
                        $query->where("waktu","like","%".$searchValue."%")
                            ->orWhere("jumlah","like","%".$searchValue."%")
                            ->orWhere("status","like","%".$searchValue."%")
                            ->orWhere("kode_data_bank","like","%".$searchValue."%");
                    })
                    ->orderBy("tgl_status","desc")
                    ->count();
        
        $response['records'] = Ticket::orderBy($this->columnName,$this->columnSortOrder)
                    ->where("kode_reseller",$this->kode)
                    ->where(function ($query) use ($searchValue) {
                        $query->where("waktu","like","%".$searchValue."%")
                            ->orWhere("jumlah","like","%".$searchValue."%")
                            ->orWhere("status","like","%".$searchValue."%")
                            ->orWhere("kode_data_bank","like","%".$searchValue."%");
                    })
                    ->skip($this->start)
                    ->take($this->rowperpage)
                    ->get()->toArray();
                    
        return fractal()
            ->item($response)
            ->transformWith(new DatatablesTransformers); 
    }
}
