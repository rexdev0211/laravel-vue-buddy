<?php

namespace App\Console\Commands;

use App\Repositories\PhotoRepository;
use App\Services\HelperService;
use App\UserPhoto;
use Illuminate\Console\Command;
use DB;

class PhotosNudeRating extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:photos-nude-rating';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update photos nude rating';

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
        $choices = ['Profile photos', 'Public photos', 'Event photos'];
        $choice = $this->choice('What photos you want to update?', $choices, 0);
        $limit = intval($this->ask('How many photos do you want to parse (enter a number, leave empty to parse all)?', 'all'));

        $photos = $this->userPhotoRepository->whereNull('nudity_rating');
        if ($choice == $choices[0]) {
            $photos->where('is_default', 'yes');
        } elseif ($choice == $choices[1]) {
            $photos->where('visible_to', 'public');
        } elseif ($choice == $choices[2]) {
            $photos = DB::table('event_user_photo')
                ->join('user_photos', 'user_photos.id', '=', 'event_user_photo.user_photo_id')
                ->whereNull('user_photos.nudity_rating')
                ->select('user_photos.id');
        }

        if ($limit) {
            $photos->limit($limit);
        }

        return $photos->get();
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
            if (
                !($photo instanceof UserPhoto)
                &&
                !empty($photo->id)
            ) {
                $photo = UserPhoto::find($photo->id);
            }

            /** @var UserPhoto $photo */
            $photo->updateNudityRating();
            $this->line("Photo #{$photo->id} rated as {$photo->nudity_rating}");
        }
        $this->info('Updated ' . count($photos) . ' photos');
    }
}
