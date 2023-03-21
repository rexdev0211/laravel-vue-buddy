<?php

namespace App\Console\Commands;

use App\Models\Rush\Rush;
use App\Models\Rush\RushRank;
use App\Models\Rush\RushView;
use App\Models\Rush\RushStrip;
use App\Models\Rush\RushMedia;
use App\Models\Rush\RushFavorite;
use App\Models\Rush\RushApplause;

use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class ClearRush extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:clear_rush';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear Rush strips';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $strips = $this->getExpiredStrips();
        $deleteRushIds = $this->getRushesIdsToDelete($strips);
        $deleteStripIds = $this->getStripsIdsToDelete($strips);
        $deleteImageIds = $this->getImagesIdsToDelete($strips);

        $this->clearRushContent($deleteStripIds);
        $this->clearRushMedia($deleteImageIds);

        $successfullyDeletedRushIds = $this->clearRushes($deleteRushIds);
        $this->clearRushStats($successfullyDeletedRushIds);
        $this->updateRushRank($deleteRushIds, $successfullyDeletedRushIds);
        $this->clearStrips();
        $this->clearRushesWithExpiredStrips();
        $this->clearFavorites();
        $this->clearUnusedAssets();
    }

    protected function getExpiredStrips(): Collection
    {
        $strips = RushStrip::with('rush')
            ->with('rush.author')
            ->whereRaw('created_at < NOW() - INTERVAL 72 HOUR')
            ->orderBy('created_at', 'ASC')
            ->get()
            ->values();

        return $strips;
    }

    protected function getRushesIdsToDelete(Collection $strips): array
    {
        $rusheIds = $strips->pluck('rush.id')
            ->unique()
            ->values()
            ->toArray();

        return $rusheIds;
    }

    protected function getStripsIdsToDelete(Collection $strips): array
    {
        $stripIds = $strips->pluck('id')->toArray();

        return $stripIds;
    }

    protected function getImagesIdsToDelete(Collection $strips): array
    {
        $imagesIds = $strips->pluck('image_id')
            ->filter(function($image_id) {
                return $image_id;
            })
            ->unique()
            ->values()
            ->toArray();

        return $imagesIds;
    }

    protected function clearRushContent(array $stripIds): void
    {
        // Remove RushApplause
        RushApplause::whereIn('strip_id', $stripIds)->delete();
        $this->info('Applauses removed for '.count($stripIds).' Slides.');

        // Remove RushRank
        RushRank::whereIn('strip_id', $stripIds)->delete();
        $this->info('Ranks data removed for '.count($stripIds).' Slides.');

        // Remove RushStrip
        RushStrip::whereIn('id', $stripIds)->delete();
        $this->info(count($stripIds).' Rush Strip Slides removed.');
    }

    protected function clearRushMedia(array $imageIds): void
    {
        // Remove RushMedia
        $images = RushMedia::withCount('image_strips')
            ->whereIn('id', $imageIds)
            ->get()
            ->filter(function($image) {
                return $image->image_strips_count == 0;
            });

        foreach ($images as $image) {
            /** @var RushMedia $image */
            $image->deleteImage();
            $image->delete();
        }

        $this->info($images->count().' Rush Media files removed.');
    }

    protected function clearRushes(array $rushIds): array
    {
        // Remove Rush
        $deletedRushIds = Rush::whereIn('id', $rushIds)
            ->withCount('strips')
            ->get()
            ->filter(function($image) {
                return $image->strips_count == 0;
            })
            ->pluck('id')
            ->toArray();

        if (count($deletedRushIds) > 0) {
            Rush::whereIn('id', $deletedRushIds)->delete();
            $this->info(count($deletedRushIds).' Rush Strips removed.');
        }

        return $deletedRushIds;
    }

    protected function clearRushStats(array $deletedRushIds): void
    {
        // Remove RushView for deleted Rushes
        RushView::whereIn('rush_id', $deletedRushIds)->delete();
        $this->info('Rush Strip Slides View data removed for '.count($deletedRushIds).' deleted Rush Strips');

        // Remove RushFavorite
        RushFavorite::whereIn('rush_id', $deletedRushIds)->delete();
        $this->info('Rush Strip Slides Favorites data removed for '.count($deletedRushIds).' deleted Rush Strips');
    }

    protected function updateRushRank(array $rushIds, array $deletedRushIds): void
    {
        // Update RushRank for Rushes that still alive
        $stillAliveRushes = array_diff($rushIds, $deletedRushIds);
        if (count($stillAliveRushes)) {
            $rushes = Rush::with('strips')
                ->whereIn('id', $stillAliveRushes)
                ->get();

            foreach ($rushes as $rush) {
                /** @var Rush $rush */
                $rush->updateRanks();
            }
        }
        $this->info('Rush Strips Runk data updated for '.count($stillAliveRushes).' Rush Strips that have Slides deleted');
    }

    protected function clearStrips(): void
    {
        // Ordinary users shouldn't see usual strips older than 24 hrs
        // Ordinary users shouldn't see PRO strips older than 72 hrs

        // Mark Slides that active more than 24 hours as deleted
        $stripsDelete = RushStrip::with('rush.author')
            ->whereRaw('created_at < NOW() - INTERVAL 24 HOUR')
            ->where('is_deleted', 0)
            ->orderBy('created_at', 'ASC')
            ->get()
            ->filter(function($strip) {
                /** @var User */
                return !$strip->rush->author->isPro();
            })
            ->pluck('id');

        RushStrip::whereIn('id', $stripsDelete)
            ->update(['is_deleted' => 1]);

        // Mark Rush Slide Ranks as deleted for just marked as deleted Slides
        RushRank::whereIn('strip_id', $stripsDelete)
            ->update(['is_deleted' => 1]);

        $this->info(count($stripsDelete).' Rush Slides that active longer than 24 hours marked as deleted');
    }

    protected function clearRushesWithExpiredStrips(): void
    {
        // Now we need to check if we have Rushes that have latest slide marked as deleted
        $rushesDeleteIds = RushStrip::where('is_deleted', 1)
            ->pluck('rush_id');

        $rushesDelete = Rush::with('latest_strip')
            ->where('status', 'active')
            ->whereIn('id', $rushesDeleteIds)
            ->get()
            ->filter(function($item) {
                return $item->latest_strip->is_deleted;
            })
            ->pluck('id');

        Rush::whereIn('id', $rushesDelete)
            ->update(['status' => 'deleted']);

        $this->info(count($rushesDelete).' Rush Strips that have Latest Slide active longer than 24 hours marked as deleted');
    }

    protected function clearUnusedAssets(): void
    {
        // Clear junky favorites/applauses/views/ranks
        RushRank::whereDoesntHave('rush')->delete();
        RushView::whereDoesntHave('rush')->delete();
        RushStrip::whereDoesntHave('rush')->delete();
        RushFavorite::whereDoesntHave('rush')->delete();
        RushApplause::whereDoesntHave('rush')->delete();
    }

    protected function clearFavorites(): void
    {
        // Now we need to remove favorites for Rushes with "deleted" state
        // TODO: Leave "deleted" favs for PRO users if we already implemented 72 hours view for PRO users
        RushFavorite::whereHas('rush', function($query){
            $query->where('status', '!=', 'active');
        })->delete();
    }
}
