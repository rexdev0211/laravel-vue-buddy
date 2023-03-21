<?php

namespace App;

use App\Models\Payment\SegpayPurchase;
use App\Models\Payment\TwokchargeTransactions;
use App\Repositories\EventRepository;
use App\Services\BuddyLinkService;
use App\Services\ChatService;
use App\Services\SpamService;
use App\Traits\HybridRelations;
use App\Traits\SpatialTrait;
use Carbon\Carbon;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Events\UserSuspended;
use App\Events\RefreshDataRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Passport\HasApiTokens;
use League\Flysystem\Exception;
use App\Enum\ProTypes;
use DB;
use Illuminate\Support\Facades\Redis;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens, HybridRelations, SpatialTrait;

    protected $connection = 'mysql';

    const
        GROUP_MEMBER = 'member',
        GROUP_STAFF = 'staff',

        ATTRIBUTES_MODE_FULL = 'full',
        ATTRIBUTES_MODE_GENERAL = 'general',
        ATTRIBUTES_MODE_GROUP_MESSAGE = 'group_message',
        ATTRIBUTES_MODE_STATUS = 'status',
        ATTRIBUTES_MODE_CONVERSATION = 'conversation',
        ATTRIBUTES_MODE_DISCOVER = 'discover',

        STATUS_ACTIVE = 'active',
        STATUS_SUSPENDED = 'suspended',
        STATUS_DEACTIVATED = 'deactivated',
        STATUS_GHOSTED = 'ghosted',
        COMPUTED_STATUS_DELETED = 'deleted',

        BLOCKED_NAME = 'blocked',

        GENERAL_ATTRIBUTES_FIELDS = [
            'id',
            'name',
            'photo',
            'discreet_mode',
            'invisible',
            'pro_expires_at',
            'user_group',
            'last_active',
            'status',
            'deleted_at',
        ],

        MIN_ALLOWED_LATITUDE_Y=-89.999,
        MAX_ALLOWED_LATITUDE_Y=89.999,

        MIN_ALLOWED_LONGITUDE_X=-179.999,
        MAX_ALLOWED_LONGITUDE_X=179.999;

    protected $guarded = ['id', '_token'];

    protected $dates = ['last_active', 'dob', 'last_login', 'deleted_at', 'pro_expires_at', 'purchase_blocked'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'gps_geom', 'location',
    ];
    
    /**
     * Force location attribute to have UTF-8 charset
     */
    public function getLocaitonAttribute() {
        return mb_convert_encoding($this->location, 'utf-8');
    }

    protected $spatialFields = [
        'location',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function newsletter() {
        return $this->hasOne('App\Newsletter');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function visited() {
        return $this->belongsToMany('App\User', 'user_visits_map', 'visitor_id', 'visited_id')->withPivot('idate');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function visitors() {
        return $this->belongsToMany('App\User', 'user_visits_map', 'visited_id', 'visitor_id')->withPivot('idate');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function events() {
        return $this->hasMany('App\Event', 'user_id', 'id')->where('type', '<>', Event::TYPE_CLUB)->where('status', '!=', Event::STATUS_SUSPENDED);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function clubs() {
        return $this->hasMany('App\Event', 'user_id', 'id')->where('type', Event::TYPE_CLUB)->where('status', '!=', Event::STATUS_SUSPENDED);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function eventMemberships() {
        return $this->hasMany('App\EventMembership', 'user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function activeEventMemberships() {
        return $this->eventMemberships()
            ->whereIn('status', [
                EventMembership::STATUS_HOST,
                EventMembership::STATUS_MEMBER,
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function favorites() {
        return $this->hasMany('App\UserFavorite', 'user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function blocked() {
        return $this->hasMany('App\UserBlocked', 'user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function lastSegpayTransaction() {
        return $this->hasOne('App\Models\Payment\SegpayPurchase', 'user_id', 'id')->latest();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function allSegpayTransactions() {
        return $this->hasMany('App\Models\Payment\SegpayPurchase', 'user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function lastTwokTransaction() {
        return $this->hasOne('App\Models\Payment\TwokchargeTransactions', 'user_id', 'id')->latest();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function allTwokTransactions() {
        return $this->hasMany('App\Models\Payment\TwokchargeTransactions', 'user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function lastAppleTransaction() {
        return $this->hasOne('App\Models\Payment\AppleSubscription', 'user_id', 'id')->latest();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function allAppleTransactions() {
        return $this->hasMany('App\Models\Payment\AppleSubscription', 'user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function lastGoogleTransaction() {
        return $this->hasOne('App\Models\Payment\GoogleSubscription', 'user_id', 'id')->latest();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function allGoogleTransactions() {
        return $this->hasMany('App\Models\Payment\GoogleSubscription', 'user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function lastFlexpayTransaction() {
        return $this->hasOne('App\Models\Payment\FlexpaySubscription', 'user_id', 'id')->whereNotNull('transaction_id')->latest();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function allFlexpayTransactions() {
        return $this->hasMany('App\Models\Payment\FlexpaySubscription', 'user_id', 'id')->whereNotNull('transaction_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function countryRow() {
        return $this->belongsTo('App\Country', 'country_code', 'code')->withDefault();
    }

    public function assignBuddyLink()
    {
        $forceRandom = false;
        do {
            $this->link = BuddyLinkService::getComputedBuddyLink($this->name, $forceRandom, true);
            try {
                $this->save();
            } catch (\Exception $e) {
                $forceRandom = true;
            }
        } while ($this->isDirty());
    }

    /**
     * @return mixed
     */
    public function lastSubscription() {
        $lastTransaction = collect([
            $this->lastFlexpayTransaction,
            $this->lastAppleTransaction,
            $this->lastGoogleTransaction,
            $this->lastSegpayTransaction,
            $this->lastTwokTransaction,
        ])
        ->filter()
        ->sortByDesc('updated_at')
        ->first();

        return $lastTransaction;
    }

    /**
     * Get User PRO Service
     */
    public function getIssuer() {
        $lastTransaction = $this->lastSubscription();
        if (!empty($lastTransaction)) {
            return $lastTransaction->issuerCodename;
        } else {
            return 'Unknown issuer';
        }
    }

    /**
     * Get User PRO Package name
     */
    public function getProPlan() {
        $subscription = $this->lastSubscription();
        return $subscription ? $subscription->getPackageName() : null;
    }

    /**
     * Get User PRO Transaction ID
     */
    public function getProTransactionId() {
        $transaction = $this->lastSubscription();
        return $transaction ? $transaction->transaction_id : null;
    }

    /**
     * Get User PRO Transaction Email
     */
    public function getProTransactionEmail() {
        $subscription = $this->lastSubscription();
        if ($subscription instanceof SegpayPurchase) {
            return $subscription->email;
        } elseif ($subscription instanceof TwokchargeTransactions) {
            return $subscription->email;
        }
        return null;
    }

    /**
     * Get User PRO Transaction Created Date
     */
    public function getProRebillDate() {
        $subscription = $this->lastSubscription();
        return $subscription ? $subscription->created_at : null;
    }

    /**
     * @param $username
     * @return mixed
     */
    public function findForPassport($username) {
        $user = $this
            ->where(function($q) use ($username) {
                $q->where('email', $username)
                  ->orWhere('name', $username)
                  ->orWhere('link', $username);
            })
            ->whereIn('status', ['active', 'ghosted', 'deactivated'])
            ->whereNull('deleted_at')
            ->first();

        return $user;
    }

    /**
     * @return string
     */
    public function getComputedStatusAttribute(): string
    {
        /*
        - ACTIVE
        - DELETED (trash can + "deleted")
        - GHOSTED (stop sign + "ghosted")
        - SUSPENDED (stop sign + "suspended")
        */
        $status = $this->status;
        if (!empty($this->deleted_at)) {
            $status = self::COMPUTED_STATUS_DELETED;
        } elseif ($this->status == self::STATUS_DEACTIVATED) {
            // A deactivated account's status is still "Active"
            // (which means, not deleted, not ghosted, not suspended).
            $status = self::STATUS_ACTIVE;
        }
        return Str::studly($status);
    }

    /**
     * @return null|string
     */
    public function getActivityStatusAttribute(): ?string
    {
        /*
        - "(dormant)" if the account has not been active for 12 months
        - "(deactivated)"
        */
        /** @var Carbon $lastActive */
        $lastActive = $this->last_active;
        $activityStatus = null;
        if ($this->status == self::STATUS_DEACTIVATED) {
            $activityStatus = '(deactivated)';
        } elseif (
            !empty($lastActive)
            &&
            $lastActive->diffInMonths() >= 12
        ) {
            $activityStatus = '(dormant)';
        }
        return $activityStatus;
    }

    /**
     * @return bool
     */
    public function getIsPurchaseBlockedAttribute()
    {
        return $this->isPurchaseBlocked();
    }

    public function isPurchaseBlocked()
    {
        return !$this->purchase_blocked || $this->purchase_blocked->isPast() ? false : true;
    }

    /**
     * @return mixed
     */
    public function blockPurchase($date = null)
    {
        $this->purchase_blocked = $date ?? Carbon::now()->addHours(1)->format('Y-m-d H:i:s');
        $this->save();

        return $this;
    }

    /**
     * @return mixed
     */
    public function unblockPurchase()
    {
        $this->purchase_blocked = null;
        $this->save();

        return $this;
    }

    /**
     * @return bool
     */
    public function isDeleted() {
        return !is_null($this->deleted_at);
    }

    /**
     * @return bool
     */
    public function pushNotificationsEnabled() {
        return $this->push_notifications == 'yes';
    }

    /**
     * @return bool
     */
    public function isNewUser() {
        return Carbon::now()->subDays(3)->lt($this->created_at);
    }

    /**
     * @return bool
     */
    public function isStaff() {
        return $this->user_group == self::GROUP_STAFF;
    }

    /**
     * @return bool
     */
    public function isPro() {
        return $this->isStaff() || ($this->pro_expires_at && !$this->pro_expires_at->isPast());
    }

    /**
     * Upgrade user's subscription
     *
     * @param string $date
     * @param string $type
     * @return User
     */
    public function upgradeToPro(string $date, string $type): User
    {
        $this->pro_expires_at = $date;
        $this->pro_type = $type;
        $this->purchase_blocked = null;
        $this->save();

        event(new RefreshDataRequest($this->id, 'upgradeToPro'));

        return $this;
    }

    /**
     * @param $password
     * @return $this
     */
    public function updatePassword($password): User
    {
        $this->update([
            'password' => $password
        ]);

        event(new RefreshDataRequest($this->id, 'updatePassword'));

        return $this;
    }

    /**
     * @return User
     */
    public function downgrade(): User
    {
        $this->pro_expires_at = null;
        $this->pro_type = ProTypes::NONE;
        $this->purchase_blocked = null;
        $this->save();

        event(new RefreshDataRequest($this->id, 'downgrade'));

        return $this;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags()
    {
        return $this->belongsToMany('App\Tag', 'user_tags_map');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function onesignalPlayers() {
        return $this->hasMany('App\OnesignalPlayer');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     *  This function is used to check if related user is added to favorites by current logged in user
     *  (kind of reversed relationship)
     *  It is used like in this example:
     *       ->with(['userFrom.isFavoriteRelation' => function($query) use ($owner) {
     *           $query->where('user_id', '=', $owner->id);
     *       }])
     */
    public function isFavoriteRelation() {
        return $this->hasMany('App\UserFavorite', 'user_favorite_id', 'id');
    }

    /**
     * @return bool
     */
    public function softDeleteUser() {
        return $this->update([
            'email_orig' => $this->email,
            'email' => null,
            'deleted_at' => Carbon::now()
        ]);
    }

    /**
     * Mark user as undeleted
     */
    public function softUndeleteUser() {
        $model = self::where('email', $this->email_orig)->first();

        if (!is_null($model)) {
            throw new Exception('Email already exists. User joined again?');
        }

        $this->update([
            'email' => $this->email_orig,
            'email_orig' => '',
            'deleted_at' => null
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function photos() {
        return $this->hasMany('App\UserPhoto');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function getDefaultUserPhotoAttribute()
    {
        return $this->photos()->where('is_default', '=', 'yes')->first();
    }

    /**
     * @return mixed
     */
    public function publicPhotos() {
        return $this->photos()
            ->where('visible_to', 'public')
            ->where('is_default', '!=', 'yes')
            ->orderBy('id', 'desc');
    }

    /**
     * @return mixed
     */
    public function publicVideos() {
        return $this->hasMany('App\UserVideo')
            ->where('visible_to', 'public')
            ->orderBy('id', 'desc');
    }

    /**
     * Return an invisible status
     * 
     * @return bool
     */
    public function isInvisible(): bool
    {
        return in_array($this->invisible, ['yes', 1]) ? true : false;
    }

    /**
     * Return an online status for a user
     * 
     * @return bool
     */
    public function isOnline(): bool
    {
        $isOnline = $this->activeWithinPeriod($this->last_active);
        if ($this->isPro()) {
            if (!$this->discreet_mode) {
                return $isOnline;
            } else {
                return false;
            }
        } else {
            return $isOnline;
        }
    }

    /**
     * Return a a recently online status for a user
     * 
     * @return bool
     */
    public function wasRecentlyOnline(): bool
    {
        $wasRecentlyOnline = $this->userWasRecentlyOnline($this->last_active);
        if ($this->isPro()) {
            if (!$this->discreet_mode) {
                return $wasRecentlyOnline;
            } else {
                return false;
            }
        } else {
            return $wasRecentlyOnline;
        }
    }

    /**
     * @return mixed
     */
    public function isFavorite(){
        return UserFavorite::isFavorite(\Auth::id(), $this->id);
    }

    /**
     * @return mixed
     */
    public function isFavoriteFor(User $user){
        return UserFavorite::isFavorite($user->id, $this->id);
    }

    /**
     * @return mixed
     */
    public function isBlockedBy(User $user) {
        return UserBlocked::isBlocked($user->id, $this->id);
    }

    /**
     * @param $lastActive
     *
     * @return mixed
     */
    public function activeWithinPeriod($lastActive, $period = 0)
    {
        if (!$lastActive) {
            return false;
        }

        if(!($lastActive instanceof Carbon)) {
            $lastActive = Carbon::createFromFormat('Y-m-d H:i:s', $lastActive);
        }

        if (!$period) {
            $period = config('const.USER_IS_ONLINE_MINUTES') * 60;
        }

        return Carbon::now()->subSeconds((int) $period + 30)->lt($lastActive);
    }

    /**
     * @param User $initiator
     */
    public function block(User $initiator): void
    {
        $initiatorId = $initiator->id;
        $suppressedId = $this->id;

        // Block user
        UserBlocked::firstOrCreate([
            'user_id' => $initiatorId,
            'user_blocked_id' => $suppressedId
        ]);

        Message::where(function ($query) use ($initiatorId, $suppressedId) {
           $query->where('user_from', $initiatorId)
                 ->where('user_to', $suppressedId)
                 ->update(['is_blocked_by_sender' => 1]);
        })->orWhere(function ($query) use ($initiatorId, $suppressedId) {
           $query->where('user_to', $initiatorId)
                 ->where('user_from', $suppressedId)
                 ->update(['is_blocked_by_recipient' => 1]);
        });

        event(new RefreshDataRequest($initiatorId, 'block'));
        event(new RefreshDataRequest($suppressedId, 'block'));
    }

    public function enableNewsletterSubscription()
    {
        $this->changeNewsletterSubscription('yes');
    }

    public function disableNewsletterSubscription()
    {
        $this->changeNewsletterSubscription('no');
    }

    /**
     * @param string $flag
     */
    public function changeNewsletterSubscription($flag='yes')
    {
        $user = $this;

        /** @var Newsletter $newsletters */
        $newsletters = Newsletter::where(function($q) use($user) {
            $q->where('user_id', $user->id)
                ->orWhere('email', $user->email);
        })->get();

        /** @var Newsletter $newsletter */
        foreach ($newsletters as $newsletter) {
            $newsletter->subscribed = $flag;
            $newsletter->save();
        }
    }

    /**
     * @param string|null $reasonCode
     *
     * @return void
     */
    public function suspend(string $reasonCode = null) {
        $this->status = self::STATUS_SUSPENDED;
        $this->save();

        (new EventRepository)
            ->where('user_id', $this->id)
            ->updateAll(['status' => 'suspended']);

        Message::where('user_from', $this->id)->update([
            'is_sender_suspended' => 1
        ]);

        if (!empty($reasonCode)) {
            $spamService = new SpamService;
            $spamService->setUser($this);
            $spamService->sendAdminEmail(SpamService::ACTION_SUSPEND, $reasonCode);
        }

        $this->disableNewsletterSubscription();

        /** @var User $currentUser */
        $currentUser = auth()->user();

        if (!empty($currentUser)) {
            Redis::del('conversations:' . $currentUser->id);
        }

        Redis::del('conversations:' . $this->id);

        event(new UserSuspended($this->id));
    }

    public function unsuspend() {
        $this->status = self::STATUS_ACTIVE;
        $this->save();

        (new EventRepository)
            ->where('user_id', $this->id)
            ->updateAll(['status' => "active"]);

        Message::where(function ($query) {
            $query->where('user_from', $this->id)
                ->update([
                    'is_sender_suspended' => 0,
                    'is_sender_ghosted' => 0
                ]);
        })->orWhere(function ($query) {
            $query->where('user_to', $this->id)
                ->update([
                    'is_sender_suspended' => 0,
                    'is_recipient_ghosted' => 0
                ]);
        });

        if ($this->subscribed == 'yes') {
            $this->enableNewsletterSubscription();
        }
    }

    /**
     * @param $lastActive
     * @return mixed
     */
    public function userWasRecentlyOnline($lastActive) {
        $period = config('const.USER_WAS_RECENTLY_MINUTES') * 60;
        
        if ($this->country_code) {
            $period = intval(Redis::get('wasRecentlyOnlineTime.' . $this->country_code) ?? 0);
            
            if ($period == 0) {
                $country = $this->countryRow;
                if (!empty($country) && !is_null($country->was_recently_online_time)) {
                    $period = intval($country->was_recently_online_time);
                    Redis::set('wasRecentlyOnlineTime.' . $this->country_code, $period);
                }
            }
        }
        
        return $this->activeWithinPeriod($lastActive, $period);
    }

    /**
     * @return array
     */
    public function getBlockedUsersIds() {
        $blocked = \App\UserBlocked::where('user_id', '=', $this->id)
                                   ->orWhere('user_blocked_id', '=', $this->id)
                                   ->get();

        $blockedMe    = $blocked->whereNotIn('user_id', [$this->id])->pluck('user_id');
        $blockedUsers = $blocked->whereNotIn('user_blocked_id', [$this->id])->pluck('user_blocked_id');

        return array_merge($blockedUsers->toArray(), $blockedMe->toArray());
    }

    /**
     * @param string $token
     */
    public function sendPasswordResetNotification($token)
    {
        $emailService = app('App\Services\EmailService');
        $resetUrl = url('reset-password/'.$token);
        $lang = request()->get('lang', 'en');
        $emailService->sendForgotPasswordEmail($this, $resetUrl, $lang);
    }

    public function incrementHisProfileViewed() {
        $this->increment('his_profile_viewed');
    }

    public function incrementViewedOtherProfiles() {
        $this->increment('viewed_other_profiles');
    }

    /**
     * @return bool
     */
    public function isSuspended() {
        return $this->status == self::STATUS_SUSPENDED;
    }

    /**
     * @return bool
     */
    public function isGhosted() {
        return $this->status == self::STATUS_GHOSTED;
    }

    /**
     * @param string|null $reasonCode
     *
     * @return void
     */
    public function ghost(string $reasonCode = null) {
        $this->status = self::STATUS_GHOSTED;
        $this->save();

        Message::where(function ($query) {
            $query->where('user_from', $this->id)
                ->update([
                    'is_sender_ghosted' => 1
                ]);
        })->orWhere(function ($query) {
            $query->where('user_to', $this->id)
                ->update([
                    'is_recipient_ghosted' => 1,
                ]);
        });

        if (!empty($reasonCode)) {
            $spamService = new SpamService;
            $spamService->setUser($this);
            $spamService->sendAdminEmail(SpamService::ACTION_GHOST, $reasonCode);
        }

        $this->disableNewsletterSubscription();
    }

    /**
     * Add to trusted message senders list
     *
     * @return void
     */
    public function addToTrustedSendersList(): void
    {
        Redis::setex("trusted-senders:{$this->id}", 30 * 3600, true);
    }

    /**
     * Remove from trusted message senders list
     *
     * @return void
     */
    public function removeFromTrustedSendersList(): void
    {
        Redis::del("trusted-senders:{$this->id}");
    }

    /**
     * Is trusted message sender even if email was bounced
     *
     * @return bool
     */
    public function isTrustedMessageSender(): bool
    {
        $trusted = Redis::get("trusted-senders:{$this->id}");
        return !empty($trusted);
    }

    /**
     * @return string
     */
    public function getGroupAttribute() {
        if ($this->isStaff()) return 'Staff';
        elseif ($this->isPro()) {
            if ($this->pro_type == ProTypes::PAID)   return 'Pro (p)';
            if ($this->pro_type == ProTypes::MANUAL) return 'Pro (m)';
            if ($this->pro_type == ProTypes::COUPON) return 'Pro (c)';
        }

        return 'Free';
    }

    /**
     * @return string
     */
    public function getGroupFullAttribute() {
        if ($this->isStaff()) return 'Staff';
        elseif ($this->isPro()) {
            if ($this->pro_type == ProTypes::PAID)   return 'Pro (paid)';
            if ($this->pro_type == ProTypes::MANUAL) return 'Pro (manual)';
            if ($this->pro_type == ProTypes::COUPON) return 'Pro (coupon)';
        }

        return 'Free';
    }

    /**
     * @return string
     */
    public function proExpiresAt() {
        return $this->pro_expires_at->format('d.m.Y');
    }

    /**
     * @return bool
     */
    public function isMediaDeleted() {
        return $this->isSuspended() || $this->isDeleted() || $this->isDeactivated();
    }

    /**
     * @return bool
     */
    public function isDeactivated() {
        return $this->status == self::STATUS_DEACTIVATED;
    }

    /**
     * @return bool
     */
    public function isActive() {
        return !$this->isSuspended();
    }

    public function returnYesNo($value): string
    {
        return $value ? 'yes' : 'no';
    }

    public function getAppViewSensitiveEventsAttribute($value): string
    {
        return $this->returnYesNo($value);
    }

    public function getAppViewSensitiveMediaAttribute($value): string
    {
        return $this->returnYesNo($value);
    }

    public function getInvisibleAttribute($value): string
    {
        return $this->returnYesNo($value);
    }

    public function getViewSensitiveEventsAttribute($value): string
    {
        return $this->returnYesNo($value);
    }

    public function getViewSensitiveMediaAttribute($value): string
    {
        return $this->returnYesNo($value);
    }

    public function getWebViewSensitiveContentAttribute($value): string
    {
        return $this->returnYesNo($value);
    }

    /**
     * @return array
     */
    public function getAllAttributes() {
        $mergedPhoto = $this->getMergedPhoto();
        if (!empty($mergedPhoto)) {
            $mergedPhoto->setUrls(false);
        }

        $defaultPhoto = $this->getDefaultPhoto();
        if (!empty($defaultPhoto)) {
            $defaultPhoto->setUrls(true);
            $defaultPhotoPending = $defaultPhoto->isAdult() && $defaultPhoto->status === 'queued';
            $defaultPhotoRejected = ($defaultPhoto->isAdult() || $defaultPhoto->isProhibited()) && $defaultPhoto->status === 'reviewed';
            $defaultPhoto = $defaultPhoto->toArray() + ['rejected' => $defaultPhotoRejected, 'pending' => $defaultPhotoPending];
        }

        $adultAvatar = $this->getAdultPhoto();
        if (!empty($adultAvatar)) {
            $adultAvatar->setUrls(true);
            $adultAvatarRejected = $adultAvatar->isProhibited();
            $adultAvatar = $adultAvatar->toArray() + ['rejected' => $adultAvatarRejected];
        }

        $returnUser = [
            'id'                        => $this->id,
            'link'                      => $this->link,
            'about'                     => $this->about,
            'user_group'                => $this->user_group,
            'address'                   => $this->address,
            'body'                      => $this->body,
            'dob'                       => $this->dob->format('Y-m-d'),
            'language'                  => $this->language,
            'drugs'                     => $this->drugs,
            'email'                     => $this->email,
            'headline'                  => $this->headline,
            'height'                    => $this->height,
            'hiv'                       => $this->hiv,
            'lat'                       => (string) $this->lat,
            'lng'                       => (string) $this->lng,
            'name'                      => $this->name,
            'penis'                     => $this->penis,
            'position'                  => $this->position,
            'tags'                      => $this->tags->toArray(),
            'weight'                    => $this->weight,
            'last_active'               => !empty($this->last_active) ? $this->last_active->toDateTimeString() : null,
            'his_profile_viewed'        => $this->his_profile_viewed,
            'viewed_other_profiles'     => $this->viewed_other_profiles,
            'show_age'                  => $this->show_age,
            'unit_system'               => $this->unit_system,
            'email_reminders'           => $this->email_reminders,
            'location_type'             => $this->location_type,
            'locality'                  => $this->locality,
            'state'                     => $this->state,
            'country'                   => $this->country,
            'country_code'              => $this->country_code,
            'has_notifications'         => $this->has_notifications,
            'has_new_notifications'     => $this->has_new_notifications,
            'has_new_visitors'          => $this->has_new_visitors,
            'has_new_messages'          => $this->has_new_messages,
            'subscribed'                => $this->subscribed,
            'notification_sound'        => $this->notification_sound,
            'push_notifications'        => $this->push_notifications,
            'web_view_sensitive_content'=> $this->web_view_sensitive_content,
            // Do not reveal ghosted status in public for anti-spam reasons
            'status'                    => $this->status == self::STATUS_GHOSTED ? self::STATUS_ACTIVE : $this->status,
            'deleted_at'                => $this->deleted_at,
            'discreet_mode'             => $this->discreet_mode && $this->isPro() ? true : false,
            'isPro'                     => $this->isPro(),
            'pro_expires_at'            => $this->pro_expires_at,
            'issuer'                    => $this->getIssuer(),
            'avatars'                   => [
                'merged'  => $mergedPhoto,
                'default' => $defaultPhoto,
                'adult'   => $adultAvatar,
            ],
            'is_guide_modal_shown'      => $this->is_guide_modal_shown,

            'app_view_sensitive_events' => $this->app_view_sensitive_events,
            'app_view_sensitive_media'  => $this->app_view_sensitive_media,
            'invisible'                 => $this->invisible,
            'view_sensitive_events'     => $this->view_sensitive_events,
            'view_sensitive_media'      => $this->view_sensitive_media,
        ];

        if (\Helper::isApp() && !$returnUser['height']) {
            $returnUser['height'] = 0;
        }

        if (\Helper::isApp() && !$returnUser['weight']) {
            $returnUser['weight'] = 0;
        }

        $returnUser = array_map(function($el) {
            return is_null($el) ? '' : $el;
        }, $returnUser);

        return $returnUser;
    }

    public function getDefaultPhoto(): ?UserPhoto
    {
        return $this->photos->first(function($photo) {
            return $photo->is_default == 'yes' && $photo->slot == 'clear';
        });
    }

    public function getAdultPhoto(): ?UserPhoto
    {
        return $this->photos->first(function($photo) {
            return $photo->is_default == 'yes' && $photo->slot == 'adult';
        });
    }

    /*
     * - In "edit profile" we offer two avatars, but only one of them will be displayed in the public profile
     * - By default, the default avatar will be displayed as primary profile image
     * - The default avatar MUST be non-adult, otherwise it is rejected.
     * - If the user has set an adult avatar, this will be shown as default profile picture in the website, but not the app.
     * - Adult version should never be shown in app version
     * - Prohibited image should never be shown as avatar even for image owner
     */
    public function getMergedPhoto(bool $ignoreRestrictions = false): ?UserPhoto
    {
        $clearPhoto = $this->getDefaultPhoto();
        $adultPhoto = $this->getAdultPhoto();

        if (!$ignoreRestrictions) {
            if ($clearPhoto and ($clearPhoto->isProhibited())) {
                $clearPhoto = null;
            }

            if ($adultPhoto and ($adultPhoto->isProhibited() || $adultPhoto->restrictedInMobileApplication())) {
                $adultPhoto = null;
            }
        }

        if (\Helper::isMarketplace() && !$ignoreRestrictions) {
            $adultPhoto = null;
        }

        return $adultPhoto ?? $clearPhoto;
    }

    public function getRawPhotoUrlByKey(string $key): ?string
    {
        $clearPhoto = $this->getDefaultPhoto();
        $adultPhoto = $this->getAdultPhoto();
        $source = [
            'adult' => [
                'small' => $adultPhoto ? $adultPhoto->getUrl('180x180', true) : UserPhoto::DEFAULT_IMAGE_SMALL,
                'orig' => $adultPhoto ? $adultPhoto->getUrl('orig', true) : UserPhoto::DEFAULT_IMAGE_ORIGINAL
            ],
            'clear' => [
                'small' => $clearPhoto ? $clearPhoto->getUrl('180x180', true) : UserPhoto::DEFAULT_IMAGE_SMALL,
                'orig' => $clearPhoto ? $clearPhoto->getUrl('orig', true) : UserPhoto::DEFAULT_IMAGE_ORIGINAL
            ]
        ];
        return Arr::get($source, $key, null);
    }

    public function getPhotoUrl(string $size = 'orig', bool $ignoreRestrictions = false, User $recipient = null): ?string
    {
        $defaultPhoto = $this->getMergedPhoto($ignoreRestrictions);
        return !empty($defaultPhoto) ?
            $defaultPhoto->getUrl($size, $ignoreRestrictions, $recipient)
            :
            ($size === 'orig' ? UserPhoto::DEFAULT_IMAGE_ORIGINAL : UserPhoto::DEFAULT_IMAGE_SMALL);
    }

    public function getPhotoRating(): ?string
    {
        $defaultPhoto = $this->getDefaultPhoto();
        return !empty($defaultPhoto) ? $defaultPhoto->getRating() : null;
    }

    /**
     * @param null|User $retriever
     *
     * @return array
     */
    public function getPublicAttributes(?User $retriever = null) {
        if (!$this->deleted_at) {
            /** @var Collection $publicPhotos */
            $publicPhotos = $this->publicPhotos;

            $defaultPhoto = $this->getDefaultPhoto();
            if ($defaultPhoto) {
                $publicPhotos->prepend($defaultPhoto);
            }

            $adultPhoto = $this->getAdultPhoto();
            if ($adultPhoto ) {
                $publicPhotos->prepend($adultPhoto);
            }

            // Photos
            $publicPhotos->transform(function($photo) {
                /** @var UserPhoto $photo */
                $photo->setUrls();
                return $photo;
            });

            $photos = $this->isPro()
                          ? $publicPhotos->toArray()
                          : $publicPhotos->take(config('const.MAX_PUBLIC_PICTURES_AMOUNT') + ($defaultPhoto && $adultPhoto ? 2 : ($defaultPhoto || $adultPhoto ? 1 : 0)))->toArray();

            // Videos
            $this->publicVideos->transform(function($video) {
                /** @var UserVideo $video */
                $video->setUrls();
                return $video;
            });
            $videos = !$this->isPro() ?
                $this->publicVideos->take(config('const.MAX_PUBLIC_VIDEOS_AMOUNT'))->toArray()
                :
                $this->publicVideos->toArray();

            if (!empty($retriever)) {
                $tap = Notification::where([
                    'user_from' => $retriever->id,
                    'user_to' => $this->id,
                    'type' => 'wave',
                ])
                ->whereRaw('idate > NOW() - INTERVAL 24 HOUR')
                ->first();
            }
        }

        if (!empty($retriever)) {
            $chatService = new ChatService();
            $chatService->setCurrentUser($retriever);
            $chatService->setInterlocutor($this);
            $chatService->setChannel(Message::CHANNEL_USER);
            $unreadMessagesCount = $chatService->getUnreadMessagesCount();
        }

        $result = [
            'id'                        => $this->id,
            'link'                      => $this->link,
            'about'                     => $this->about,
            'user_group'                => $this->user_group,
            'name'                      => $this->name,
            'height'                    => $this->height,
            'weight'                    => $this->weight,
            'position'                  => $this->position,
            'dob'                       => $this->dob->format('Y-m-d'),
            'body'                      => $this->body,
            'penis'                     => $this->penis,
            'hiv'                       => $this->hiv,
            'drugs'                     => $this->drugs,
            'locality'                  => $this->locality,
            'state'                     => $this->state,
            'country'                   => $this->country,
            'country_code'              => $this->country_code,
            'deleted_at'                => $this->deleted_at,
            'status'                    => $this->status == self::STATUS_GHOSTED ? self::STATUS_ACTIVE : $this->status,
            'tags'                      => $this->tags->toArray(),
            'tap'                       => $tap ?? null,
            'public_photos'             => empty($this->deleted_at) ? $photos : [],
            'public_videos'             => empty($this->deleted_at) ? $videos : [],
            'isOnline'                  => empty($this->deleted_at) ? $this->isOnline() : false,
            'wasRecentlyOnline'         => empty($this->deleted_at) ? $this->wasRecentlyOnline() : false,
            'age'                       => empty($this->deleted_at) && $this->show_age == 'yes' ? $this->dob->age : 0,
            'isFavorite'                => empty($this->deleted_at) ? (bool)$this->isFavorite() : false,
            'unreadMessagesCount'       => $unreadMessagesCount ?? 0,
            'photo_small'               => empty($this->deleted_at) ? $this->getPhotoUrl('180x180') : UserPhoto::DELETED_IMAGE_SMALL,
            'photo_orig'                => empty($this->deleted_at) ? $this->getPhotoUrl('orig') : UserPhoto::DELETED_IMAGE_SMALL,
            'distanceMeters'            => !empty($retriever) ?
                (int)\Backend::getDistanceMetersBetween($retriever->lat, $retriever->lng, $this->lat, $this->lng)
                :
                null
        ];

        return $result;
    }

    /**
     * Format user data for Rush view
     * @return array
     */
    public function formatForRush()
    {
        /** @var UserPhoto $photo */
        $isPro = $this->isPro();
        return [
            'id'                       => $this->id,
            'name'                     => $this->name,
            'isPro'                    => $isPro,
            'profile_photo'            => $this->getPhotoUrl('180x180'),
            'discreet_mode'            => $this->discreet_mode && $isPro ? true : false,
            'notification_sound'       => $this->notification_sound == 'yes' ? true : false,
            'view_sensitive_media'     => $this->view_sensitive_media,
            'has_unseen_messages'      => (bool)$this->has_new_messages,
            'has_unseen_notifications' => (bool)$this->has_notifications,
        ];
    }

    /**
     * @param string $mode
     * @param null|User $retriever
     * @param bool $privacyEnabled
     *
     * @return array
     */
    public function getAttributesByMode(string $mode, ?User $retriever = null, bool $privacyEnabled = false): array
    {
        if (defined("FORCE_CACHE_FILLING") || (bool) config('cache.users_cache_attributes', false) === true) {
            $data = json_decode(Redis::get('user_attributes_by_mode.' . $this->id.'.'.$mode) ?? '', true);
        }

        if (empty($data)) {
            // General fields. Might be suppressed below.
            $data = [
                'id' => $this->id,
                'link' => $this->link,
            ];

            if ($mode == self::ATTRIBUTES_MODE_GROUP_MESSAGE && $this->isBlockedBy($this)) {
                $data['name'] = self::BLOCKED_NAME;
                $data['link'] = null;
            }

            if ($mode == self::ATTRIBUTES_MODE_GROUP_MESSAGE) {
                $data['age'] = $this->show_age == 'yes' ? $this->dob->age : 0;
                $data['height'] = $this->height;
                $data['weight'] = $this->weight;
                $data['position'] = $this->position;
            }

            // Discover initial request
            if ($mode == self::ATTRIBUTES_MODE_DISCOVER) {
                $data['height'] = $this->height;
                $data['weight'] = $this->weight;
                $data['position'] = $this->position;
                $data['user_group'] = $this->user_group;
                $data['locality'] = $this->locality;
                $data['country'] = $this->country;
                $data['has_videos'] = (bool)$this->publicVideos()->count();
                $data['age'] = $this->show_age == 'yes' ? $this->dob->age : 0;
            }

            // Discover poll request
            if ($mode == self::ATTRIBUTES_MODE_STATUS) {
                // General fields should be suppressed
                $data = [
                    'id' => $this->id,
                    'link' => $this->link,
                    // Do not reveal ghosted status in public for anti-spam reasons
                    'status' => $this->status == self::STATUS_GHOSTED ? self::STATUS_ACTIVE : $this->status,
                    'deleted_at' => $this->deleted_at,
                    'locality' => $this->locality,
                    'country' => $this->country,
                ];
            }

            if (defined("FORCE_CACHE_FILLING") || (bool)config('cache.users_cache_attributes', false) === true) {
                Redis::set('user_attributes_by_mode.' . $this->id.'.'.$mode, json_encode($data ?? []));
            }
        }

        $data['isFavorite'] = !empty($retriever) && !$privacyEnabled ? $this->isFavoriteFor($retriever) : false;

        // User request
        if ($mode == self::ATTRIBUTES_MODE_FULL) {
            $this->getPublicAttributes($retriever);
        }

        $data['isOnline'] = !empty($this->deleted_at) ? false : $this->isOnline();
        $data['wasRecentlyOnline'] = !empty($this->deleted_at) ? false : $this->wasRecentlyOnline();
        $data['photo_small'] = !empty($this->deleted_at) ? UserPhoto::DELETED_IMAGE_SMALL : (!$privacyEnabled ? $this->getPhotoUrl('180x180') : UserPhoto::DEFAULT_IMAGE_SMALL);
        $data['photo_rating'] = !$privacyEnabled ? $this->getPhotoRating() : null;
        $data['name'] = !$privacyEnabled ? $this->name : null;
        $data['deleted_at'] = $this->deleted_at;

        if ($mode == self::ATTRIBUTES_MODE_DISCOVER || $mode == self::ATTRIBUTES_MODE_STATUS) {
            $data['distanceMeters'] = (int) $this->distanceMeters;
        }

        return $data;
    }

    /**
     * @return int
     */
    public function getFavouritesCount(): int
    {
        if (defined("FORCE_CACHE_FILLING") || (bool) config('cache.users_cache_attributes', false) === true) {
            $favourites = Redis::get('favourites_count.' . $this->id);

            if (empty($favourites)) {
                $getFreshFavourites = $this->favorites->count();
                Redis::set('favourites_count.' . $this->id, $getFreshFavourites);
                $favourites = Redis::get('favourites_count.' . $this->id);
            }

            return (int)$favourites;
        }

        return $this->favorites->count();
    }

    public function getBlockedCount(): int
    {
        if (defined("FORCE_CACHE_FILLING") || (bool) config('cache.users_cache_attributes', false) === true) {
            $blocked = Redis::get('blocked_count:' . $this->id);

            if (empty($blocked)) {
                $getFreshBlocked = $this->blocked->count();
                Redis::set('blocked_count:' . $this->id, $getFreshBlocked);
                $blocked = Redis::get('blocked_count:' . $this->id);
            }

            return (int) $blocked;
        }

        return $this->blocked->count();
    }

    public static function getCachedUser($userId)
    {
        $user = json_decode((string) Redis::get('cached_user.' . $userId) ?? '');

        if (empty($user)) {
            $user = User::where('id', $userId)->first();
            Redis::set('cached_user.' . $userId, json_encode($user));
        }

        return $user;
    }

    public static function getCachedAttributesByMode($baseUser, string $mode, ?User $retriever = null, bool $privacyEnabled = false)
    {
        if (method_exists($baseUser, 'getAttributesByMode')) {
            return $baseUser->getAttributesByMode($mode, $retriever, $privacyEnabled);
        }

        /** @var User $freshedUser */
        $freshedUser = User::find($baseUser->id);

        return $freshedUser->getAttributesByMode($mode, $retriever, $privacyEnabled);
    }

    public static function cleanAttributesCache($userId)
    {
        Redis::del('user_attributes_by_mode.'.$userId.'.'.User::ATTRIBUTES_MODE_CONVERSATION);
        Redis::del('user_attributes_by_mode.'.$userId.'.'.User::ATTRIBUTES_MODE_DISCOVER);
        Redis::del('user_attributes_by_mode.'.$userId.'.'.User::ATTRIBUTES_MODE_FULL);
        Redis::del('user_attributes_by_mode.'.$userId.'.'.User::ATTRIBUTES_MODE_GENERAL);
        Redis::del('user_attributes_by_mode.'.$userId.'.'.User::ATTRIBUTES_MODE_GROUP_MESSAGE);
    }
}

class ApiResetPasswordNotification extends ResetPassword {
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line('You are receiving this email because we received a password reset request for your account.')
            ->action('Reset Password', url(config('app.url').'/reset-password/'.$this->token))
            ->line('If you did not request a password reset, no further action is required.');
    }
}
