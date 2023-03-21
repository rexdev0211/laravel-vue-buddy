<?php

namespace App\Http\Controllers\Web\Admin;

use App\Models\Rush\Rush;
use App\Http\Controllers\Web\Controller;

class RushController extends Controller
{
    /**
     * Rush Strips list
     */
    public function index()
    {
        $rushes = Rush::where('status', '!=', 'deleted')
                      ->with('ranks')
                      ->with('author')
                      ->with('latest_strip')
                      ->withCount('favorites')
                      ->latest()
                      ->paginate(15);

        $rushes->getCollection()
               ->transform(function($item){
                   return $item->formatForAdmin();
               });

        return view('admin.rush.index', [
            'list' => $rushes,
        ]);
    }

    /**
     * Suspend User Rush
     */
    public function suspend($id)
    {
        $rush = Rush::where('id', $id)
                    ->first();

        if ($rush) {
            $rush->suspend();
        }

        return redirect()->back();
    }

    /**
     * Activate Suspended Rush
     */
    public function activate($id)
    {
        $rush = Rush::where('id', $id)
                    ->first();

        if ($rush) {
            $rush->activate();
        }

        return redirect()->back();
    }
}
