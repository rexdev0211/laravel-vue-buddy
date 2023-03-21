<?php namespace App\Repositories;

use App\EmailTemplate;

class EmailTemplateRepository extends BaseRepository
{
    public function __construct(EmailTemplate $model = null)
    {
        if (empty($model)){
            $model = new EmailTemplate();
        }
        parent::__construct($model);
    }

    /**
     * @param $perPage
     * @param string $orderBy
     * @param string $orderBySort
     * @return mixed
     */
    public function getAllPaginated($perPage, $orderBy='sort_order', $orderBySort = 'asc'){
        return $this->with('emailTemplateLangs')->orderBy($orderBy, $orderBySort)->paginate($perPage);
    }

    /**
     * @param $name
     * @return mixed
     */
    public function findByName($name)
    {
        return $this->where('name', '=', $name)->first();
    }

    /**
     * @param $id
     * @param $data
     */
    public function updateEmailTemplate($id, $data)
    {
        return $this->update($id, $data);
    }




}