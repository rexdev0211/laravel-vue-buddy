<?php

namespace App\Services;

use App\Events\VideoDownloadPercentage;
use FFMpeg\Filters\Video\ResizeFilter;
use FFMpeg\Format\Video\DefaultVideo;
use FFMpeg\Format\Video\WebM;
use FFMpeg\Format\Video\X264;
use Carbon\Carbon;
use Log;
use App;

use App\User;
use App\UserVideo;

use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use FFMpeg\Media\Video;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\Coordinate\Dimension;

use PhpThumbFactory;

class VideoConverter
{
    const GIF_DURATION = 3;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var UserVideo
     */
    protected $video;

    /**
     * @var string
     */
    protected $extension;

    /**
     * @var int
     */
    protected $phase;

    /**
     * @var FFMpeg
     */
    protected $ffmpeg;

    /**
     * @var FFProbe
     */
    protected $ffprobe;

    /**
     * Video file
     *
     * @var Video
     */
    protected $sourceVideoFileInstance;

    /**
     * @var string
     */
    protected $sourceVideoFullPath;

    /**
     * @var int
     */
    protected $width;

    /**
     * @var int
     */
    protected $height;

    /**
     * @var bool
     */
    protected $isPortrait;

    /**
     * @var float
     */
    protected $duration;

    /**
     * @var string
     */
    protected $jobId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $envOs = config('const.SERVER_OS');
        if ($envOs == 'mac') {
            $this->ffmpeg = FFMpeg::create([
                'ffmpeg.binaries'  => '/usr/local/bin/ffmpeg',
                'ffprobe.binaries' => '/usr/local/bin/ffprobe',
            ]);
        } else {
            $this->ffmpeg = FFMpeg::create();
        }

        $this->ffprobe = $this->ffmpeg->getFFProbe();
    }

    /**
     * Method returns codec class depend on extension
     *
     * @param string $extension
     * @return DefaultVideo
     * @throws \Exception
     */
    public function getCodecByExtension(string $extension): DefaultVideo
    {
        $codecMap = [
            'mp4' => (new X264('aac'))->setAdditionalParameters(['-strict', '-2']),
            'webm' => new WebM()
        ];

        if (empty($codecMap[$extension])) {
            throw new \Exception("No codec mapping for .$extension extension!");
        }

        return $codecMap[$extension];
    }

    /**
     * Convert video
     *
     * @return UserVideo
     * @throws \Exception
     */
    public function run(): UserVideo
    {
        $this->sourceVideoFullPath = $this->video->getSourceVideoFullPath();
        $this->sourceVideoFileInstance = $this->ffmpeg->open($this->sourceVideoFullPath);

        $this->info("Processing started", $this->sourceVideoFullPath);
        $this->captureVideoAttributes();

        // Video processing consists of 2 phases:
        // - .mp4 job by worker "mp4"
        // - .webm job by worker "webm"
        // Resize and create thumbnail only during the first phase ("mp4")
        if ($this->phase === 1) {
            $this->video->refresh();
            $this->video->status = 'processing';
            $this->video->save();

            $this->resizeVideo();
            sleep(1);
            $this->createThumbnails();
        }

        $this->publishVideo();

        // Mark video accessible after the first phase ("mp4")
        if ($this->phase === 1) {
            $this->video->refresh();
            $this->video->status = 'accessible';
            $this->video->save();
        }

        // Mark video processed after the second phase ("webm")
        if ($this->phase === 2) {
            $this->video->refresh();
            $this->video->status = 'processed';
            $this->video->save();

            // Delete the source
            // Allow deletion only during second phase (after .webm is ready)
            if ($this->phase === 2) {
                $this->video->deleteSourceVideo();
                $this->info("Source video deleted");
            }
        }

        $this->info("Processing finished!");

        return $this->video;
    }

    /**
     * Set video width, height and duration
     *
     * @return void
     */
    private function captureVideoAttributes()
    {
        $dimensions = $this->ffprobe
            ->streams($this->sourceVideoFullPath)
            ->videos()
            ->first()
            ->getDimensions();

        $this->width  = (int)$dimensions->getWidth();
        $this->height = (int)$dimensions->getHeight();
        $this->duration = (float)$this->ffprobe->format($this->sourceVideoFullPath)->get('duration');
        $this->isPortrait = $this->height > $this->width;

        $this->info("Captured attributes: width={$this->width}; height={$this->height}; duration={$this->duration} sec.");
    }

    /**
     * Resize the video
     *
     * @return void
     */
    private function resizeVideo()
    {
        $hdWidth = $this->isPortrait ? 720 : 1280;
        $hdHeight = $this->isPortrait ? 1280 : 720;

        if (
            $this->width > $hdWidth
            ||
            $this->height > $hdHeight
        ) {
            $this->info("Resizing from {$this->width}x{$this->height} to {$hdWidth}x{$hdHeight}");

            // Convert to HD format
            $this->sourceVideoFileInstance
                ->filters()
                ->resize(new Dimension($hdWidth, $hdHeight), ResizeFilter::RESIZEMODE_INSET)
                ->synchronize();

            $this->info("Resized");
        } else {
            $this->info("No resizing needed. Keeping size {$this->width}x{$this->height})");
        }
    }

    /**
     * Create a thumbnail
     *
     * @return void
     * @throws \Exception
     */
    private function createThumbnails()
    {
        $this->info("Creating thumbnails (big + small)");

        // If video duration is more than 10 seconds get frame on 10th second
        // else
        // get the first frame
        $thumbnailSecond = 0;
        if ($this->duration > 10) {
            $thumbnailSecond = 10;
        }

        // Generate target thumbs directory
        $createDirResult = $this->video
            ->getPublicDisk()
            ->makeDirectory($this->video->getRelativeThumbnailDirectory());
        if ($createDirResult) {
            $this->info("Public thumbnail directory successfully created:\n" . $this->video->getPublicThumbnailDirectory());
        } else {
            $this->info("Cannot create public thumbnail directory!\n" . $this->video->getPublicThumbnailDirectory());
        }

        // Generate big thumbnail
        $publicThumbnailPath = $this->video->getPublicThumbnailPath();
        $this->sourceVideoFileInstance
            ->frame(TimeCode::fromSeconds($thumbnailSecond))
            ->save($publicThumbnailPath);
        $this->info("Public thumbnail saved", $publicThumbnailPath);

        // Resize thumbnail if video is bigger than HD
        $hdWidth = $this->isPortrait ? 720 : 1280;
        $hdHeight = $this->isPortrait ? 1280 : 720;
        if (
            $this->width > $hdWidth
            ||
            $this->height > $hdHeight
        ) {
            $thumb = PhpThumbFactory::create($publicThumbnailPath);
            $thumb->resize($hdWidth, $hdHeight);
            $thumb->save($publicThumbnailPath);
            $this->info("Public thumbnail resized from {$this->width}x{$this->height} to 1280x720");
        }

        // Generate small thumbnail
        $publicMiniThumbnailPath = $this->video->getPublicThumbnailPath('180x180');
        $thumb = PhpThumbFactory::create($publicThumbnailPath);
        $thumb->adaptiveResizeQuadrant(180, 180, 'T')
              ->pad(180, 180);
        $thumb->save($publicMiniThumbnailPath);
        $this->info("Public mini thumbnail saved", $publicMiniThumbnailPath);

        // Generate gif thumbnail
        $publicGifThumbnailPath = $this->video->getPublicGifThumbnailPath();
        $this->sourceVideoFileInstance
            ->gif(TimeCode::fromSeconds($thumbnailSecond), new Dimension(300, 300), self::GIF_DURATION)
            ->save($publicGifThumbnailPath);
        $this->info("Public gif thumbnail saved", $publicGifThumbnailPath);

        $this->video->refresh();
        $this->video->thumbnail_type = 'gif';
        $this->video->save();
    }

    /**
     * Move video file to public folder
     *
     * @return void
     */
    private function publishVideo() {
        $this->info("Converting video");

        $publicVideoRelativeDirectory = $this->video->getRelativeVideoDirectory();
        if (!$this->video->getPublicDisk()->exists($publicVideoRelativeDirectory)) {
            $createDirResult = $this->video
                ->getPublicDisk()
                ->makeDirectory($publicVideoRelativeDirectory);
            if ($createDirResult) {
                $this->info("Public video directory successfully created:\n" . $this->video->getPublicVideoDirectory());
            } else {
                $this->info("Cannot create public video directory!\n" . $this->video->getPublicVideoDirectory());
            }
        }

        $publicVideoFullPath = $this->video->getPublicVideoPath($this->extension);
        $this->convertVideo($publicVideoFullPath);
    }

    /**
     * Launch video conversion
     *
     * @param string $outputPath
     * @return void
     */
    private function convertVideo(string $outputPath): void
    {
        $this->info("Converting to .{$this->extension}");

        try {
            $codec = $this->getCodecByExtension($this->extension);

            $videoInfo = @`exiftool {$this->sourceVideoFullPath}`;
            if (!empty($videoInfo)) {
                $this->info("Video data:\n$videoInfo", $outputPath);
            }
            if ($this->extension === 'mp4' || $this->extension === 'webm') {
                $codec->on('progress', function ($video, $format, $percentage) {
                    UserVideo::where('user_id', $this->user->id)
                        ->where('hash', $this->video->hash)
                        ->update(['percentage' => floor($percentage / 2 + ($this->extension === 'mp4' ? 0 : 50))]);
                });
            }
            $this->sourceVideoFileInstance->save($codec, $outputPath);

            if (file_exists($outputPath)) {
                $this->info("Successfully converted to .{$this->extension} and saved to public directory", $outputPath);
            } else {
                $this->info("Successfully converted to .{$this->extension}, but no file found in public directory ($outputPath)!");
            }
        } catch (\Throwable $e) {
            $this->info(
                "Conversion to .{$this->extension} failed!\n" .
                $e->getMessage() . "\n" .
                (!empty($e->getPrevious()) ? $e->getPrevious()->getMessage() : null)
            );
        }
    }

    /**
     * Get file path and size string
     *
     * @param string $filePath
     * @return string
     */
    private function getFilePathAndSize(string $filePath): string
    {
        if (file_exists($filePath)) {
            $suffix = number_format(filesize($filePath) / (1024 * 1024), 2) . ' MB';
        } else {
            $suffix = 'file not found';
        }
        return "$filePath ($suffix)";
    }

    /**
     * Log method
     *
     * @param string $message
     * @param string $filePath
     *
     * @return string
     */
    private function info(string $message, string $filePath = null)
    {
        $timestamp = Carbon::now()->format('Y-m-d H:i:s');
        $suffix = $filePath ? "\n" . $this->getFilePathAndSize($filePath) : null;

        echo "[$timestamp][{$this->jobId}] $message $suffix\n";
        Log::info("[{$this->jobId}] $message $suffix");
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * @return UserVideo
     */
    public function getVideo(): UserVideo
    {
        return $this->video;
    }

    /**
     * @param UserVideo $video
     */
    public function setVideo(UserVideo $video): void
    {
        $this->video = $video;
    }

    /**
     * @return int
     */
    public function getPhase(): int
    {
        return $this->phase;
    }

    /**
     * @param int $phase
     */
    public function setPhase(int $phase): void
    {
        $this->phase = $phase;
    }

    /**
     * @return string
     */
    public function getExtension(): string
    {
        return $this->extension;
    }

    /**
     * @param string $extension
     */
    public function setExtension(string $extension): void
    {
        $this->extension = $extension;
    }

    /**
     * @return string
     */
    public function getJobId(): string
    {
        return $this->jobId;
    }

    /**
     * @param string $jobId
     */
    public function setJobId(string $jobId): void
    {
        $this->jobId = $jobId;
    }
}
