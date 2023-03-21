<?php

namespace App\Http\Controllers\Web;

use App\Repositories\NewsletterRepository;
use App\Repositories\PageRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function __construct()
    {

    }

    /**
     * @param $lang
     * @param $pageSlug
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|string
     */
    public function getStaticPage($lang, $pageSlug, PageRepository $pageRepository) {
        $page = $pageRepository->findByUrl($pageSlug, $lang);
        if (!is_null($page)) {
            return $page;
        }

        $page = $pageRepository->findByUrl($pageSlug, 'en');
        if (!is_null($page)) {
            return $page;
        }

        return \Response::json('404', 404);
    }

    /**
     * @param Request $request
     */
    public function sparkpostWebhook(Request $request, UserRepository $userRepository, NewsletterRepository $newsletterRepository)
    {
        $resultAll = $request->all();

        //this is check that webhook url works when you add it in sparkpost area
        if (empty($resultAll[0]['msys'])) {
            return \Response::json('ok', 200);
        }

        foreach ($resultAll as $result) {
            $type = $receiverEmail = $bounceClass = false;

            if (empty($result['msys']['message_event'])) {
                continue;
            }

            if (!empty($result['msys']['message_event']['type'])) {
                $type = $result['msys']['message_event']['type'];
            }

            if (!empty($result['msys']['message_event']['rcpt_to'])) {
                $receiverEmail = $result['msys']['message_event']['rcpt_to'];
            }

            if (!empty($result['msys']['message_event']['bounce_class'])) {
                $bounceClass = $result['msys']['message_event']['bounce_class'];
            }

            $user = $newsletter = null;

            if ($receiverEmail) {
                $user = $userRepository->findByEmail($receiverEmail);

                $newsletter = $newsletterRepository->getNewsletterByMail($receiverEmail);
            }

            if (is_null($user) && is_null($newsletter)) {
                \Log::info('Unknown email in users or newsletter: ' . $receiverEmail);

                \Log::info(print_r($request->all(), 1));

                continue;
            }

            if ($type == 'bounce') {
                // https://www.sparkpost.com/docs/deliverability/bounce-classification-codes/?_ga=2.95476698.777481772.1536608466-1893163187.1528143297
                if (in_array($bounceClass, [10, 30, 90])) {
                    if (!is_null($newsletter)) {
                        $newsletterRepository->update($newsletter->id, ['email_validation' => 'bounce']);
                    }

                    if (!is_null($user)) {
                        $userRepository->updateUser($user->id, ['email_validation' => 'bounce']);
                    }
                }
            } elseif ($type == 'delivery') {
                if (!is_null($newsletter)) {
                    $newsletterRepository->update($newsletter->id, ['email_validation' => 'delivery']);
                }

                if (!is_null($user)) {
                    $userRepository->updateUser($user->id, ['email_validation' => 'delivery']);
                }
            } else {
                \Log::info('Unknown webhook type received');

                \Log::info(print_r($request->all(), 1));
            }
        }

        return \Response::json('ok', 200);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sparkpostGeneration(Request $request)
    {
        \Log::info('GENERATION EVENT webhook received');

        \Log::info(print_r($request->all(), 1));

        return response()->json('ok', 200);
    }

    /**
     * @param Request $request
     */
    public function newsletterUnsubscribe($id, $slug, NewsletterRepository $newsletterRepository) {
        $success = $newsletterRepository->unsubscribeByIdAndUniqueKey($id, $slug);

        if ($success) {
            return "<div style='font-size: 26px; text-align:center; padding: 50px; color: green;'>You've been successfully unsubscribed</div>";
        } else {
            return "<div style='font-size: 26px; text-align:center; padding: 50px; color: red;'>Something went wrong. Couldn't process your request.</div>";
        }
    }
}