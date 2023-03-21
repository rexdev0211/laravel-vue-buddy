<?php namespace App\Repositories;

use App\Tag;

class TagRepository extends BaseRepository
{

    public function __construct(Tag $model = null)
    {
        if (empty($model)){
            $model = new Tag();
        }
        parent::__construct($model);
    }

    /**
     * @param $tagName
     */
    public function findOrCreateTag($tagName)
    {
        $tag = $this->findByName($tagName);

        if(is_null($tag))
        {
            $tag = $this->model->create(['name'=>$tagName]);
        }

        return $tag;
    }

    /**
     * @param $filterName
     * @return $this
     */
    public function findByName($name)
    {
        return $this->where('name', $name)->first();
    }
}
