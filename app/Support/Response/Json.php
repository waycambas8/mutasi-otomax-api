<?php

namespace App\Support\Response;

use DateTime;

class Json
{
    static $Json = [
        'timestamp' => NULL,
        'environment' => NULL,
        'data' => NULL
    ];

    public static function custom($JsonArray)
    {
        self::$Json = $JsonArray;
    }

    public static function get($CompositeKey = NULL)
    {
        if ($CompositeKey) {
            $Init = new self();
            return $Init->GetVal(self::$Json, $CompositeKey);
        } else {
            return self::$Json;
        }
    }

    public static function set($CompositeKey, $Val)
    {
        $Init = new self();
        $Init->SetVal(self::$Json, $CompositeKey, $Val);
    }

    public static function unset($CompositeKey)
    {
        $Init = new self();
        $Init->UnSetVal(self::$Json, $CompositeKey);
    }

    protected function GetVal(&$arr, $Path)
    {
        $loc = &$arr;
        foreach(explode('.', $Path) as $step) {
            $loc = &$loc[$step];
        }
        return $loc;
    }

    protected function SetVal(&$arr, $Path, $Val)
    {
        $loc = &$arr;
        foreach(explode('.', $Path) as $step) {
            $loc = &$loc[$step];
        }
        return $loc = $Val;
    }

    protected function UnSetVal(&$arr, $Path)
    {
        $loc = &$arr;
        foreach(explode('.', $Path) as $step) {
            $loc = &$loc[$step];
            unset($arr[$step]);
        }
        return $loc;
    }

}
