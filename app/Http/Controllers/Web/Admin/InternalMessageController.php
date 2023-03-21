<?php

namespace App\Http\Controllers\Web\Admin;

use App\User;
use App\Repositories\MessageRepository;
use App\Repositories\CountryRepository;
use App\Models\Message\InternalMessagesQueue;
use App\Http\Controllers\Web\Controller;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

class InternalMessageController extends Controller
{
    protected $languages = [
        'en' => "English",
        'de' => "German",
        // 'fr' => "French",
        // 'it' => "Italian",
        // 'nl' => "Dutch",
    ];

    public function index(CountryRepository $countryRepository)
    {
        $senders = User::whereUserGroup(User::GROUP_STAFF)
                       ->pluck('name', 'id');

        $countries = $countryRepository->getCountriesList(false);

        $somethingInQueue = InternalMessagesQueue::where('is_finished', 0)->first();

        return view('admin.internalMessage.index', [
            'senders'     => $senders,
            'languages'   => $this->languages,
            'countries'   => $countries,
            'queueActive' => $somethingInQueue ? true : false,
            'groups'      => [
                'all'  => 'All',
                'free' => 'Free',
                'pro'  => 'PRO',
            ],
        ]);
    }

    public function send(CountryRepository $countryRepository, MessageRepository $messageRepository)
    {
        $groups = ['all', 'free', 'pro'];
        $errors = [];
        $sender = User::whereUserGroup(User::GROUP_STAFF)
                      ->whereId(request()->get('sender_id'))
                      ->first();

        if (!$sender)
            $errors[] = 'Select Sender from list.';

        $finalCountriesList = [];
        $fullCountriesList = $countryRepository->getCountriesList(false);
        $selectedCountriesList = request()->get('countries');

        if (
            $selectedCountriesList
            &&
            is_array($selectedCountriesList)
            &&
            count($selectedCountriesList)
        ) {
            foreach ($selectedCountriesList as $country) {
                if (isset($fullCountriesList[$country]))
                    $finalCountriesList[] = $country;
            }
        } else if ($selectedCountriesList === null) {
            $finalCountriesList = array_keys($fullCountriesList);
        }

        if (!count($finalCountriesList))
            $errors[] = 'Select target Countries from list.';

        $language = request()->get('language');
        if (!isset($this->languages[$language]))
            $errors[] = 'Select target Language from list.';

        $group = request()->get('group');
        if (!in_array($group, $groups))
            $errors[] = 'Select target group from list.';

        $message = request()->get('message');
        if (!$message)
            $errors[] = 'Message is required.';

        if (count($errors))
            return redirect()->back()->withInput()->withErrors($errors);

        /** @var Builder $targetUsers */
        $targetUsers = User::whereIn('country_code', $finalCountriesList);

        if ($language == 'en') {
            $targetUsers = $targetUsers->where('language', '!=', 'de');
        } else {
            $targetUsers = $targetUsers->whereLanguage($language);
        }

        if ($group == 'free') {
            $targetUsers = $targetUsers->where(function($query) {
                /** @var Builder $query */
                $query->whereNull('pro_expires_at')
                      ->orWhere('pro_expires_at', '<', date('Y-m-d H:i:s', strtotime('now')));
            });
        } elseif ($group == 'pro') {
            $targetUsers = $targetUsers->where(function($query) {
                /** @var Builder $query */
                $query->whereNotNull('pro_expires_at')
                      ->orWhere('pro_expires_at', '>=', date('Y-m-d H:i:s', strtotime('now')));
            });
        }

        $targetUsers = $targetUsers
            ->whereIn('status', ['active', 'deactivated'])
            ->whereNull('deleted_at')
            ->pluck('id');

        /** @var Collection $targetUsers */
        if ($targetUsers->isEmpty())
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['There are no members in this locale/countries...']);

        $result = $messageRepository->massSendMessage($sender, $targetUsers->toArray(), $message);
        if ($result) {
            return redirect()
                ->back()
                ->with('successMessage', count($targetUsers).' Messages has been successfully sent to queue.');
        }

        return redirect()
            ->back()
            ->withInput()
            ->withErrors(['Something went wrong. Messages were not sent.']);
    }
}
