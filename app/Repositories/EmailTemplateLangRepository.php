<?php namespace App\Repositories;

use App\EmailTemplateLang;

class EmailTemplateLangRepository extends BaseRepository
{
    public function __construct(EmailTemplateLang $model = null)
    {
        if (empty($model)){
            $model = new EmailTemplateLang();
        }
        parent::__construct($model);
    }


    /**
     * @param $name
     * @return mixed
     */
    public function findByEmailTemplate($templateId, $langCode)
    {
        return $this->where('email_template_id', '=', $templateId)->where('lang', $langCode)->first();
    }

    /**
     * @param $id
     * @param $data
     */
    public function updateOrCreateEmailTemplateLang($templateId, $langCode, $data)
    {
        return $this->updateOrCreate(
            ['email_template_id' => $templateId, 'lang' => $langCode],
            $data);
    }

}