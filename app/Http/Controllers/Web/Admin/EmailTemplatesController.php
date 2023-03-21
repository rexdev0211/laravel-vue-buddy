<?php namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Web\Controller;
use App\Repositories\EmailTemplateLangRepository;
use App\Repositories\EmailTemplateRepository;
use App\Services\HelperService;
use Helper;
use App\Http\Requests\Admin\EmailTemplateLangFormRequest;
use Illuminate\Http\Request;
use Redirect;

/**
 * Class EmailTemplatesController
 * @package App\Http\Controllers
 */
class EmailTemplatesController extends Controller
{
    private $emailTemplateRepository;
    private $emailTemplateLangRepository;

    public function __construct(EmailTemplateRepository $emailTemplateRepository, EmailTemplateLangRepository $emailTemplateLangRepository)
    {
        $this->emailTemplateRepository = $emailTemplateRepository;
        $this->emailTemplateLangRepository = $emailTemplateLangRepository;
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index(HelperService $helperService)
    {
        $sessionKey = 'admin.emailTemplates';

        $perPage = (int)Helper::getUserPreference($sessionKey, 'perPage', Helper::getDefaultPerPageNumber());
        $orderBy = Helper::getUserPreference($sessionKey, 'orderBy', 'sort_order');
        $orderBySort = Helper::getUserPreference($sessionKey, 'orderBySort', 'asc');

//        $resetForm = \Request::exists('resetFilters');

        $emailTemplates = $this->emailTemplateRepository->getAllPaginated($perPage, $orderBy, $orderBySort);

        $langs = $helperService->getEnumOptions('email_template_langs', 'lang', false);

        return view('admin.emailTemplates.index', compact('emailTemplates', 'langs'));
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id, Request $request, HelperService $helperService)
    {
        $emailTemplate = $this->emailTemplateRepository->find($id);

        if (is_null($emailTemplate)) {
            dd('email template does not exist');
        }

        $langs = $helperService->getEnumOptions('email_template_langs', 'lang', false);

        if (empty($langs[$request->get('lang')])) {
            dd('lang not available');
        }

        $emailTemplateLang = $this->emailTemplateLangRepository->findByEmailTemplate($id, $request->get('lang'));

        if (is_null($emailTemplateLang)) {
            $emailTemplateLang = (object) [
                'lang' => $request->get('lang'),
            ];
        }

        return view('admin.emailTemplates.edit', compact('emailTemplate', 'emailTemplateLang'));
    }

    /**
     * @param EmailTemplateLangFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(EmailTemplateLangFormRequest $request, HelperService $helperService)
    {
        $langs = $helperService->getEnumOptions('email_template_langs', 'lang', false);

        if (empty($langs[$request->get('lang')])) {
            dd('lang not available');
        }

        $this->emailTemplateLangRepository->updateOrCreateEmailTemplateLang($request->id, $request->get('lang'), $request->only('subject', 'body'));

        return Redirect::route('admin.emailTemplates');
    }

}
