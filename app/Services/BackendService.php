<?php namespace App\Services;

use App\Repositories\EventRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Telegram\Bot\Api;
use App\Enum\ProTypes;

use App\User;
use App\Message;
use App\Event;
use App\UserVideo;

class BackendService
{
    /**
     * @return array
     */
    public function getPredefinedHeights($header = false)
    {
        $min = 150;
        $max = 212;
        $heights = [];

        foreach(range($min, $max) as $number)
        {
            $feet = $number * 0.032808;
            $feetInt = floor($feet);
            $inches = round(($feet - $feetInt) * 12);
            if ($inches == 12) {
                $inches = 0;
                $feetInt++;
            }
            $heights[$number] = "{$number}cm ({$feetInt}'{$inches}”)";
        }

        if($header) {
            $heights = ['0' => $header] + $heights;
        }

        return $heights;
    }

    /**
     * @return array
     */
    public function getPredefinedWeights($header = false)
    {
        $min = 45;
        $max = 125;
        $weights = [];

        foreach(range($min, $max) as $number)
        {
            $lbs = round($number * 2.2046226218);
            $weights[$number] = "{$number}kg ({$lbs} lbs)";
        }

        if($header) {
            $weights = ['0' => $header] + $weights;
        }

        return $weights;
    }

    /**
     * @return array
     */
    public function getPredefinedBodyTypes($header = false)
    {
        $options = ['slim', 'average', 'athletic', 'muscular', 'stocky'];

//        $body = [
//            'Slim' => 'Slim',
//            'Average' => 'Average',
//            'Athletic' => 'Athletic',
//            'Muscular' => 'Muscular',
//            'Stocky' => 'Stocky',
//        ];

        $body = [];

        foreach ($options as $option) {
            $body[$option] = $option;
        }

        if($header) {
            $body = ['' => $header] + $body;
        }

        return $body;
    }

    /**
     * @return array
     */
    public function getPredefinedPenisSizes($header = false)
    {
        $penis = [
            'S' => 'S',
            'M' => 'M',
            'L' => 'L',
            'XL' => 'XL',
            'XXL' => 'XXL',
        ];

        if($header) {
            $penis = ['' => $header] + $penis;
        }

        return $penis;
    }

    /**
     * @return array
     */
    public function getPredefinedPositionTypes($header = false)
    {
        $options = ['top', 'more_top', 'versatile', 'more_bottom', 'bottom'];

        $position = [];

//        $position = [
//            'Top' => 'Top',
//            'Bottom' => 'Bottom',
//            'Versatile' => 'Versatile',
//        ];

        foreach ($options as $option) {
            $position[$option] = $option;
        }

        if($header) {
            $position = ['' => $header] + $position;
        }

        return $position;
    }

    /**
     * @return array
     */
    public function getPredefinedHivTypes($header = false)
    {
        $options = ['positive', 'negative', 'undetectable', 'prep', 'unknown'];

        $hiv = [];

//        $hiv = [
//            'Positive' => 'Positive',
//            'Negative' => 'Negative',
//            'Undetectable' => 'Undetectable',
//            'PrEP' => 'PrEP',
//            'Unknown' => 'Unknown',
//        ];

        foreach ($options as $option) {
            $hiv[$option] = $option;
        }

        if($header) {
            $hiv = ['' => $header] + $hiv;
        }

        return $hiv;
    }

    /**
     * @return array
     */
    public function getPredefinedDrugsTypes($header = false)
    {
        $options = ['yes', 'no', 'socially'];

        $drugs = [];

//        $drugs = [
//            'Yes' => 'Yes',
//            'No' => 'No',
//            'Socially' => 'Socially',
//        ];

        foreach ($options as $option) {
            $drugs[$option] = $option;
        }

        if($header) {
            $drugs = ['' => $header] + $drugs;
        }

        return $drugs;
    }

    /**
     * @param $lat1
     * @param $lng1
     * @param $lat2
     * @param $lng2
     * @return int
     */
    public function getDistanceMetersBetween($lat1, $lng1, $lat2, $lng2) {
        //this way of calculation takes 0.05 seconds for 10K calls
        //but it has an error of about 12 meters for each 259 km distance - it's acceptable for us
        $radlat1 = M_PI * $lat1/180;
        $radlat2 = M_PI * $lat2/180;
        $theta = $lng1-$lng2;
        $radtheta = M_PI * $theta/180;
        $dist = sin($radlat1) * sin($radlat2) + cos($radlat1) * cos($radlat2) * cos($radtheta);

        if ($dist > 1) {
            $dist = 1;
        }

        $dist = acos($dist);
        $dist = $dist * 180/M_PI;
        $dist = $dist * 60 * 1.1515;

        $dist = $dist * 1.609344 * 1000; //convert to meters

        return $dist;

        //this way of calculation takes 4.9 seconds for 10K calls
//        $sql = "SELECT ST_Distance_sphere(point(:lng1, :lat1), point(:lng2, :lat2)) As distance";
//        $distance = \DB::select($sql, ['lng1' => $lng1, 'lat1' => $lat1, 'lng2' => $lng2, 'lat2' => $lat2]);
//        return $distance[0]->distance;
////        return number_format($distance, 1, '.', '');
    }

    /**
     * @param $address
     * @return string
     */
    public function extractLocationFromAddress($address) {
        $addressArr = explode(', ', $address);

        if (count($addressArr) > 1) {
            return trim($addressArr[count($addressArr) -2 ]);
        } else {
            return $address;
        }
    }

    /**
     * @param User $userToLoad
     * @param User $displayForUser
     * @return User
     */
    public function loadSinglePublicUserAttributes(User $userToLoad, User $displayForUser) {
        $userObjectAttributes = $userToLoad->getAttributes() + $userToLoad->getRelations();

        if (!array_key_exists('tags', $userObjectAttributes)) {
            $userToLoad->load('tags');
        }

        if (!array_key_exists('publicPhotos', $userObjectAttributes)) {
            $userToLoad->load('publicPhotos');
        }

        if (!array_key_exists('publicVideos', $userObjectAttributes)) {
            $userToLoad->load('publicVideos');
        }

        if (!array_key_exists('user_favorite_id', $userObjectAttributes)) {
            if (!array_key_exists('isFavoriteRelation', $userObjectAttributes)) {
                $userToLoad->load(['isFavoriteRelation' => function($query) use ($userToLoad) {
                    $query->where('user_id', '=', $userToLoad->id);
                }]);
            }

            $userToLoad['user_favorite_id'] = count($userToLoad->isFavoriteRelation);
        }

        if (!array_key_exists('distanceMeters', $userObjectAttributes)) {
            $userToLoad['distanceMeters'] = $this->getDistanceMetersBetween($userToLoad->lat, $userToLoad->lng, $displayForUser->lat, $displayForUser->lng);
        }

        return $userToLoad;
    }

    /**
     * @return int
     */
    public function getVideosServerFreeSpace() {
        if (\App::environment() == 'local') {
            $directory = "dev/sda2";
        } else {
            $directory = "/nfs/videos";
        }

        $output = exec("df -h |grep $directory");
        if (preg_match_all('(\S+)', $output, $array)) {
            $available = $array[0][3];
            $amount = substr($available, 0, strlen($available)-1);
            $type = substr($available, strlen($available)-1, 1);

            if ($type == 'G') $amount *= 1024;
            if ($type == 'K') $amount /= 1024;

            return $amount;
        } else {
            return 0;
        }
    }

    /**
     * @return string
     */
    public function removeOriginalVideos() {
        $deletedVideosCount = 0;
        $notDeletedVideos = [];

        UserVideo::where('status', 'processed')
            ->chunk(1000, function ($videos) use (&$deletedVideosCount, &$notDeletedVideos) {
                /** @var UserVideo $video */
                foreach ($videos as $video) {
                    if ($video->hasSourceVideo()) {
                        $video->deleteSourceVideo();
                        if (!$video->hasSourceVideo()) {
                            $deletedVideosCount++;
                        } else {
                            $notDeletedVideos[] = $video->getSourceVideoFullPath();
                        }
                    }
                }
            });

        $message =
            "Removed $deletedVideosCount videos\n" .
            (
                !empty($notDeletedVideos) ?
                    "WARNING! Cannot delete " . count($notDeletedVideos) . " videos:\n" . implode("\n", $notDeletedVideos)
                    :
                    ''
            );

        return $message;
    }

    /**
     * @param $message
     */
    public function sendTelegramNotification($message) {
        $telegram = new Api(config('const.TELEGRAM_BOT_TOKEN'));

        $telegram
            ->setAsyncRequest(true)
            ->sendMessage([
                'chat_id' => config('const.TELEGRAM_CHAT_ID'),
                'text' => $message
            ]);
    }
}
