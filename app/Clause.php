<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Clause extends Model
{
    public $timestamps = false;
    
    protected $fillable = ['clause', 'description', 'link', 'language', 'slug'];

    public function sections()
    {
    	return $this->belongsTo('App\Section', 'section_id');
    }

    public function getClause($locale, $slug)
    {
    	return $this->with('sections')
                    ->where('language', $locale)
            		->where('slug', $slug)
                    ->first();
    }
}
