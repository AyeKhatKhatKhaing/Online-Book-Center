<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    public function getChapter()
    {
        return $this->hasMany('App\Chapter',"category_id");
    }

}
