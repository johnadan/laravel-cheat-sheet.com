<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    public $timestamps = false;

    public function clauses()
    {
    	return $this->hasMany('App\Clause', 'section_id');
    }

    public function getAllSheets($locale)
    {
    	return $this->with(['clauses' => function ($query) use($locale) {
            $query->where('language', $locale);
        }])->get();
    }
}
