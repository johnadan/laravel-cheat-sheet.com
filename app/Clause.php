<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Clause extends Model
{
    public $timestamps = false;
    
    public function sections()
    {
    	return $this->belongsTo('App\Section');
    }
}
