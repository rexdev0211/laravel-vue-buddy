<?php namespace App;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Jenssegers\Mongodb\Eloquent\HybridRelations;
use Sightengine\SightengineClient;

class UserVideo extends Model
{
    use HybridRelations;

    protected $connection = 'mysql';

    const
        RATING_UNRATED = 'unrated',
        RATING_CLEAR = 'clear',
        RATING_SOFT = 'soft',
        RATING_ADULT = 'adult',
        RATING_PROHIBITED = 'prohibited',

        DELETE_SOURCE_VIDEO = 1,
        DELETE_PUBLIC_THUMBS = 2,
        DELETE_PUBLIC_VIDEOS = 4,

        GENERAL_RATINGS = [
            self::RATING_CLEAR,
            self::RATING_SOFT,
            self::RATING_ADULT,
        ],

        DELETE_ALL = self::DELETE_SOURCE_VIDEO | self::DELETE_PUBLIC_THUMBS | self::DELETE_PUBLIC_VIDEOS;

    protected $table = 'user_videos';

    public $timestamps = false;

    protected $guarded = array('id', '_token');

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user() {
        return $this->belongsTo('App\User');
    }

    public function getVideoUrl(string $extension = 'mp4'): string
    {
        return $this->getPublicDisk()->url("/videos/{$this->video_name}.$extension");
    }

    /**
     * @return bool
     */
    public function isAdult()
    {
        $isAdult = ($this->nudity_rating ?? 0) > config('const.START_NUDITY_RATING');
        if ($this->status == 'processed') {
            $isAdultManual = $this->manual_rating == self::RATING_CLEAR ? false : true;
        }

        return $isAdultManual ?? $isAdult;
    }

    /**
     * @return bool
     */
    public function isProhibited()
    {
        return $this->manual_rating == self::RATING_PROHIBITED;
    }

    public function getThumbnailUrl(string $size = 'orig'): string
    {
        if (
            $this->thumbnail_type === 'jpg'
            ||
            $size === 'orig'
        ) {
            return $this->getPublicDisk()->url("/thumbs/{$this->video_name}_{$size}.jpg");

        } elseif ($this->thumbnail_type === 'gif') {
            return $this->getGifThumbnailUrl();

        } else {
            return $this->getPublicDisk()->url("/thumbs/{$this->video_name}_orig.jpg");
        }
    }

    public function getGifThumbnailUrl(): string
    {
        return $this->getPublicDisk()->url("/thumbs/{$this->video_name}.gif");
    }

    /**
     * Load video paths for being returned to the frontend (worker mode)
     */
    public function setUrlsIgnorePlatform() {
        $this->thumb_orig = $this->getThumbnailUrl();
        $this->thumb_small = $this->getThumbnailUrl('180x180');
        $this->video_url = [
            'mp4' => $this->getVideoUrl('mp4'),
            'webm' => $this->getVideoUrl('webm')
        ];
    }

    /**
     * @param bool $ignoreRestrictions
     *
     * Load video paths for being returned to the frontend
     */
    public function setUrls(bool $ignoreRestrictions = false, ?User $currentUser = null)
    {
        $this->thumb_orig = $this->getThumbPath('orig', $ignoreRestrictions, $currentUser);
        $this->thumb_small = $this->getThumbPath('small', $ignoreRestrictions, $currentUser);

        $this->video_url = [
            'mp4' => $this->getVideoUrl('mp4'),
            'webm' => $this->getVideoUrl('webm')
        ];
    }

    /**
     * @param string $size
     * @param bool $ignoreRestrictions
     * @param User|null $currentUser
     * @return string
     */
    public function getThumbPath(string $size = 'orig', bool $ignoreRestrictions = false, ?User $currentUser = null): string
    {
        $user = $currentUser ?? auth()->user();

        $isPro = $user ? $user->isPro() : false;

        $sensitiveMediaAllowed = $user->view_sensitive_media == 'yes' || $user->view_sensitive_media == 1;
        $viewSensitiveMedia = $user->web_view_sensitive_content == 'yes' || $user->web_view_sensitive_content == 1;

        if ($size === 'orig') {
            return $this->getThumbnailUrl();
        } else if ($size === 'small') {
            if (
                !\Helper::isApp()
                &&
                $user->id !== $this->user_id
                &&
                !$viewSensitiveMedia
                &&
                !$ignoreRestrictions
                &&
                $this->isAdult()
                ||
                !\Helper::adultContentEnabled()
            ) {
                return UserPhoto::IMAGE_PATH_ADULT . '_180x180.jpg';
            }

            if (
                \Helper::isMarketplace()
                &&
                !$ignoreRestrictions
                &&
                (!$isPro || !$sensitiveMediaAllowed)
            ) {
                return UserPhoto::IMAGE_PATH_ADULT . '_180x180.jpg';
            }

            return $this->getThumbnailUrl('180x180');
        }
    }

    /**
     * Upload video file directly to video server
     * @param User $user
     * @param UploadedFile $file
     * @param string $hash
     * @param string $visibleTo
     *
     * @return UserVideo
     */
    public function upload(User $user, UploadedFile $file, string $hash, $visibleTo='private'): UserVideo
    {
        $isSingleServerMode = config('filesystems.video.single_server_mode', true);
        $storage = $isSingleServerMode ?
            'instance_1'
            :
            Arr::random(array_keys(config('filesystems.disks.video.source')));

        $storageIndex = "video.source.$storage";

        do {
            $randomPath = implode('/', [str_random(3), str_random(3), str_random(3)]);
        } while (Storage::disk($storageIndex)->exists("/videos/$randomPath"));

        $video = Storage::disk($storageIndex)->putFile("/videos/$randomPath", $file);

        $this->user_id = $user->id;
        $this->video_name = str_replace('videos/', '', strtok($video, '.'));
        $this->hash = $hash;
        $this->orig_extension = $file->extension();
        $this->nudity_rating = config('const.START_NUDITY_RATING') + 0.1;
        $this->visible_to = $visibleTo;
        $this->status = 'waiting';
        $this->storage = $storage;
        $this->save();

        return $this;
    }

    /**
     * Delete source video
     *
     * @param int $deleteMask - Delete bit-mask. Nothing by default.
     *
     * @return void
     */
    protected function deleteAssets(int $deleteMask = 0): void
    {
        $relativeVideoDirectory = $this->getRelativeVideoDirectory();
        $relativeThumbsDirectory = $this->getRelativeThumbnailDirectory();

        // Delete source directory
        if (
            $deleteMask & self::DELETE_SOURCE_VIDEO
            &&
            $this->getSourceDisk()->exists($relativeVideoDirectory)
        ) {
            try {
                $this->getSourceDisk()->deleteDirectory($relativeVideoDirectory);
            } catch (\Throwable $e) {
                Log::error('Cannot remove video source directory', [
                    'path' => $this->getSourceVideoDirectory(),
                    'exception' => $e->getMessage()
                ]);
            }
        }

        // Delete public thumbnails
        if (
            $deleteMask & self::DELETE_PUBLIC_THUMBS
            &&
            $this->getPublicDisk()->exists($relativeThumbsDirectory)
        ) {
            try {
                $this->getPublicDisk()->deleteDirectory($relativeThumbsDirectory);
            } catch (\Throwable $e) {
                Log::error('Cannot remove public video directory', [
                    'path' => $this->getPublicThumbnailDirectory(),
                    'exception' => $e->getMessage()
                ]);
            }
        }

        // Delete public videos
        if (
            $deleteMask & self::DELETE_PUBLIC_VIDEOS
            &&
            $this->getPublicDisk()->exists($relativeVideoDirectory)
        ) {
            try {
                $this->getPublicDisk()->deleteDirectory($relativeVideoDirectory);
            } catch (\Throwable $e) {
                Log::error('Cannot remove public video directory', [
                    'path' => $this->getPublicVideoDirectory(),
                    'exception' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Delete source video only
     *
     * @return void
     */
    public function deleteSourceVideo(): void
    {
        $this->deleteAssets(self::DELETE_SOURCE_VIDEO);
    }

    /**
     * Delete all assets
     *
     * @return void
     */
    public function deleteAllAssets(): void
    {
        $this->deleteAssets(self::DELETE_ALL);
    }

    /**
     * Get public videos disc
     *
     * @return Filesystem
     */
    public function getPublicDisk(): Filesystem
    {
        $isSingleServerMode = config('filesystems.video.single_server_mode', true);
        $storage = $isSingleServerMode ? 'instance_1' : $this->storage;
        return Storage::disk("video.public.$storage");
    }

    /**
     * Get source videos disc
     *
     * @return Filesystem
     */
    public function getSourceDisk(): Filesystem
    {
        $isSingleServerMode = config('filesystems.video.single_server_mode', true);
        $storage = $isSingleServerMode ? 'instance_1' : $this->storage;
        return Storage::disk("video.source.$storage");
    }

    /**
     * Get video filename
     *
     * @return string
     */
    public function getVideoFilename(): string
    {
        $videoPathParts = explode('/', $this->video_name);
        return $videoPathParts[3] ?? $videoPathParts[2];
    }

    /**
     * Source video relative path
     *
     * @param string $nameSuffix
     *
     * @return string
     */
    public function getSourceVideoRelativePath(string $nameSuffix = ''): string
    {
        return "videos/{$this->video_name}{$nameSuffix}.{$this->orig_extension}";
    }

    /**
     * Source video full path
     *
     * @param string $nameSuffix
     *
     * @return string
     */
    public function getSourceVideoFullPath(string $nameSuffix = ''): string
    {
        return $this->getSourceDisk()->path($this->getSourceVideoRelativePath($nameSuffix));
    }

    /**
     * Has source video flag
     *
     * @return bool
     */
    public function hasSourceVideo(): bool
    {
        return $this->getSourceDisk()->exists($this->getSourceVideoRelativePath());
    }

    /**
     * Get public thumbnail root directory
     *
     * @return string
     */
    public function getPublicThumbnailRoot(): string
    {
        return $this->getPublicDisk()->path("thumbs");
    }

    /**
     * Get public video root directory
     *
     * @return string
     */
    public function getPublicVideoRoot(): string
    {
        return $this->getPublicDisk()->path("videos");
    }

    /**
     * Get public thumbnail directory
     *
     * @return string
     */
    public function getPublicThumbnailDirectory(): string
    {
        return $this->getPublicDisk()->path($this->getRelativeThumbnailDirectory());
    }

    /**
     * Get source video directory
     *
     * @return string
     */
    public function getSourceVideoDirectory(): string
    {
        return $this->getSourceDisk()->path($this->getRelativeVideoDirectory());
    }

    /**
     * Get public video directory
     *
     * @return string
     */
    public function getPublicVideoDirectory(): string
    {
        return $this->getPublicDisk()->path($this->getRelativeVideoDirectory());
    }

    /**
     * Get public thumbnail path by it's size
     *
     * @param string $size
     * @return string
     */
    public function getPublicThumbnailPath(string $size = 'orig'): string
    {
        return $this->getPublicDisk()->path("thumbs/{$this->video_name}_{$size}.jpg");
    }

    /**
     * Get public gif thumbnail path
     *
     * @return string
     */
    public function getPublicGifThumbnailPath(): string
    {
        return $this->getPublicDisk()->path("thumbs/{$this->video_name}.gif");
    }

    /**
     * Get public video path
     *
     * @return string
     */
    public function getPublicVideoPath(string $extension): string
    {
        return $this->getPublicDisk()->path("videos/{$this->video_name}.$extension");
    }

    /**
     * Relative thumbs directory
     *
     * @return string
     */
    public function getRelativeThumbnailDirectory(): string
    {
        $videoPathParts = explode('/', $this->video_name);
        $pathParts = [
            'thumbs',
            $videoPathParts[0],
            $videoPathParts[1],
            strlen($videoPathParts[2]) <= 3 ? $videoPathParts[2] : ''
        ];
        return implode('/', $pathParts);
    }

    /**
     * Relative videos directory
     *
     * @return string
     */
    public function getRelativeVideoDirectory(): string
    {
        $videoPathParts = explode('/', $this->video_name);
        $pathParts = [
            'videos',
            $videoPathParts[0],
            $videoPathParts[1],
            strlen($videoPathParts[2]) <= 3 ? $videoPathParts[2] : ''
        ];
        return implode('/', $pathParts);
    }

    public function delete(): ?bool
    {
        $this->deleteAllAssets();

        return parent::delete();
    }

    /**
     * @return float|null
     */
    public function getNudityRating(): ?float
    {
        $videoUrl = $this->getVideoUrl();

        if (\App::environment() == 'development') {
            return 0.001;
        }

        $client = new SightengineClient(config('const.SightengineClient_USER'), config('const.SightengineClient_PSW'));

        try {
            $output = $client->check(['nudity'])->set_url($videoUrl);
            return (float)$output->nudity->raw;
        } catch (\Exception $e) {
            \Log::error($e->getCode());
            \Log::error($e->getMessage());
            return null;
        }
    }

    /**
     * @param bool $force
     */
    public function updateNudityRating($force = true)
    {
        if ($force || $this->nudity_rating === null) {
            $nudityRating = $this->getNudityRating();

            if ($nudityRating !== null) {
                $this->nudity_rating = $nudityRating;
                $this->save();
            }
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany|\Jenssegers\Mongodb\Relations\BelongsToMany
     */
    public function urlSharing()
    {
        return $this->belongsToMany(SharingUrl::class);
    }
}
