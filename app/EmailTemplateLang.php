<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class EmailTemplateLang extends Model {

    protected $table = 'email_template_langs';

    protected $guarded = array('id', '_token');


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function emailTemplate() {
        return $this->belongsTo('App\EmailTemplate');
    }

}
