<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Web\Controller;
use App\Repositories\MessageRepository;
use App\Repositories\UserRepository;
use App\Repositories\VideoRepository;
use App\Services\BackendService;
use Illuminate\Support\Facades\File;

class TempController extends Controller
{
    /**
     * @param UserRepository $userRepository
     * @param MessageRepository $messageRepository
     * @return string
     */
    function sendCustomNotification(UserRepository $userRepository, MessageRepository $messageRepository)
    {
        $users = $userRepository
            ->where('status', 'active')
            ->whereNull('deleted_at')
//            ->where('country_code', 'DE')
//            ->where(function ($query) {
//                $query->where('locality', 'Cologne')
//                    ->orWhere('locality', 'Koln');
//            })
            ->orderBy('id', 'asc')
            ->get();

//        $users = $userRepository->where('id', 2)->get();

        $newMessagesDe = $newMessagesEn = [];

        $newMessagesDe[] = [
            'msg_type' => 'text',
            'message' => 'Hey Buddy,

viele sind auf Grund der derzeitigen Umstände verunsichert.
Dafür haben wir alle bei Buddy größtes Verständnis.

Bitte handele nach den Empfehlungen Deines Gesundheitsamtes und zeig Verantwortung für Dich und andere Buddies. Selbstverständlich gehst Du nicht zu einem Date, wenn Du Dich unwohl fühlst. Behalte einen kühlen Kopf, verzichte momentan lieber auf das eine oder andere Date und schicke oder teile dafür ein Video mehr im Profil! ;)

Pass auf Dich auf und bleib gesund.
Deine BUDDYbuilder.'
        ];

        $newMessagesEn[] = [
            'msg_type' => 'text',
            'message' => 'Hey Buddy,

many of you are concerned or even anxious about the current situation.
We at BUDDY understand this.

Please act according to the advice of your local health authorities and be responsible for yourself and other buddies.
Of course, you are not going on a date when you feel sick. Please remain calm and just skip one or another date for now. Instead, send or share more videos of yourself ;)

Watch out for yourself and stay healthy.
Your BUDDYbuilders.'
        ];

//        if (\App::environment() == 'local') {
//            $newMessagesDe[] = [
//                'msg_type' => 'video',
//                'message' => '9/9J/9JfYt7vwTbHArD1yRSDYXIVthxueLtkWEIsXd7V1',
//                'video_id' => '63'
//            ];
//
//            $newMessagesEn[] = [
//                'msg_type' => 'video',
//                'message' => '9/9J/9JfYt7vwTbHArD1yRSDYXIVthxueLtkWEIsXd7V1',
//                'video_id' => '63'
//            ];
//        } else {
//            $newMessagesDe[] = [
//                'msg_type' => 'video',
//                'message' => 'Z/Zi/ZiL2T7i1KhU2mJXAaP6JRfcHKieAlRBWTB191FpN',
//                'video_id' => '1'
//            ];
//
//            $newMessagesEn[] = [
//                'msg_type' => 'video',
//                'message' => 'Z/Zi/ZiL2T7i1KhU2mJXAaP6JRfcHKieAlRBWTB191FpN',
//                'video_id' => '1'
//            ];
//        }

        foreach ($users as $user) {
            if ($user->id == config('const.BB_USER_ID')) {
                continue;
            }

            $newMessages = $user->language == 'de' ? $newMessagesDe : $newMessagesEn;

            foreach ($newMessages as $newMessage) {
                $data = $newMessage;

                $data['user_from'] = config('const.BB_USER_ID');
                $data['user_to'] = $user->id;
                $data['is_read'] = 'no';

                $message = $messageRepository->createMessage($data, $data['msg_type']);

                event(new \App\Events\NewMessageReceived($message->toArray()));
            }

            $userRepository->updateUser($user->id, ['has_new_messages' => true]);
        }

        return 'sent to '.count($users);
    }

    /**
     * @param UserRepository $userRepository
     * @return string
     */
    function updateLocality(UserRepository $userRepository)
    {
        return 'disabled for now';

//        $users = $userRepository->whereNull('locality')->orWhere('locality', 'undefined')->orderBy('id', 'asc')->get();
        $users = $userRepository->where('country_code', '')->orderBy('id', 'asc')->get();
//        $users = $userRepository->whereIn('id', ['2858'])->orderBy('id', 'asc')->get();

        foreach ($users as $user) {
            if ($user->lat) {
                $url  = "https://maps.googleapis.com/maps/api/geocode/json?latlng=".$user->lat.",".$user->lng."&language=en&sensor=false&key=".config('const.GMAP_API_KEY');
                $json = @file_get_contents($url);
                $data = json_decode($json);

                if (is_null($data)) {
                    dd('error', $data);
                }

                if($data->status == "OK") {
                    $comp = $data->results[0]->address_components;

                    $locality = array_filter($comp, function($v) {
                        return in_array('locality', $v->types);
                    });

                    $state = array_filter($comp, function($v) {
                        return in_array('administrative_area_level_1', $v->types);
                    });

                    $country = array_filter($comp, function($v) {
                        return in_array('country', $v->types);
                    });

                    $address = $data->results[0]->formatted_address;

                    try {
                        $locality = array_shift($locality);
                        $state = array_shift($state);
                        $country = array_shift($country);

                        $countryName = is_null($country) ? '' : $country->long_name;
                        $countryCode = is_null($country) ? '' : $country->short_name;
                        $stateName = is_null($state) ? '' : $state->long_name;
                        $localityName = is_null($locality) ? (is_null($state) ? '' : $state->long_name) : $locality->long_name;

                        $user->locality = $localityName;
                        $user->state = $stateName;
                        $user->country = $countryName;
                        $user->country_code = $countryCode;
                        $user->address = $address;
                        $user->save();
                    } catch (\Exception $e) {
                        \Debugbar::info($user);
                        \Debugbar::info($data);

                        return $e->getMessage();
                    }
                }
            }
        }

        return 'ok';
    }

    /**
     * @param BackendService $backendService
     */
    public function removeOriginalVideos(BackendService $backendService) {
        $log = $backendService->removeOriginalVideos();

        echo $log;
    }
}