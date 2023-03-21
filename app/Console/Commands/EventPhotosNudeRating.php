<?php

namespace App\Console\Commands;

use App\Repositories\PhotoRepository;
use App\Services\HelperService;
use Illuminate\Console\Command;

class EventPhotosNudeRating extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:event-photos-nude-rating';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update event photos nude rating';

    private $userPhotoRepository;

    private $helperService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(PhotoRepository $userPhotoRepository, HelperService $helperService)
    {
        parent::__construct();

        $this->userPhotoRepository = $userPhotoRepository;
        $this->helperService = $helperService;
    }

    /**
     * @return mixed
     */
    private function getPhotosList()
    {
        $sql = "  SELECT p.* FROM event_user_photo e 
                  join `user_photos` p on p.id = e.user_photo_id 
                  where nudity_rating is null group by p.id";

        return \DB::select($sql);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $photos = $this->getPhotosList();

        foreach ($photos as $photo) {
            $nudityRating = $photo->getNudityRating();

            if (!is_null($nudityRating)) {
                $this->userPhotoRepository->updatePhoto($photo->id, ['nudity_rating' => $nudityRating]);
                $message = 'photo '. $photo->id . ' with base url ' . $photo->photo . ' updated to '. $nudityRating;
                $this->line($message);
                \Log::info($message);
            } else {
                $message = 'NULL value returned';
                $this->error($message);
                \Log::error($message);
            }
        }

        $this->info('Updated ' . count($photos) . ' photos');
    }
}
