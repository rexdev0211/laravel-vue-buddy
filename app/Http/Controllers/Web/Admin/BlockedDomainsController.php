<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Web\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlockedDomainsController extends Controller
{
    var $keywordsFileName   = 'blockedKeywords';
    var $shortenersFileName = 'blockedShorteners';
    var $ipsFileName        = 'blockedIPs';

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $domains = $shorteners = $ips = '';

        if (\Storage::exists($this->keywordsFileName)) {
            $domains = \Storage::get($this->keywordsFileName);
        }

        if (\Storage::exists($this->shortenersFileName)) {
            $shorteners = \Storage::get($this->shortenersFileName);
        }

        if (\Storage::exists($this->ipsFileName)) {
            $ips = \Storage::get($this->ipsFileName);
        }

        return view('admin.blockedDomains.index', compact('domains', 'shorteners', 'ips'));
    }

    /**
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function updateDomains(Request $request) {
        $domains = strtolower($request->get('domains', ''));

//        $domains = explode("\n", $domains);
//
//        foreach ($domains as $key => $domain) {
//            $domains[$key] = preg_replace('/[[:^print:]]/', "", $domain);
//        }
//
//        $domains = implode("\n", $domains);

        $errorMessage = "Failed to write into file: storage/app/blockedKeywords. Please check file permission and try again.";

        try {
            $write = \Storage::put($this->keywordsFileName, $domains);
        } catch (\Exception $e) {
            return back()->withErrors($errorMessage);
        }

        if ($write == false) {
            return back()->withErrors($errorMessage);
        }

        $message = 'Successful update at ' . date('H:i:s');

        return redirect()->route('admin.blockedDomains')->with('successMessage', $message);
    }

    /**
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function updateShorteners(Request $request) {
        $shorteners = strtolower($request->get('shorteners', ''));

//        $shorteners = explode("\n", $shorteners);
//
//        foreach ($shorteners as $key => $shortener) {
//            $shorteners[$key] = preg_replace('/[[:^print:]]/', "", $shortener);
//        }
//
//        $shorteners = implode("\n", $shorteners);

        $errorMessage = "Failed to write into file: storage/app/blockedShorteners. Please check file permission and try again.";

        try {
            $write = \Storage::put($this->shortenersFileName, $shorteners);
        } catch (\Exception $e) {
            return back()->withErrors($errorMessage);
        }

        if ($write == false) {
            return back()->withErrors($errorMessage);
        }

        $message = 'Successful update at ' . date('H:i:s');

        return redirect()->route('admin.blockedDomains')->with('successMessage', $message);
    }

    /**
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function updateIPs(Request $request) {
        $ips = strtolower($request->get('ips', ''));
        $errorMessage = "Failed to write into file: storage/app/blockedIPs. Please check file permission and try again.";

        try {
            $write = \Storage::put($this->ipsFileName, $ips);
        } catch (\Exception $e) {
            return back()->withErrors($errorMessage);
        }

        if ($write == false) {
            return back()->withErrors($errorMessage);
        }

        $message = 'Successful update at ' . date('H:i:s');

        return redirect()->route('admin.blockedDomains')->with('successMessage', $message);
    }

    /**
     * @return JsonResponse
     */
    public function addIP(): JsonResponse
    {
        $ip = strtolower(request()->get('ip', null));
        if (empty($ip)) {
            return response()->json([
                'error' => 'IP is required'
            ]);
        }

        try {
            $ips = \Storage::get($this->ipsFileName);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to write into file: storage/app/blockedIPs. Please check file permission and try again'
            ]);
        }
        $ipsArray = preg_split('/[\n\s\r]+/', $ips);
        $ipsArray[] = $ip;
        $ipsArray = array_unique($ipsArray);
        $ips = implode("\n", $ipsArray);

        $write = \Storage::put($this->ipsFileName, $ips);
        if ($write == false) {
            return response()->json([
                'error' => 'Failed to write into file: storage/app/blockedIPs. Please check file permission and try again'
            ]);
        }

        return response()->json([
            'message' => 'Successful update at ' . date('H:i:s')
        ]);
    }
}
