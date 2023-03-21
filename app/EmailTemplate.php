<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model {

    protected $table = 'email_templates';

    protected $guarded = array('id', '_token');


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function emailTemplateLangs() {
        return $this->hasMany('App\EmailTemplateLang');
    }

}
