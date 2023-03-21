<?php namespace App\Services;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;
use Request;

class HelperService
{
    /**
     * Get per-page list to be displayed in templates
     *
     * @return array
     */
    public function getPerPageArray()
    {
        return config()->get('app.perPageArray');
    }

    /**
     * Get the default selected per page number
     *
     * @return int
     */
    public function getDefaultPerPageNumber()
    {
        $list = $this->getPerPageArray();
        return $list[0];
    }

    /**
     * Get the user filter preference from: 1) $_GET, 2) or from Session, 3) or $defaultValue if nothing is set yet.
     * @param $sessionKey - the session key to use for get/set
     * @param $variableName - variable name to check
     * @param $defaultValue - default value if nothing in session
     * @param $resetValueToDefault - reset saved value to the default one
     */
    public function getUserPreference($sessionKey, $variableName, $defaultValue = '', $resetValueToDefault = false)
    {
        $sessionVariableName = $sessionKey . '.' . $variableName;

        if ($resetValueToDefault) {
            session([$sessionVariableName => $defaultValue]);
        } elseif (Request::exists($variableName)) {
            session([$sessionVariableName => Request::get($variableName)]);
        }

        if (!session()->has($sessionVariableName)) {
            session([$sessionVariableName => $defaultValue]);
        }

        return session($sessionVariableName, $defaultValue);
    }

    /**
     * @param $sessionKey
     * @param $variableName
     * @return mixed
     */
    public function getSessionUserPreference($sessionKey, $variableName, $defaultValue = '')
    {
        $sessionVariableName = $sessionKey . '.' . $variableName;

        return session($sessionVariableName, $defaultValue);
    }

    /**
     * @param $userType
     * @param $permission
     * @return bool
     */
    public function userHasPermission($userType, $permission, $ownerId)
    {
        /** @var UserPermissionRepository $userPermissionRepository */
        $userPermissionRepository = \App::make('\App\Repositories\UserPermissionRepository');
        $allPermissions = $userPermissionRepository->getUserPermissions($ownerId);
        return $allPermissions[$userType][$permission] == 'yes';
    }

    public function loginFromAdminToUser($userId)
    {
        \Session::set('impersonated_admin_id', \Auth::user()->id);
        \Auth::loginUsingId($userId);
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function loginBackToAdmin($backRoute = '')
    {
        if (\Session::get('impersonated_admin_id')) {
            \Auth::loginUsingId(\Session::get('impersonated_admin_id'));
            \Session::forget('impersonated_admin_id');
        }
    }

    /**
     * @param $table
     * @param $column
     * @param $header
     * @return array
     */
    public function getEnumOptions($table, $column, $header = false)
    {
        $results = \DB::select("SHOW COLUMNS FROM `" . addslashes($table) . "` LIKE '" . addslashes($column) . "'");
        $enumArr = explode(',', substr($results[0]->Type, 5, -1));

        $enumList = [];
        if ($header) {
            $enumList[''] = $header;
        }

        foreach ($enumArr as $enum) {
            $enum = substr($enum, 1, -1);
            $enumList[$enum] = ucfirst($enum);
        }

        return $enumList;
    }

    /**
     * @param $distance
     * @return string
     */
    public function formatDistanceForHumans($distanceMeters, $unitSystem = 'metric')
    {
        if ($unitSystem == 'metric') {
            if ($distanceMeters < 1000) {
                return intval($distanceMeters) . 'm';
            } else {
                return number_format($distanceMeters / 1000, 1, '.', '') . 'km';
            }
        } else {
            return number_format($distanceMeters / 1000 / 1.609344, 1, '.', '') . 'mi';
        }
    }

    /**
     * Format a Carbon date
     * @param Carbon $date
     * @return string
     */
    public function formatDate($date, $format = 'F j, Y')
    {
        if (!$date instanceof Carbon) {
            return '---';
        }

        if ($this->isEmptyDate($date)) {
            return '---';
        }

        $formattedDate = $date->format($format);

        if (\App::isLocale('de')) {
            $monthsEnglish = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            $monthsGerman = ['Januar', 'Februar', 'Marz', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'];
            return str_replace($monthsEnglish, $monthsGerman, $formattedDate);
        }

        return $formattedDate;
    }

    public function formatDateMysql($dateString, $format = 'F j, Y')
    {
        if (is_null($dateString)) {
            $dateString = '0000-00-00';
        }
        $carbonDate = Carbon::createFromFormat('Y-m-d', $dateString);

        return $this->formatDate($carbonDate, $format);
    }

    /**
     * @param $date
     * @param $format
     * @return string
     */
    public function formatEventDate($date, $format): string
    {
        $carbonDate = Carbon::createFromFormat('Y-m-d', $date);

        return $this->formatDate($carbonDate, $format);
    }

    public function formatDateTimeMysql($dateString, $format = 'F j, Y')
    {
        if (is_null($dateString)) {
            $dateString = '0000-00-00 00:00:00';
        }
        $carbonDate = Carbon::createFromFormat('Y-m-d H:i:s', $dateString);

        return $this->formatDate($carbonDate, $format);
    }

    /**
     * Format a datetime
     * @param $date
     * @return string
     */
    public function formatDateTime($date, $format = 'F j, Y H:i')
    {
        return $this->formatDate($date, $format);
    }

    /**
     * @param $date
     * @return string
     */
    public function formFormatDate($date)
    {
        if (!$date instanceof Carbon) {
            return '';
        }

        if ($this->isEmptyDate($date)) {
            return '';
        }

        return $date->format($this->formDateFormat('php'));
    }

    /**
     * @param $date
     * @return string
     */
    public function formFormatDateTime($date)
    {
        if (!$date instanceof Carbon) {
            return '';
        }

        if ($this->isEmptyDate($date)) {
            return '';
        }

        return $date->format($this->formDateTimeFormat('php'));
    }

    /**
     * @param $language : php|js
     * @return string
     */
    public function formDateFormat($language)
    {
        if ($language == 'php') {
            return 'd/m/Y';
        } elseif ($language == 'js') {
            return 'dd/mm/yyyy';
        }
    }

    /**
     * @param $language : php|js
     * @return string
     */
    public function formDateTimeFormat($language)
    {
        if ($language == 'php') {
            return 'd/m/Y H:i';
        } elseif ($language == 'js') {
            return 'dd/mm/yyyy hh:ii';
        }
    }

    /**
     * @param $var
     * @param array $keys
     */
    public function convertDateStringToCarbon($var, array $keys)
    {
        foreach ($keys as $key) {
            if (!empty($var[$key]) && is_string($var[$key])) {
                $var[$key] = Carbon::createFromFormat(\Helper::formDateFormat('php'), $var[$key]);
            }
        }

        return $var;
    }

    /**
     * Check if a date is not set
     * @param Carbon $date
     */
    public function isEmptyDate(Carbon $date)
    {
        if ($date->year <= 0 || $date == '-infinity' || $date == '+infinity') {
            return true;
        }

        return false;
    }

    /**
     * Format a phone number
     * @param $phone
     */
    public function formatPhone($phone)
    {
        if (preg_match('/^(\d*)(\d{3})(\d{3})(\d{4})$/', $phone, $matches)) {
            $result = $matches[1] . ($matches[1] ? ' ' : '') . '(' . $matches[2] . ') ' . $matches[3] . '-' . $matches[4];
            return $result;
        } elseif (preg_match('/^(\d{3})(\d{4})$/', $phone, $matches)) {
            $result = $matches[1] . '-' . $matches[2];
            return $result;
        }

        return $phone;
    }

    /**
     * Format a money number
     */
    public function formatMoney($money)
    {
        return '$' . number_format($money, 2);
    }

    /**
     * @return array
     */
    public function getMonthsList()
    {
        $return = [];
        $date = Carbon::create(2010, 12, 15);
        for ($i = 0; $i < 12; $i++) {
            $date->addMonth();
            $return[$date->format('m')] = $date->format('F');
        }

        return $return;
    }

    /**
     * @return array
     */
    public function getYearsList()
    {
        $return = [];
        foreach (range(date('Y'), date('Y') + 8) as $year) {
            $return[$year] = $year;
        }

        return $return;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param $key
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getModelRelation(Model $model, $key, $with = false)
    {
        if (array_key_exists($key, $model->getRelations()) && $model->{$key} === null) {
            $model->load($key);
        }

        if ($with !== false) {
            $model->load($with);
        }

        return $model->{$key};
    }

    /**
     * @return array|string
     */
    public function getPageTitle()
    {
        $url = \Request::path();

        $pointPos = stripos($url, '.');
        if ($pointPos !== false) {
            $url = substr($url, 0, $pointPos);
        }

        return ucwords(str_replace(['-', '_', '/', '.'], ' ', $url));
    }

    public function getOrderByLink($baseUrl, $sessionKey, $sortText, $sortKey)
    {
        $orderBy = $this->getSessionUserPreference($sessionKey, 'orderBy');
        $orderBySort = $this->getSessionUserPreference($sessionKey, 'orderBySort');

        $sortOrder = 'asc';
        $faSort = '';
        if ($orderBy == $sortKey) {
            $sortOrder = ($orderBySort == 'asc' ? 'desc' : 'asc');
            $faSort = ($orderBySort == 'asc' ? 'fa-sort-asc' : 'fa-sort-desc');
        }

        return '<a href="' . $baseUrl . '?orderBy=' . $sortKey . '&orderBySort=' . $sortOrder . '">' . $sortText . ' <i class="fa ' . $faSort . '"></i></a>';
    }

    /**
     * @param $text
     * @return mixed
     */
    public function extractAdNumberFromText($text)
    {
        if (preg_match('/^AD (\S+)/', $text, $m)) {
            if (isset($m[1])) {
                return $m[1];
            }
        }

        return $text;
    }

    /**
     * @param $url
     * @return mixed
     */
    public function removeHttpFromUrl($url)
    {
        return preg_replace('#^https?://#', '', rtrim($url, '/'));
    }

    /**
     * Used for json formatting. Jquery will reorder items if returned in id:value format
     *
     * @param $array
     */
    public function convertArrayToKeyValueFormat($list)
    {
        $array = [];
        foreach ($list as $key => $value) {
            if (is_array($value)) {
                $valueArray = $this->convertArrayToKeyValueFormat($value);
                $array[] = ['key' => $key, 'value' => $valueArray];
            } else {
                $array[] = ['key' => $key, 'value' => $value];
            }
        }

        return $array;
    }

    /**
     * @param $lng
     * @param $lat
     * @return mixed
     */
    public function getGpsGeom($lng, $lat)
    {
        $sql = "SELECT point(:longitude, :latitude) AS gps";
        //TODO: use here ? SELECT ST_SRID(ST_GeomFromText('LineString(1 1,2 2)',4326));
        $geom = \DB::select($sql, ['longitude' => $lng, 'latitude' => $lat]);
        return $geom[0]->gps;
    }

    /**
     * @param $timezoneName - Europe/Chisinau
     */
    public function changeApplicationTimezone($timezoneName)
    {
        \Config::set('app.timezone', $timezoneName);
        putenv('APP_TIMEZONE=' . $timezoneName);
        date_default_timezone_set($timezoneName);

        $offset = Carbon::now()->offset; //this is with the newly set timezone
        $mysqlTZ = ($offset < 0 ? '-' : '+') . Carbon::createFromTimestampUTC(abs($offset))->format('H:i');
        \DB::update("SET time_zone = '" . $mysqlTZ . "' ");
    }

    /**
     * @param $newLanguage
     */
    public function changeApplicationLanguage($newLanguage)
    {
        $locales = config('app.locales');

        $newLanguage = isset($locales[$newLanguage]) ? $newLanguage : 'en';

        session(['lang' => $newLanguage]);

        setcookie('lang', $newLanguage, time() + 60 * 60 * 24 * 30, '/');

        $_COOKIE['lang'] = $newLanguage;
    }

    /**
     * Set application's locale
     */
    public function setApplicationLocale()
    {
        $sessionLang = session('lang', false);

        if (isset($_COOKIE['lang']) && $_COOKIE['lang'] != \App::getLocale()) {
            \App::setLocale($_COOKIE['lang']);
        } elseif ($sessionLang && $sessionLang != \App::getLocale()) {
            \App::setLocale($sessionLang);
        }

        \Carbon\Carbon::setLocale(\App::getLocale());
    }

    /**
     * @param $code
     * @return mixed
     */
    public function verifyCaptchaCode($code)
    {
        $client = new Client();

        $captchaResponse = $client->post(
            'https://www.google.com/recaptcha/api/siteverify',
            ['form_params' =>
                [
                    'secret' => config('const.RECAPTCHA_PRIVATE_KEY'),
                    'response' => $code
                ]
            ]
        );

        $captchaBody = json_decode((string)$captchaResponse->getBody());

        return $captchaBody->success && $captchaBody->score >= (float) config('const.RECAPTCHA_SCORE');
    }

    /**
     * @return bool
     */
    public function adultContentEnabled()
    {
        return config('app.adult_content_enabled', true);
    }

    /**
     * @param bool $value
     *
     * @return bool
     */
    public function toggleAdultContent(bool $value)
    {
        return config(['app.adult_content_enabled' => $value]);
    }

    /**
     * @return bool
     */
    public function censorshipEnabled()
    {
        // check tester IP
        $clientIp = self::getClientIp();
        if ($clientIp == config('app.testing_ip')) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function getClientIp()
    {
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
            if (array_key_exists($key, $_SERVER) === true){
                foreach (explode(',', $_SERVER[$key]) as $ip){
                    $ip = trim($ip); // just to be safe
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE || FILTER_FLAG_NO_RES_RANGE) !== false){
                        return $ip;
                    }
                }
            }
        }

        return request()->ip();
    }

    /**
     * @return bool
     */
    public function isAppDomain()
    {
        return preg_match('/^app\./ui', request()->getHttpHost());
    }

    /**
     * @return bool
     */
    public function isApp(){
        return config('app.is_mobile_api', false);
    }

    /**
     * @return bool
     */
    public function isMarketplace(){
        return $this->isAppDomain() || $this->isApp();
    }

    /**
     * @return string
     */
    public function getUserIpFromRequest()
    {
        $ipHeader = request()->header('X-Forwarded-For', null);
        if ($ipHeader) {
            $ipHeader = trim(explode(',', $ipHeader)[0]);
        }

        return $ipHeader ? $ipHeader : request()->ip();
    }
}