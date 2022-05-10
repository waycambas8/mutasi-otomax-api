<?php

namespace App\Traits;

use App\Models\WolescastsSearch;
use App\Models\User;

use Closure;

trait Browse
{
    protected $Search = null;
    protected $OrderBy = [];
    protected $tableName = null;

    public function Browse($request, $Model, $function = null)
    {
        if (count($this->OrderBy) > 0) {
            foreach ($this->OrderBy as $key => $order) {
                if (isset($request->ArrQuery->{'orderBy.' . $order})) {
                    $orderName = $order;
                    if ($this->tableName && !preg_match('/\./', $order)) {
                        $orderName = "$this->tableName.$order";
                    }
                    $Model->orderBy($orderName, $request->ArrQuery->{'orderBy.' . $order});
                }
            }
        }
        if (isset($request->ArrQuery->take)) {
            if ($request->ArrQuery->take !== 'all') {
                $request->ArrQuery->take = (int) $request->ArrQuery->take;
            }
        }
        if (isset($request->ArrQuery->skip)) {
            $request->ArrQuery->skip = (int) $request->ArrQuery->skip;
        }

        if ($this->Search) {
            $In = [];
            $this->Search->take($request->ArrQuery->take)->skip($request->ArrQuery->skip);
            $In = $this->Search->get()->pluck('_id')->all();
        }

        $Array = [
            'query' => $request->ArrQuery
        ];
        if ($request->ArrQuery->{'with.total'}) {
            $ModelForCount = clone $Model;
        }
        if ($this->Search) {
            $prefix = $this->TableName ? $this->TableName . '.' : '';
            $Model->whereIn($prefix . 'id', $In);
            $ModelForCount->whereIn($prefix . 'id', $In);
            if ($request->ArrQuery->take !== 'all') {
                $Model->take($request->ArrQuery->take);
            }
        } else {
            if ($request->ArrQuery->take !== 'all') {
                $Model->take($request->ArrQuery->take)->skip($request->ArrQuery->skip);
            }
        }
        if (config('app.debug')) {
            try {
                $ModelForSQL = clone $Model;
                $Array['debug'] = [
                    'sql' => $ModelForSQL->toSql(),
                    'bindings' => $ModelForSQL->getBindings()
                ];
            } catch (\Exception $e) {
            }
        }
        $data = $Model->get();

        if ($function instanceof Closure) {
            $data = call_user_func_array($function, [ $data ]);
        }

        if (isset($request->ArrQuery->map)) {
            try {
                $map = base64_decode($request->ArrQuery->map);
                $data = query_map($data, $map);
            } catch (\Exception $e) {
            }
        } elseif (isset($request->QueryContent->map)) {
            $data = query_map($data, $request->QueryContent->map);
        }
        if ($request->ArrQuery->{'with.total'}) {
            $ModelForCount->getQuery()->orders = null;
            if (isset($request->ArrQuery->{'with.total.groupBy'})) {
                $Array['total'] = (int) $ModelForCount->get()->count();
            } else if (isset($request->ArrQuery->cms) && isset($request->ArrQuery->type)
            && $request->ArrQuery->type == 'member' && isset($request->ArrQuery->manual_count)) {
                $Array['total'] = (int) User::where(function ($query) use($request) {
                    $query->where('position_id',5);
                    $query->where('deleted_at',null);
                    if (isset($request->ArrQuery->app_id)) {
                        $query->where("app_id", $request->ArrQuery->app_id);
                    }
                })->count();
            } else {
                $Array['total'] = (int) $ModelForCount->count();
            }

        }
        if ((isset($request->ArrQuery->set)) && $request->ArrQuery->set === 'first') {
            $Array['show'] = (int) isset($data[0]) ? 1 : 0;
            $Array['records'] = isset($data[0]) ? $data[0] : (object)[];
        } else if(isset($request->ArrQuery->config_select)){
            $Array['records'] = $data;
        } else {
            if (isset($request->ArrQuery->unshow)) {
                $Array['records'] = $data;
            }else{
                $Array['show'] = (int) $data->count();
                $Array['records'] = $data;
            }

        }
        if (isset($Array['show'])) {
            $Array['length'] = $Array['show'];
        } else {
            $Array['length'] = 0;
        }
        return $Array;
    }

    public function ElasticSearch($tableName = '')
    {
        if ($tableName) {
            $this->TableName = $tableName;
        }
        $this->Search = app('es')->type(with(new WolescastsSearch)->getType());
        return $this;
    }

    public function search($body = [])
    {
        if ($this->Search) {
            $this->Search->body($body);
        }
        return $this;
    }

    public function Group(&$item, $key, $str, &$data)
    {
        if(substr($key, 0, strlen($str)) === $str) {
            if (is_object($item)) {
                $item->{substr($key, strlen($str))} = $data->{$key};
            } else {
                $item[substr($key, strlen($str))] = $data->{$key};
            }
            unset($data->{$key});
        }
    }

    public function OrderBy($orderlist)
    {
        if (isset($orderlist)) {
            $this->OrderBy = $orderlist;
        }
        return $this;
    }

    public function TableName($tableName)
    {
        if (isset($tableName)) {
            $this->tableName = $tableName;
        }
        return $this;
    }
}
