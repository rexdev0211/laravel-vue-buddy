<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Page extends Model {

    protected $table = 'pages';

    protected $guarded = array('id', '_token');


    public static $developerUsedPagesUrls = [
        'some-page-used-by-developer.html',
        'page-like-view-invoice-or-something-else.html'
    ];

}
