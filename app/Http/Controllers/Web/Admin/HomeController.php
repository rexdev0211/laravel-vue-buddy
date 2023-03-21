<?php namespace App\Http\Controllers\Web\Admin;

use App\EmailTemplate;
use App\EmailTemplateLang;
use App\Http\Controllers\Web\Controller;
use App\Http\Requests\Admin\AdminProfileFormRequest;
use App\Repositories\AdminRepository;
use App\Repositories\EmailTemplateLangRepository;
use App\Repositories\EmailTemplateRepository;
use App\Repositories\UserRepository;
use App\Services\BackendService;
use App\Services\EmailService;
use App\Services\HelperService;
use App\Services\TokenService;

class HomeController extends Controller
{
    public function index(UserRepository $userRepository)
    {
        $totalCount = $userRepository->count();

        return view('admin.index', compact('totalCount'));
    }

    /**
     * @param $id
     * @param TokenService $tokenService
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function loginAsUser($id, TokenService $tokenService) {
        $token = $tokenService->generateTokenForUser($id);

        return view('admin.loginAsUser', compact('token', 'id'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function profile()
    {
        $user = \Auth::getUser();

        return view('admin.profile', compact('user'));
    }

    /**
     * @param AdminProfileFormRequest $request
     * @param HelperService $helperService
     * @param AdminRepository $adminRepository
     * @return \Illuminate\Http\RedirectResponse
     */
    public function profileUpdate(AdminProfileFormRequest $request, HelperService $helperService, AdminRepository $adminRepository)
    {
        $user = \Auth::getUser();

        $adminRepository->updateAdmin($user->id, $request->only(['email', 'password', 'name']));

        //upload photo
        if($request->hasFile('photo'))
        {
            (new \App\Services\MediaService())->uploadAdminPhoto($request->file('photo'), $user->id);
        }

        return \Redirect::route('admin.profile')->with('showNotification', 'Profile was successfully updated');
    }

    /**
     * Save menu state
     */
    public function menu()
    {
        $menu = [
            'community'  => request()->get('community') ? true : false,
            'moderation' => request()->get('moderation') ? true : false,
            'admin'      => request()->get('admin') ? true : false,
        ];

        session(['adminMenu' => $menu]);

        return response()->json(['success' => true, 'menu' => $menu]);
    }

    /**
     * Test mail send
     */
    public function sendmail()
    {
        (
            new EmailService(
                new EmailTemplateRepository(EmailTemplate::find(1)),
                new EmailTemplateLangRepository(EmailTemplateLang::find(1))
            )
        )->sendMail(
            'uniwertz@shapelab.ee',
            'Test',
            'Test email header',
            'Test email body'
        );

        return 'ok';
    }
}
