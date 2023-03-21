<?php namespace App;

use App\Facades\Helper;
use Illuminate\Database\Eloquent\Model;
use Sightengine\SightengineClient;
use Jenssegers\Mongodb\Eloquent\HybridRelations;

class UserPhoto extends Model
{
    use HybridRelations;

    protected $connection = 'mysql';

    protected $appends = [
        'photoUrl'
    ];

    const
        RATING_UNRATED = 'unrated',
        RATING_CLEAR = 'clear',
        RATING_SOFT = 'soft',
        RATING_HARD = 'hard',
        RATING_ADULT = 'adult',
        RATING_PROHIBITED = 'prohibited',

        GENERAL_RATINGS = [
        self::RATING_CLEAR,
        self::RATING_SOFT,
        self::RATING_ADULT,
        self::RATING_PROHIBITED,
    ],

        IMAGE_PATH_PROHIBITED = '/assets/img/prohibited',
        IMAGE_PATH_ADULT = '/assets/img/toohot',
        IMAGE_PATH_CENSORED = '/assets/img/censored',
        IMAGE_PATH_DEFAULT = '/assets/img/default',

        DEFAULT_IMAGES = [
            self::IMAGE_PATH_PROHIBITED,
            self::IMAGE_PATH_ADULT,
            self::IMAGE_PATH_DEFAULT,
            self::IMAGE_PATH_CENSORED,
        ],

        DEFAULT_IMAGE_SMALL = self::IMAGE_PATH_DEFAULT . '_180x180.jpg',
        DEFAULT_IMAGE_ORIGINAL = self::IMAGE_PATH_DEFAULT . '_orig.jpg',
        ADULT_IMAGE_SMALL = self::IMAGE_PATH_ADULT . '_180x180.jpg',
        ADULT_IMAGE_ORIGINAL = self::IMAGE_PATH_ADULT . '_orig.jpg',
        CENSORED_IMAGE_SMALL = self::IMAGE_PATH_CENSORED . '_180x180.jpg',
        CENSORED_IMAGE_ORIGINAL = self::IMAGE_PATH_CENSORED . '_orig.jpg',
        DELETED_IMAGE_SMALL = self::IMAGE_PATH_DEFAULT . '_180x180.jpg';

    protected $table = 'user_photos';

    public $timestamps = false;

    protected $guarded = array('id', '_token');

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * @return bool
     */
    public function isAdult()
    {
        $isAdult = ($this->nudity_rating ?? 0) > config('const.START_NUDITY_RATING');
        if ($this->status == 'reviewed') {
            $isAdultManual = $this->manual_rating == self::RATING_CLEAR ? false : true;
        }

        return $isAdultManual ?? $isAdult;
    }

    /**
     * @return bool
     */
    public function isProhibited()
    {
        return
            $this->manual_rating == self::RATING_PROHIBITED;
    }

    /**
     * @var bool $ignoreRestrictions
     * @return bool
     */
    public function restrictedInMobileApplication(bool $ignoreRestrictions = false)
    {
        $isPro = auth()->user() ?
            auth()->user()->isPro()
            :
            false;

        $sensitiveMediaAllowed = auth()->user()
            ? auth()->user()->app_view_sensitive_media == 'yes'
            : false;

        return
            (
                !$ignoreRestrictions
                &&
                (
                    \Helper::isMarketplace()
                    and
                    $this->isAdult()
                    and
                    !$isPro || !$sensitiveMediaAllowed
                )
            );
    }

    /**
     * Load photo paths for being returned to the frontend
     *
     * @param bool $ignoreRestrictions
     *
     * @return UserPhoto
     */
    public function setUrls(bool $ignoreRestrictions = false, ?User $currentUser = null): UserPhoto
    {
        $this->photo_small = $this->getUrl('180x180', $ignoreRestrictions, $currentUser);
        $this->photo_orig = $this->getUrl('orig', $ignoreRestrictions, $currentUser);
        return $this;
    }

    /**
     * @param string $size
     * @param bool $ignoreRestrictions
     * @param User|null $recipient
     * @return string
     */
    public function getUrl(string $size = 'orig', bool $ignoreRestrictions = false, ?User $recipient = null): string
    {
        return self::getUrlByRelativePath(
            $this->getRelativePath($ignoreRestrictions, $recipient),
            $size
        );
    }

    /**
     * @param bool $ignoreRestrictions
     * @param User|null $recipient
     * @return string
     */
    public function getRelativePath(bool $ignoreRestrictions = false, ?User $recipient = null): string
    {
        if ($recipient) {
            $user = $recipient;
        } else {
            $user = auth()->user();
        }

        if (is_null($user)) {
            return $this->photo;
        }

        if (\Helper::isApp()) {
            $viewSensitiveMedia = ($user->app_view_sensitive_media == 'yes' || $user->app_view_sensitive_media == 1) && $user->isPro();
        } else {
            $viewSensitiveMedia = ($user->web_view_sensitive_content == 'yes' || $user->web_view_sensitive_content == 1);
        }

        // Won't be shown anywhere (except owner); regardless PRO or "view_sensitive_media" option
        if (!$ignoreRestrictions && $this->isProhibited()) {
            return self::IMAGE_PATH_PROHIBITED;
        }

        if (
            $this->slot === 'clear'
            &&
            (
                $this->status === 'queued'
                ||
                $this->status === 'reviewed'
            )
            &&
            $this->isAdult()
            &&
            !$ignoreRestrictions
        ) {
            return self::IMAGE_PATH_DEFAULT;
        }

        if (!\Helper::adultContentEnabled()) {
            return self::IMAGE_PATH_ADULT;
        }

        if (
            $user->id !== $this->user_id
            &&
            !$viewSensitiveMedia
            &&
            !$ignoreRestrictions
            &&
            $this->isAdult()
            &&
            !Helper::isApp()
        ) {
            return self::IMAGE_PATH_ADULT;
        }

        if (
            $user->id !== $this->user_id
            &&
            $this->restrictedInMobileApplication($ignoreRestrictions)
            &&
            !$ignoreRestrictions
            &&
            $this->isAdult()
            &&
            Helper::isApp()
        ) {
            return self::IMAGE_PATH_ADULT;
        }

        // Won`t be shown in staging environment
        if (!$ignoreRestrictions && $this->isAdult() && \Helper::censorshipEnabled()) {
            return self::IMAGE_PATH_CENSORED;
        }

        // An ordinary photo, will be shown everywhere
        return $this->photo;
    }

    /**
     * @return string
     */
    public function getRating(): string
    {
        if ($this->isProhibited()) {
            return self::RATING_PROHIBITED;
        }

        if ($this->isAdult()) {
            return self::RATING_ADULT;
        }

        return self::RATING_CLEAR;
    }

    /**
     * Format data for rush view
     *
     * @return array
     */
    public function formatForRushView(): array
    {
        return [
            'id'    => $this->id,
            'image' => $this->photo,
            'small' => self::getUrlByRelativePath($this->photo, '180x180'),
            'orig'  => self::getUrlByRelativePath($this->photo),
        ];
    }

    /**
     * @param string $fileBaseName
     * @param string $size
     *
     * @return mixed
     */
    public static function getUrlByRelativePath(?string $fileBaseName, $size = 'orig'): string
    {
        return self::getPhotoUrl($fileBaseName, $size, 'users');
    }

    /**
     * @param int $itemId
     * @param string $size
     *
     * @return string
     */
    public static function getAdminPhotoUrl(int $itemId, string $size = 'orig'): string
    {
        $fileBaseName = substr($itemId, 0, 1) . '/' . substr($itemId, 0, 2) . '/' . $itemId;
        return self::getPhotoUrl($fileBaseName, $size, 'admins');
    }

    /**
     * @return string
     */
    public function getPhotoUrlAttribute(): string
    {
        return static::getPhotoUrl($this->photo, 'orig', 'users');
    }

    /**
     * @param string $fileBaseName
     * @param string $size
     * @param string $subdir
     * @param string $quadrant
     * @param bool $baseUrl
     *
     * @return string
     */
    public static function getPhotoUrl(string $fileBaseName, ?string $size, ?string $subdir, ?string $quadrant = 'T', bool $baseUrl = false): string
    {
        $size = $size ?? 'orig';

        $rootDir = public_path();
        $filePrefix = 'uploads/' . $subdir . '/' . $fileBaseName;
        $fileOriginal = $filePrefix . '_orig.jpg';

        if (empty($fileBaseName)) {
            return UserPhoto::IMAGE_PATH_DEFAULT . "_$size.jpg";
        }

        // Default images
        if (in_array($fileBaseName, UserPhoto::DEFAULT_IMAGES)) {
            return url('/') . $fileBaseName . "_$size.jpg";
        }

        //if no image - use the default image
        if (
            !file_exists($rootDir . '/' . $fileOriginal)
            ||
            !filesize($rootDir . '/' . $fileOriginal)
        ) {
            $defaultImgPath = UserPhoto::IMAGE_PATH_DEFAULT . "_$size.jpg";
            $defaultImgFullPath = public_path($defaultImgPath);
            if (!file_exists($defaultImgFullPath) || !filesize($defaultImgFullPath)) {
                return UserPhoto::IMAGE_PATH_DEFAULT . "_180x180.jpg";
            }

            return UserPhoto::IMAGE_PATH_DEFAULT . "_$size.jpg";
        }

        $relativeFileName = $filePrefix . '_' . $size . '.jpg';
        $fileName = $rootDir . '/' . $relativeFileName;
        $fileOriginal = $rootDir . '/' . $fileOriginal;

        //create thumb if it doesn't exist || refresh thumb if original is newer
        if ($size != 'orig') {
            if (
                !file_exists($fileName)
                or
                filemtime($fileName) < filemtime($fileOriginal)
            ) {
                try {
                    $thumb = \PhpThumbFactory::create($fileOriginal);
                    preg_match('/^(\d+)x(\d+)$/', $size, $sizeArr);
                    $thumb->adaptiveResizeQuadrant($sizeArr[1], $sizeArr[2], $quadrant)
                        ->pad($sizeArr[1], $sizeArr[2]);
                    $thumb->save($fileName);
                } catch (\Exception $e) {
                    \Log::error("+++ Get photo url error: $fileBaseName - $size - $subdir - $quadrant - $baseUrl - " . $e->getMessage());
                }
            }
        }

        if (!$baseUrl) {
            $baseUrl = url('/');
        }

        //now return the direct link to the image
        return $baseUrl . '/' . $relativeFileName;
    }

    /**
     * @return float|null
     */
    public function getNudityRating(): ?float
    {
        $photoUrl = $this->getUrl('orig', true);

        if (
            \App::environment() == 'development'
            ||
            strpos($photoUrl, UserPhoto::IMAGE_PATH_DEFAULT) !== false
        ) {
            return 0.001;
        }

        $client = new SightengineClient(config('const.SightengineClient_USER'), config('const.SightengineClient_PSW'));
        try {
            $output = $client->check(['nudity'])->set_url($photoUrl);
            return (float)$output->nudity->raw;
        } catch (\Exception $e) {
            \Log::error($e->getCode());
            \Log::error($e->getMessage());
            return null;
        }
    }

    /**
     * @param bool $force
     *
     * @return void
     */
    public function updateNudityRating(bool $force = true)
    {
        if ($force || $this->nudity_rating === null) {
            $nudityRating = $this->getNudityRating();
            if ($nudityRating !== null) {
                $this->nudity_rating = $nudityRating;
                $this->save();
            }
        }
    }

    public function setAsDefault(string $slot)
    {
        // Set nudity rating
        $this->updateNudityRating(false);

        // Clear slot
        UserPhoto::where([
            'user_id' => $this->user_id,
            'slot' => $slot,
            'is_default' => 'yes',
            'visible_to' => 'public'
        ])->update([
            'is_default' => 'no',
            'visible_to' => 'private',
            'slot' => null
        ]);

        // Insert photo to slot
        $this->update([
            'is_default' => 'yes',
            'visible_to' => 'public',
            'slot' => $slot
        ]);
    }
}
