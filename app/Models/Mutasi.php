<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mutasi extends Model
{
    public $timestamps = false;
    protected $table = 'mutasi';
    protected $primaryKey = 'kode';
}
