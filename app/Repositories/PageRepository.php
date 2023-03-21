<?php namespace App\Repositories;

use App\Page;

class PageRepository extends BaseRepository
{

//    protected $model;

    public function __construct(Page $model = null)
    {
        if (empty($model)){
            $model = new Page();
        }
        parent::__construct($model);
    }

    /**
     * @param $perPage
     * @param string $orderBy
     * @param string $filterTitle
     * @param string $filterUrl
     * @return mixed
     */
    public function getAllPaginated($perPage, $orderBy='title', $orderBySort = 'asc', $filterTitle = '', $filterUrl = ''){
        return $this->filterByTitle($filterTitle)->filterByUrl($filterUrl)->orderBy($orderBy, $orderBySort)->paginate($perPage);
    }

    /**
     * @param $filterTitle
     * @return $this
     */
    public function filterByTitle($filterTitle)
    {
        if(!$filterTitle) return $this;

        return $this->where('title', 'like', '%'.$filterTitle.'%');
    }

    /**
     * @param $filterUrl
     * @return $this
     */
    public function filterByUrl($filterUrl)
    {
        if(!$filterUrl) return $this;

        return $this->where('url', 'like', '%'.$filterUrl.'%');
    }

    /**
     * @param $data
     */
    public function createPage($data)
    {
        $page = $this->create($data);

        $this->createRevision($page->id, $data['content']);
    }

    /**
     * @param $pageId
     * @param $data
     */
    public function updatePage($pageId, $data)
    {
        $this->createRevision($pageId, $data['content']);

        $this->update($pageId, $data);
    }

    /**
     * @param $pageId
     * @param $content
     */
    public function createRevision($pageId, $content)
    {
        $admin = \Auth::getUser();

        \DB::table('page_revisions')->insert(
            array(
                'page_id' => $pageId,
                'user_id' => $admin->id,
                'user_name' => $admin->email,
                'content' => $content
            )
        );
    }

    /**
     * @param $url
     * @return mixed
     */
    public function findByUrl($url, $lang)
    {
        return $this->where('url', '=', $url)->where('lang', $lang)->first();
    }




}