<?php

namespace App\Repositories;

use App\Admin;

class AdminRepository extends BaseRepository
{
    public function __construct(Admin $model = null)
    {
        if (empty($model)){
            $model = new Admin();
        }
        parent::__construct($model);
    }

    /**
     * @param $id
     * @param $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function updateAdmin($id, $data)
    {
        if(isset($data['password']) && $data['password'])
        {
            $data['password'] = \Hash::make($data['password']);
        }
        else
        {
            unset($data['password']);
        }

        unset($data['password2']);
        unset($data['photo']);

        return $this->update($id, $data);
    }
}