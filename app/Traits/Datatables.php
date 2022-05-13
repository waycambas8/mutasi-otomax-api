<?php
namespace App\Traits;

use Closure;

trait Datatables
{
    public function datatables($request){
        $this->kode = getallheaders()['kode'];
        $this->draw = $request->get('draw');
        $this->start = $request->get("start");
        $this->rowperpage = $request->get("length"); // Rows display per page
        
        $this->columnIndex_arr = $request->get('order');
        $this->columnName_arr = $request->get('columns');
        $this->order_arr = $request->get('order');
        $this->search_arr = $request->get('search');

        $this->columnIndex = $this->columnIndex_arr[0]['column']; // Column index
        $this->columnName = $this->columnName_arr[$this->columnIndex]['data']; // Column name
        $this->columnSortOrder = "desc";//$order_arr[0]['dir'];//"desc";// // asc or desc
        $this->searchValue = $this->search_arr['value']; // Search value

        return $this;
    }
}