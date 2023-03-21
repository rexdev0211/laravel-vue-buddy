<?php

namespace App\Http\Controllers\Web\Admin;

use App\PromoCode;
use App\Http\Controllers\Web\Controller;

class PromoCodesController extends Controller
{

    /**
     * PROmo Codes list
     */
    public function index()
    {
        return view('admin.promo.list', [
            'list' => PromoCode::orderBy('created_at', 'DESC')
                               ->paginate(15),
        ]);
    }

    /**
     * PROmo Codes list
     */
    public function create()
    {
        return view('admin.promo.manage');
    }

    /**
     * PROmo Codes list
     */
    public function edit($id)
    {
        $promo = PromoCode::find($id);

        if (!$promo) return abort(404);

        return view('admin.promo.manage', [
            'item' => $promo,
        ]);
    }

    /**
     * PROmo Codes list
     */
    public function save()
    {
        $this->validate(request(), [
            'code'            => 'required|string',
            'expiration_time' => 'required|date_format:d.m.Y|after:now',
        ]);

        $promo = [
            'id'              => intval(request()->get('id')),
            'code'            => strtolower(trim(request()->get('code'))),
            'title'           => trim(request()->get('code')),
            'expiration_time' => date('Y-m-d 23:59:59', strtotime(request()->get('expiration_time'))),
            'months'          => intval(request()->get('months')),
            'weeks'           => intval(request()->get('weeks')),
            'days'            => intval(request()->get('days')),
            'limit'           => intval(request()->get('limit')) >= 0 ? intval(request()->get('limit')) : 0,
        ];

        if ($promo['months'] + $promo['weeks'] + $promo['days'] == 0) {
            return redirect()->back()->withErrors(['errors' => ['code' => 'PROmo Time is required']])->withInput();
        }

        $uniqueCheck = PromoCode::whereCode($promo['code'])
                                ->where('id', '!=', $promo['id'])
                                ->first();
        if (!$uniqueCheck) {
            if ($promo['id'] > 0) {
                $promoCode = PromoCode::whereId($promo['id'])->first();
                $promoCode->code            = $promo['code'];
                $promoCode->title           = $promo['title'];
                $promoCode->expiration_time = $promo['expiration_time'];
                $promoCode->months          = $promo['months'];
                $promoCode->weeks           = $promo['weeks'];
                $promoCode->days            = $promo['days'];
                $promoCode->limit           = $promo['limit'];

                $promoCode->save();
            } else {
                $promoCode = PromoCode::create([
                    'code'            => $promo['code'],
                    'title'           => $promo['title'],
                    'expiration_time' => $promo['expiration_time'],
                    'months'          => $promo['months'],
                    'weeks'           => $promo['weeks'],
                    'days'            => $promo['days'],
                    'limit'           => $promo['limit'],
                    'status'          => 1,
                    'used_count'      => 0,
                ]);
            }
        } else {
            return redirect()->back()->withErrors(['errors' => ['code' => 'This PROmo Code has already been taken']])->withInput();
        }

        return redirect(route('admin.promo'));
    }

    /**
     * Invalidate PROmo Code
     */
    public function invalidate($id)
    {
        $promo = PromoCode::find($id);

        if (!$promo) return abort(404);

        $promo->status = 0;
        $promo->save();

        return redirect()->back();
    }
}
