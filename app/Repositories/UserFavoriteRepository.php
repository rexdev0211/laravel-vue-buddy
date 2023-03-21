<?php namespace App\Repositories;

use App\UserFavorite;

class UserFavoriteRepository extends BaseRepository
{
    public function __construct(UserFavorite $model = null)
    {
        if (empty($model)){
            $model = new UserFavorite();
        }
        parent::__construct($model);
    }

    /**
     * @param $userId
     * @return mixed
     */
    public function addFavorite(int $userId, int $favoriteId) {
        return $this->firstOrCreate([
            'user_id' => $userId,
            'user_favorite_id' => $favoriteId
        ]);
    }

    /**
     * @param $userId
     * @return mixed
     */
    public function removeFavorite(int $userId, int $favoriteId) {
        return $this->where('user_id', $userId)->where('user_favorite_id', $favoriteId)->delete();
    }

    /**
     * @param $userId
     * @return mixed
     */
    public function getFavoritesCount(int $userId) {
        return $this->where('user_id', $userId)->count();
    }

    /**
     * @param int $userId
     *
     * @return array
     */
    public function getAllFavoriteIds(int $userId): array
    {
        return $this
            ->where('user_id', $userId)
            ->get()
            ->pluck('user_favorite_id')
            ->toArray();
    }
}