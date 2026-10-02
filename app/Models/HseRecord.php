<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HseRecord extends Model
{
    use HasFactory;

    protected $table = 'hse_records';

    protected $fillable = [
        'timestamp',
        'tanggal',
        'kwh',
        'consumed',
        'jam_pencatatan',
        'picture',
    ];
}
