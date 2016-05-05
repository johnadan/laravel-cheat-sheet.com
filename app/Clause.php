<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Clause extends Model
{
    public $timestamps = false;
    
    protected $fillable = ['clause', 'description', 'link', 'language', 'slug'];

    public function sections()
    {
    	return $this->belongsTo('App\Section');
    }
}
