<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{

    protected $table = 'countries';

    public $timestamps = false;

    protected $guarded = array('id', '_token');


}
