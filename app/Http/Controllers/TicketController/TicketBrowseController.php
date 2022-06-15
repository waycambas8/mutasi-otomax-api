<?php
namespace App\Http\Controllers\TicketController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Ticket;

use App\Http\Transformer\DatatablesTransformers\DatatablesTransformers;
use App\Traits\Datatables;
use App\Traits\Browse;

use Carbon\Carbon;


class TicketBrowseController extends Controller
{
    use Datatables, Browse;
    
    public function get(Request $request){
        $this->datatables($request);
        $searchValue = $this->searchValue;
        $date_from = Carbon::parse($request->get('date_from'))->startOfDay();
        $date_end = Carbon::parse($request->get('date_end'))->endOfDay();

        $response['count'] = Ticket::select('count(*) as allcount')
                ->join("inbox","inbox.kode","tiket_deposit.kode_inbox")
                ->where("tiket_deposit.kode_reseller",$this->kode)
                ->whereBetween("tiket_deposit.tgl_status",[$date_from,$date_end])
                ->count();
        $response['totalRecordswithFilter'] = Ticket::where("tiket_deposit.kode_reseller",$this->kode)
                    ->join("inbox","inbox.kode","tiket_deposit.kode_inbox")
                    ->where(function ($query) use ($searchValue) {
                        $query->where("tiket_deposit.waktu","like","%".$searchValue."%")
                            ->orWhere("tiket_deposit.jumlah","like","%".$searchValue."%")
                            ->orWhere("tiket_deposit.status","like","%".$searchValue."%")
                            ->orWhere("tiket_deposit.kode_data_bank","like","%".$searchValue."%");
                    })
                    ->whereBetween("tiket_deposit.tgl_status",[$date_from,$date_end])
                    ->count();
        
        $response['records'] = Ticket::select("inbox.status as status_inbox","inbox.pesan as pesan","tiket_deposit.*")->orderBy("tiket_deposit.".$this->columnName,$this->columnSortOrder)
                    ->join("inbox","inbox.kode","tiket_deposit.kode_inbox")
                    ->where("tiket_deposit.kode_reseller",$this->kode)
                    ->where(function ($query) use ($searchValue) {
                        $query->where("tiket_deposit.waktu","like","%".$searchValue."%")
                            ->orWhere("tiket_deposit.jumlah","like","%".$searchValue."%")
                            ->orWhere("tiket_deposit.status","like","%".$searchValue."%")
                            ->orWhere("tiket_deposit.kode_data_bank","like","%".$searchValue."%");
                    })
                    ->whereBetween("tiket_deposit.tgl_status",[$date_from,$date_end])
                    ->skip($this->start)
                    ->take($this->rowperpage)
                    ->get()->toArray();
        
        $response['status'] = $this->status()->status;
                    
        return fractal()
            ->item($response)
            ->transformWith(new DatatablesTransformers)
            ->serializeWith(new \Spatie\Fractalistic\ArraySerializer()); 
    }
}
