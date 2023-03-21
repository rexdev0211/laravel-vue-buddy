<?php

namespace App\Http\Controllers\Web\Admin;

use Helper;
use App\Country;
use App\Http\Controllers\Web\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Redirect;

class OnlineCountryController extends Controller
{
    /**
     * Countries list
     */
    public function index()
    {
        $sessionKey = 'admin.onlineCountries';

        $page        = (int) Helper::getUserPreference($sessionKey, 'page', 1);
        $perPage     = (int) Helper::getUserPreference($sessionKey, 'perPage', Helper::getDefaultPerPageNumber());
        $countries = Country::where('code', '<>', 'RU')->orderBy('id')->paginate($perPage, ['*'], 'page', $page);

        if (!Redis::get('wasRecentlyOnlineTimeSetForAllCountries')) {
            foreach (Country::all() as $country) {
                Redis::set('wasRecentlyOnlineTime.' . $country->code, $country->was_recently_online_time);
            }
            Redis::set('wasRecentlyOnlineTimeSetForAllCountries', true);
        }

        return view('admin.countries.index', compact('countries'));
    }

    /**
     * Edit Country wasRecentlyOnline Time
     */
    public function edit($id)
    {
        $country = Country::find($id);

        if (is_null($country)) {
            dd('country does not exist');
        }

        $days = floor($country->was_recently_online_time / (24 * 60 * 60));
        $hours = floor(($country->was_recently_online_time - $days * 24 * 60 * 60) / (60 * 60));
        $minutes = floor(($country->was_recently_online_time - $days * 24 * 60 * 60 - $hours * 60 * 60) / 60);

        return view('admin.countries.edit', compact('country', 'days', 'hours', 'minutes'));
    }

    /**
     * Update Country wasRecentlyOnline Time
     */
    public function update(Request $request)
    {
        $country = Country::find($request->id);

        if (is_null($country)) {
            return redirect()->back()->withErrors(['errors' => ['code' => 'country is required']])->withInput();
        }

        $days = intval($request->get('days'));
        $hours = intval($request->get('hours'));
        $minutes = intval($request->get('minutes'));

        if ($days + $hours + $minutes == 0) {
            return redirect()->back()->withErrors(['errors' => ['code' => 'wasRecentlyOnline time is required']])->withInput();
        }

        $country->was_recently_online_time = $days * 24 * 60 * 60 + $hours * 60 * 60 + $minutes * 60;
        $country->changed_date = Carbon::today();
        $country->save();

        Redis::set('wasRecentlyOnlineTime.' . $country->code, $country->was_recently_online_time);

        return Redirect::route('admin.onlineCountries');
    }
}
