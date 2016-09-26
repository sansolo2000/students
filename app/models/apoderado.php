<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class apoderado extends Model
{
	protected $table = 'apoderados';
	protected $primaryKey = 'apo_codigo';
	protected $fillable = ['apo_codigo', 'per_rut', 'apo_fono', 'updated_at', 'created_at'];
	protected $guarded	= ['apo_codigo'];
}

