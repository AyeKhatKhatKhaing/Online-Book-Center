<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    public function getCategory()
    {
        return $this->hasMany('App\Category',"book_id");
    }
    public function getGroup()
    {
        return $this->belongsTo('App\Group',"group_id");
    }


}
