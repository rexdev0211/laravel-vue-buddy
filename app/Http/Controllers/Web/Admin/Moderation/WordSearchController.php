<?php

namespace App\Http\Controllers\Web\Admin\Moderation;

use Helper;
use App\User;
use App\Event;
use Illuminate\Pagination\LengthAwarePaginator;

class WordSearchController extends \App\Http\Controllers\Web\Controller
{
    /**
     * Get Words Search page
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $sessionKey = 'wordSearch';

        $resetForm = request()->exists('resetFilters');
        $page      = (int) Helper::getUserPreference($sessionKey, 'page', 1, $resetForm);
        $perPage   = (int) Helper::getUserPreference($sessionKey, 'perPage', Helper::getDefaultPerPageNumber());
        $search    = Helper::getUserPreference($sessionKey, 'search', '', $resetForm);
        $type      = Helper::getUserPreference($sessionKey, 'type', 'user', $resetForm);

        $total   = 0;
        $matches = [];
        if ($search) {
            switch ($type) {
                case 'user':
                    $matches = User::whereNull('deleted_at')
                                   ->where('status', 'active')
                                   ->where(function($query) use ($search) {
                                       $query->where('name', 'LIKE', '%'.$search.'%')
                                             ->orWhere('about', 'LIKE', '%'.$search.'%');
                                   });

                    $total = $matches->count();

                    $matches = $matches->skip(($page - 1) * $perPage)
                                       ->limit($perPage)
                                       ->get()
                                       ->map(function($item) use ($search) {
                                           $foundIn      = stripos($item->name, $search) !== false ? 'name' : 'about';
                                           $researchLink = route('admin.users.view', $item->id);

                                           return (object) [
                                               'type'  => 'User',
                                               'id'    => $item->id,
                                               'where' => 'Found in: <a href="'. $researchLink .'" data-toggle="modal" data-target="#modal-box-div">'. $foundIn .'</a>',
                                               'user'  => '<a href="'. $researchLink .'" data-toggle="modal" data-target="#modal-box-div">'. $item->name .' ['. $item->id .']</a>',
                                           ];
                                       });
                    break;

                case 'event':
                    $matches = Event::with('user')
                                    ->whereHas('user', function($query) {
                                        $query->whereNull('deleted_at')
                                              ->where('status', 'active');
                                    })
                                    ->where(function($query) use ($search) {
                                        $query->where('title', 'LIKE', '%'.$search.'%')
                                              ->orWhere('description', 'LIKE', '%'.$search.'%');
                                    });

                    $total = $matches->count();

                    $matches = $matches->skip(($page - 1) * $perPage)
                                       ->limit($perPage)
                                       ->get()
                                       ->map(function($item) use ($search) {
                                           $foundIn      = stripos($item->title, $search) !== false ? 'title' : 'description';
                                           $researchLink = route('admin.events.view', $item->id);

                                           return (object) [
                                               'type'  => 'Event',
                                               'id'    => $item->id,
                                               'where' => 'Found in: <a href="'. $researchLink .'" data-toggle="modal" data-target="#modal-box-div">'. $foundIn .'</a>',
                                               'user'  => '<a href="'. route('admin.users.view', $item->user->id) .'" data-toggle="modal" data-target="#modal-box-div">'. $item->user->name .' ['. $item->user->id .']</a>',
                                           ];
                                       });
                    break;
            }
        }

        if ($total) {
            $matches = new LengthAwarePaginator($matches, $total, $perPage, $page, ['path' => route('admin.moderation.wordSearch')]);
        }

        return view('admin.moderation.wordSearch', [
            'sessionKey' => $sessionKey,
            'matches'    => $matches,
        ]);
    }

    /**
     * Search
     * @return \Illuminate\View\View
     */
    public function search()
    {
        $sessionKey = 'wordSearch';

        $page      = (int) Helper::getUserPreference($sessionKey, 'page', 1);
        $perPage   = (int) Helper::getUserPreference($sessionKey, 'perPage', Helper::getDefaultPerPageNumber());
        $search    = Helper::getUserPreference($sessionKey, 'search', '');
        $type      = Helper::getUserPreference($sessionKey, 'type', 'user');

        return redirect(route('admin.moderation.wordSearch'));
    }
}
