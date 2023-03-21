<?php

namespace App\Console\Commands;

use App\UserVideo;
use Illuminate\Console\Command;

class ListMissingVideos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'list:missing_videos {--debug= : Debug}
                                                {--paths= : Show Paths}
                                                {--table= : Show Table}
                                                {--limit= : Limit processed videos}
                                                {--skip=  : Skip processed videos}
                                                {--nourls= : Dont show urls in table}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show list of user videos that missing file';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->option('debug')) {
            $this->info('[List:MissingVideos] Started');
        }

        $videos = UserVideo::whereNotIn('status', ['waiting', 'processing'])
                           ->orderBy('id', 'ASC');

        if ($this->option('skip')) {
            $videos = $videos->skip($this->option('skip'));
        }

        if ($this->option('limit')) {
            $videos = $videos->limit($this->option('limit'));
        }

        $videos = $videos->get();
        $total  = $videos->count();

        if ($this->option('debug')) {
            $this->info('[List:MissingVideos] Total '.$total.' videos found');
        }
        $bar = $this->output->createProgressBar($total);

        $bar->start();
        if ($this->option('debug')) {
            $this->info('');
        }

        $missing     = [];
        $chunks      = $videos->chunk(500);
        $missPrev    = false;
        $firstMissed = null;
        $lastMissed  = null;
        foreach ($chunks as $chunk) {
            foreach ($chunk as $video) {
                $path = $video->getPublicVideoPath('mp4');
                if ($this->option('debug') && $this->option('paths')) {
                    $this->info($path);
                }

                if (!file_exists($path)) {
                    $missing[] = (object) [
                        'id'         => $video->id,
                        'user_id'    => $video->user_id,
                        'video_name' => $video->video_name,
                        'url'        => $video->getVideoUrl('mp4'),
                        'path'       => $path,
                        'created_at' => null,
                    ];

                    if (!$missPrev) {
                        $missPrev  = true;
                    }
                } elseif ($missPrev) {
                    foreach ($missing as $key => $video) {
                        if (!$video->created_at) {
                            $date = date('Y-m-d H:i:s', filectime($path));
                            $video->created_at = $date;

                            if (!$firstMissed) {
                                $firstMissed = '#'.$video->id.' ('.$date.')';
                            }

                            $lastMissed = '#'.$video->id.' ('.$date.')';
                        }
                    }

                    $missPrev = false;
                }

                $bar->advance();
            }
        }

        $bar->finish();
        if ($this->option('debug')) {
            $this->info('');
            $this->info('[List:MissingVideos] Total '.count($missing).'/'. $total .' videos missing');
            $this->info('[List:MissingVideos] First Missed File '.$firstMissed);
            $this->info('[List:MissingVideos] Last Missed File '.$lastMissed);
        }

        if ($this->option('table')) {
            $header = ['File ID', '~Created At', 'User ID', 'File name'];

            if (!$this->option('nourls')) {
                $header = array_merge($header, ['Link', 'Path']);
            }

            $body   = [];
            foreach ($missing as $video) {
                $item = [
                    $video->id,
                    $video->created_at,
                    $video->user_id,
                    $video->video_name,
                ];

                if (!$this->option('nourls')) {
                    $item = array_merge($item, [$video->url, $video->path]);
                }

                $body[] = $item;
            }

            $this->table($header, $body);
        }

        if ($this->option('debug')) {
            $this->info('[List:MissingVideos] Finished');
        }
    }
}
