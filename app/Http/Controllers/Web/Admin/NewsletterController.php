<?php namespace App\Http\Controllers\Web\Admin;

use App\Services\EmailService;
use App\Models\Mail\NewsletterSchedule;
use App\Models\Mail\NewsletterScheduleMember;
use App\Repositories\CountryRepository;
use App\Repositories\NewsletterRepository;

use Illuminate\Http\Request;
use App\Http\Controllers\Web\Controller;

class NewsletterController extends Controller
{
    private function getLanguages($header = false) {
        $list = [
            'en' => "English",
            'de' => "German",
            'fr' => "French",
            'it' => "Italian",
            'nl' => "Dutch",
        ];

        if ($header) {
            $list = ['' => $header] + $list;
        }

        return $list;
    }

    /**
     * @param CountryRepository $countryRepository
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(CountryRepository $countryRepository, EmailService $emailService)
    {
        $countries = $countryRepository->getCountriesList(false, '---');

        $languages = $this->getLanguages('---');

//        $trashedOptions = [
//            'all' => '---',
//            'only_deleted' => 'Only deleted accounts',
//            'except_deleted' => 'Except deleted accounts',
//            'only_suspended' => 'Only suspended accounts',
//            'except_suspended' => 'Except suspended accounts',
//            'only_deleted_suspended' => 'Only deleted or suspended',
//            'except_deleted_suspended' => 'Except deleted and suspended',
//        ];

        return view('admin.newsletter.index', compact('countries', 'languages'));
    }

    /**
     * @param Request $request
     * @param NewsletterRepository $newsletterRepository
     * @param EmailService $emailService
     * @return int|mixed
     */
    public function send(Request $request, NewsletterRepository $newsletterRepository, EmailService $emailService)
    {
        $filterCountry  = $request->get('filterCountry');
        $filterLanguage = $request->get('filterLanguage');
        $isPreviewMail  = $request->get('sendPreview');

        if ($isPreviewMail) {
            $newsletterUsers = $newsletterRepository->where('email', $request->get('preview-mail'))->get();
        } else {
            $newsletterUsers = $newsletterRepository->getAllNewsletterUsers($filterCountry, $filterLanguage);
        }

        $totalUsersCount = count($newsletterUsers);

        if ($request->get('getCount')) {
            return "Email will be sent to $totalUsersCount users";
        }

        $validationLanguage = $filterLanguage ?: 'en';

        $subjectArray = $request->get('subject');
        $bodyArray    = $request->get('body');

        $body = $request->get('body');
        $subject = $request->get('subject');

        if (empty($subject[$validationLanguage])) {
            return \Response::json("Subject for language ".strtoupper($validationLanguage).' is required!', 422);
        }

        if (empty($body[$validationLanguage])) {
            return \Response::json("Body for language ".strtoupper($validationLanguage).' is required!', 422);
        }

        foreach ($bodyArray as $key => $item) {
            if ($item && strpos($item, '{UNSUBSCRIBE_LINK}') === false) {
                return \Response::json("{UNSUBSCRIBE_LINK} is missing in body for $key", 422);
            }
        }

        $substitutionVars   = ['{FULL_NAME}', '{EMAIL}', '{UNSUBSCRIBE_LINK}'];
        $substitutionValues = ['{{FULL_NAME}}', '{{EMAIL}}', '{{UNSUBSCRIBE_LINK}}'];

        foreach ($subjectArray as $key => $item) {
            if (!empty($item)) {
                $subjectArray[$key] = str_replace($substitutionVars, $substitutionValues, $subjectArray[$key]);
                $bodyArray[$key]    = str_replace($substitutionVars, $substitutionValues, $bodyArray[$key]);
            }
        }

        $mailInformation = [];
        foreach ($newsletterUsers as $newsletterUser) {
            if ($isPreviewMail) {
                $language = $validationLanguage;
            } else {
                $language = $newsletterUser->language ?: 'en';
            }

            $subject = !empty($subjectArray[$language]) ? $subjectArray[$language] : $subjectArray['en'];
            $body    = !empty($bodyArray[$language]) ? $bodyArray[$language] : $bodyArray['en'];

            if (!$subject || !$body) {
                \Log::error('Subject or body not available for newsletter: ' . print_r($request->all(), 1));

                continue;
            }

            if (!isset($mailInformation[$language])) {
                $mailInformation[$language]['subject'] = $subject;
                $mailInformation[$language]['body']    = $body;
            }

            $mailInformation[$language]['recipients'][] = $newsletterUser->user_id;
        }

        if (count($mailInformation)) {
            $returnOutput = '';

            foreach ($mailInformation as $lang => $mailInfo) {
                $newsletterSchedule = new NewsletterSchedule();

                $newsletterSchedule->subject = $mailInfo['subject'];
                $newsletterSchedule->body    = $mailInfo['body'];

                $newsletterSchedule->save();

                $recipients = [];
                foreach ($mailInfo['recipients'] as $recipient) {
                    $recipients[] = [
                        'user_id'     => $recipient,
                        'schedule_id' => $newsletterSchedule->id,
                    ];
                }

                $countRecepients = count($recipients);
                if ($countRecepients) {
                    $chunks = array_chunk($recipients, round(65000 / 2));
                    foreach ($chunks as $chunk) {
                        NewsletterScheduleMember::insert($chunk);
                    }

                    $returnOutput .= " Mail was successfully queued to ". $countRecepients ." users for language $lang. ";
                }
            }

            return $returnOutput;
        } else {
            return 'There is nothing to send';
        }
    }
}
