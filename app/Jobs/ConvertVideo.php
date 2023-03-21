<?php

namespace App\Jobs;

use App\User;
use App\UserVideo;
use App\Services\VideoConverter;
use App\Events\VideoProcessed;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\MaxAttemptsExceededException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ConvertVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const PROCESS_VIDEO_PHASES = [
        'mp4' => 1,
        'webm' => 2
    ];

    /** @var User */
    protected $user;

    /** @var UserVideo */
    protected $video;

    /** @var string */
    protected $extension;

    /** @var string */
    protected $jobId;

    /** @var string */
    protected $sourceVideoFullPath;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * The maximum number of exceptions to allow before failing.
     *
     * @var int
     */
    public $maxExceptions = 1;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, UserVideo $video, string $extension)
    {
        $this->user  = $user;
        $this->video = $video;
        $this->extension = $extension;
    }

    /**
     * Get phase number
     *
     * @return int
     */
    protected function getPhaseNumber(): int
    {
        return self::PROCESS_VIDEO_PHASES[$this->extension];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $currentPhase = $this->getPhaseNumber();

        try {
            if ($this->video->status !== 'processed') {
                // this process should be fired only on video server.
                // So lets be sure that this is video server by check video file exists
                $this->sourceVideoFullPath = $this->video->getSourceVideoFullPath();
                $this->setJobId();

                if ($this->video->hasSourceVideo()) {
                    $converter = new VideoConverter();
                    $converter->setUser($this->user);
                    $converter->setVideo($this->video);
                    $converter->setExtension($this->extension);
                    $converter->setPhase($currentPhase);
                    $converter->setJobId($this->jobId);
                    $converter->run();
                } else {
                    throw new \Exception("Source video not found in {$this->sourceVideoFullPath}!");
                }
            }
        } catch (\Throwable $error) {
            $this->failed($error);
            return;
        }

        if ($currentPhase === 1) {
            $isSingleServerMode = config('filesystems.video.single_server_mode', true);
            $queueSuffix = $isSingleServerMode ? '' : "-{$this->video->storage}";

            // Start second phase of processing
            dispatch(
                (new ConvertVideo($this->user, $this->video, 'webm'))
                    ->onQueue("video-convert-slow$queueSuffix")
            );
        }

        if ($currentPhase === 2) {
            $this->video->setUrlsIgnorePlatform();

            // Notify user right after .mp4 is done
            event(new VideoProcessed($this->video->toArray()));
        }
    }

    /**
     * The job failed to process.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function failed(\Exception $exception)
    {
        $originalErrorMessage = !empty($exception->getPrevious()) ?
            $exception->getPrevious()->getMessage()
            :
            $exception->getMessage();

        // Do not show user MaxAttempts errors
        if ($exception instanceof MaxAttemptsExceededException){
            $errorMessage = false;
        } else {
            $errorMessage = $originalErrorMessage;
        }

        // Send signal to front-end
        event(new VideoProcessed($this->video->toArray() + ['error' => $errorMessage]));

        // Log error
        \Log::error("[{$this->jobId}] Conversion error", ['error' => $originalErrorMessage]);

        $this->delete();
    }

    /**
     * @return string
     */
    public function getJobId(): string
    {
        return $this->jobId;
    }

    public function setJobId(): void
    {
        $this->jobId = "{$this->extension}@" . substr(md5($this->sourceVideoFullPath), 0, 8);
    }
}
