<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Web\Controller;
use App\Models\Payment\SegpayPurchase;
use App\Models\Payment\TwokchargeTransactions;
use App\User;
use Helper;
use DB;

class ProController extends Controller
{
    public function users()
    {
        $sessionKey = 'admin.proUsers';

        $page = (int)Helper::getUserPreference($sessionKey, 'page', 1);
        $perPage = (int)Helper::getUserPreference($sessionKey, 'perPage', Helper::getDefaultPerPageNumber());

        $resetForm = request()->exists('resetFilters');
        $filterStatus = Helper::getUserPreference($sessionKey, 'filterStatus', '', $resetForm);
        $filterName = Helper::getUserPreference($sessionKey, 'filterName', '', $resetForm);
        $filterEmail = Helper::getUserPreference($sessionKey, 'filterEmail', '', $resetForm);
        $filterId = Helper::getUserPreference($sessionKey, 'filterId', '', $resetForm);
        $filterTransactionId = Helper::getUserPreference($sessionKey, 'filterTransactionId', '', $resetForm);

        $users = User::select(
            'users.id',
            'users.name',
            'users.email',
            'users.email_orig',
            'users.pro_expires_at'
        );

        $joinedTables = [
            'flexpay_subscriptions',
            'apple_subscriptions',
            'google_subscriptions',
            'segpay_purchases',
            'twokcharge_transactions',
        ];

        if (empty($filterStatus) || $filterStatus == 'pro') {
            $users = $users
                ->where('users.pro_expires_at', '>=', DB::raw('NOW()'))
                ->where('users.pro_type', 'paid');
        } else {
            foreach ($joinedTables as $joinedTable) {
                $users = $users->where(function($query) use ($joinedTable) {
                    $query
                        ->where(function($query) use ($joinedTable) {
                            $query
                                ->whereExists(function ($query) use ($joinedTable){
                                    $query
                                        ->select(DB::raw(1))
                                        ->from($joinedTable)
                                        ->whereRaw("$joinedTable.user_id = users.id")
                                        ->whereNotNull("$joinedTable.transaction_id");
                                })
                                ->whereNull('users.pro_expires_at');
                        })
                        ->orWhere(function($query) {
                            $query
                                ->whereNotNull('users.pro_expires_at')
                                ->where('users.pro_expires_at', '<', DB::raw('NOW()'));
                        });
                });
            }
        }

        if (!empty($filterName)) {
            $users = $users->where('users.name', 'LIKE', '%' . $filterName . '%');
        }

        if (!empty($filterId)) {
            $users = $users->where('users.id', $filterId);
        }

        if (!empty($filterEmail)) {
            $users = $users->where('users.email', 'LIKE', '%' . $filterEmail . '%');
        }

        if (!empty($filterTransactionId)) {
            foreach ($joinedTables as $joinedTable) {
                /*
                https://stackoverflow.com/questions/7745609/sql-select-only-rows-with-max-value-on-a-column

                Joining with simple group-identifier, max-value-in-group Sub-query
                In this approach, you first find the group-identifier, max-value-in-group (already solved above)
                in a sub-query.
                Then you join your table to the sub-query with equality on both group-identifier and max-value-in-group:

                SELECT a.id, a.rev, a.contents
                FROM YourTable a
                INNER JOIN (
                    SELECT id, MAX(rev) rev
                    FROM YourTable
                    GROUP BY id
                ) b ON a.id = b.id AND a.rev = b.rev
                */
                $latestSubscriptionSubquery = DB::table($joinedTable)
                    ->select('user_id', DB::raw("MAX(id) as max_{$joinedTable}_id"))
                    ->groupBy('user_id');

                $latestSubscription = DB::table($joinedTable)
                    ->select("$joinedTable.user_id", "$joinedTable.transaction_id")
                    // Inner join a table with itself with result of MAX(id) + GROUP BY
                    ->joinSub($latestSubscriptionSubquery, "{$joinedTable}_latest", function ($join) use ($joinedTable) {
                        $join->on("$joinedTable.id", '=', "{$joinedTable}_latest.max_{$joinedTable}_id");
                    });

                $users = $users
                    ->leftJoinSub($latestSubscription, $joinedTable, function ($join) use ($joinedTable, $filterTransactionId) {
                        $join
                            ->on('users.id', '=', "$joinedTable.user_id")
                            ->where("$joinedTable.transaction_id", 'like', '%' . $filterTransactionId . '%');
                    });
            }
            $users = $users->where(function($query) {
                $query
                    ->whereNotNull('apple_subscriptions.transaction_id')
                    ->orWhereNotNull('flexpay_subscriptions.transaction_id')
                    ->orWhereNotNull('google_subscriptions.transaction_id')
                    ->orWhereNotNull('segpay_purchases.transaction_id')
                    ->orWhereNotNull('twokcharge_transactions.transaction_id');
            });
        } else {
            $users = $users->with(
                'lastFlexpayTransaction',
                'lastAppleTransaction',
                'lastGoogleTransaction',
                'lastSegpayTransaction',
                'lastTwokTransaction'
            );
        }

        $users = $users
            ->orderBy('users.id', 'ASC')
            ->paginate($perPage, ['*'], 'page', $page);

        return view('admin.pro.users', [
            'users' => $users,
            'sessionKey' => $sessionKey,
        ]);
    }

    public function userTransactions($userId)
    {
        $user = User::select('id', 'name', 'email', 'email_orig', 'pro_expires_at')
            ->where('id', $userId)
            ->with('lastSegpayTransaction')
            ->with('lastTwokTransaction')
            ->first();


        return view('admin.pro.transactions', [
            'user' => $user,
        ]);
    }

    public function segpayTransactionLogs($userId, $transactionId)
    {
        $transaction = SegpayPurchase::where('id', $transactionId)
            ->where('user_id', $userId)
            ->with('logs')
            ->first();

        $user = User::select('id', 'name')
            ->where('id', $userId)
            ->first();

        // dd($transaction);
        return view('admin.pro.transaction_logs', [
            'user' => $user,
            'userId' => $userId,
            'type' => 'segpay',
            'transaction' => $transaction,
        ]);
    }

    public function twokChargeTransactionLogs($userId, $transactionId)
    {
        $transaction = TwokchargeTransactions::where('id', $transactionId)
            ->where('user_id', $userId)
            ->with('logs')
            ->first();

        $user = User::select('id', 'name')
            ->where('id', $userId)
            ->first();

        // dd($transaction);
        return view('admin.pro.transaction_logs', [
            'user' => $user,
            'userId' => $userId,
            'type' => '2000charge',
            'transaction' => $transaction,
        ]);
    }
}
