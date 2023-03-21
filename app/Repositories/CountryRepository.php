<?php namespace App\Repositories;

use App\Country;
use Carbon\Carbon;

class CountryRepository extends BaseRepository
{
    private $cacheKey = 'countries-list2';

    /**
     * CountryRepository constructor.
     * @param Country $model
     */
    public function __construct(Country $model = null)
    {
        if (empty($model)){
            $model = new Country();
        }
        parent::__construct($model);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function createCountry(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * @param $code
     * @return string
     */
    public function getCountryNameByCode($code, $defaultName)
    {
        if (! $code || ! $defaultName) {
            return $defaultName;
        }

        $code = strtoupper($code);

//        return $this->getCountriesList()[$code] ?? '';

        $countriesList = $this->getCountriesList();

        if (! isset($countriesList[$code])) {
            $this->createCountry([
                'code' => $code,
                'name' => $defaultName
            ]);

            $countriesList = $this->getCountriesList(true);
        }

        return $countriesList[$code];
    }

    /**
     * void
     */
    public function clearCountriesList() {
        \Cache::forget($this->cacheKey);
    }

    /**
     * @return array
     */
    public function getCountriesList($refresh = false, $headerText = false) {
        if ($refresh) {
            $this->clearCountriesList();
        }

        if(! \Cache::has($this->cacheKey)) {
            $countries = $this->orderBy('name', 'asc')->get()->pluck('name', 'code')->toArray();

            $expiresAt = Carbon::today()->addDays(7);

            \Cache::put($this->cacheKey, $countries, $expiresAt);
        }

        $list = \Cache::get($this->cacheKey);

        if ($headerText !== false) {
            $list = ['' => $headerText] + $list;
        }

        return $list;
    }
}