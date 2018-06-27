<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $guarded = [];

    public function getPerPage()
    {
        return config('model.perpage', 15);
    }
}
