<?php namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Web\Controller;
use App\Http\Requests\Admin\PageFormRequest;
use App\Repositories\PageRepository;
use Illuminate\Support\Facades\View;
use Redirect;
use Helper;
use Request;


/**
 * Class PagesController
 * @package App\Http\Controllers
 */
class PagesController extends Controller {

    private $pageRepository;

    public function __construct(PageRepository $pageRepository)
    {
        $this->pageRepository = $pageRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $sessionKey = 'admin.pages';

        $perPage = (int)Helper::getUserPreference($sessionKey, 'perPage', Helper::getDefaultPerPageNumber());
        $orderBy = Helper::getUserPreference($sessionKey, 'orderBy', 'title');
        $orderBySort = Helper::getUserPreference($sessionKey, 'orderBySort', 'asc');

        $resetForm = Request::exists('resetFilters');
        $filterTitle = Helper::getUserPreference($sessionKey, 'filterTitle', '', $resetForm);
        $filterUrl = Helper::getUserPreference($sessionKey, 'filterUrl', '', $resetForm);

        $pages = $this->pageRepository->getAllPaginated($perPage, $orderBy, $orderBySort, $filterTitle, $filterUrl);

        return view('admin.pages.index', compact('pages', 'sessionKey'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|View|\Illuminate\View\View
     */
    public function add()
    {
        $pageContent = 'Your page content goes here';

        $languages = Helper::getEnumOptions('pages', 'lang', '---');

        return view('admin.pages.add', compact('pageContent', 'languages'));
    }

    /**
     * Store a newly created resource in storage.
     * Validation is made using PageFormRequest
     * @param PageFormRequest $request
     *
     * @return Response
     */
    public function insert(PageFormRequest $request)
    {
        $this->pageRepository->createPage($request->all());

        return Redirect::route('admin.pages');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $page = $this->pageRepository->find($id);

        $languages = Helper::getEnumOptions('pages', 'lang', '---');

        return view('admin.pages.edit', compact('page', 'languages'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  PageFormRequest  $request
     * @return Response
     */
    public function update(PageFormRequest $request)
    {
        $pageId = $request->id;

        $page = $this->pageRepository->find($pageId);

        if($page->is_required == 'yes')
        {
            $data = $request->except('url', 'is_required');
        }
        else
        {
            $data = $request->all();
        }

        $this->pageRepository->updatePage($pageId, $data);

        return Redirect::route('admin.pages');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function delete($id)
    {
        $page = $this->pageRepository->find($id);

        if($page->is_required != 'yes')
        {
            $this->pageRepository->deleteById($id);
        }

        return Redirect::route('admin.pages');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     */
    public function editContent($id)
    {
        $pageEdit = $this->pageRepository->find($id);

        $contentEditable = true;

        return view('admin.pages.editContent', compact('pageEdit', 'contentEditable'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     */
    public function updateContent($id)
    {
        $content = \Request::get('content', '');

        if(!$content)
        {
            return \Response::json(['status'=>'fail', 'message'=>'Nothing to save']);
        }

        $this->pageRepository->updatePage($id, ['content'=>$content]);

        return \Response::json(['status'=>'success', 'message'=>'Page content was successfully updated']);
    }

}
