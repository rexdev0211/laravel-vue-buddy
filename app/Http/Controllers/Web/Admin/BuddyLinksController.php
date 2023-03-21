<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Web\Controller;
use Illuminate\Http\Request;

class BuddyLinksController extends Controller
{
    public function index()
    {
        $buddyLinks = '';
        if (\Storage::exists('buddyLinks')) {
            $buddyLinks = \Storage::get('buddyLinks');
        }

        return view('admin.buddyIds.index', ['buddyLinks' => $buddyLinks]);
    }

    public function update(Request $request) {
        $buddyLinks = $request->get('buddy_links', '');

        $errorMessage = "Failed to write into file: storage/app/buddy_links. Please check file permission and try again.";
        try {
            $write = \Storage::put('buddy_links', $buddyLinks);
        } catch (\Exception $e) {
            return back()->withErrors($errorMessage);
        }

        if ($write == false) {
            return back()->withErrors($errorMessage);
        }

        $message = 'Successful update at ' . date('H:i:s');

        return redirect()->route('admin.buddyLinks')->with('successMessage', $message);
    }
}
