<?php namespace App\Models\Rush;

use App\Services\MediaService;
use App\UserPhoto;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Eloquent\Model;

class RushMedia extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rushes_medias';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'path',
        'extension',
        'status',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get Strips attached to this media as Image
     * @return [type] [description]
     */
    public function image_strips()
    {
        return $this->hasMany(RushStrip::class, 'image_id');
    }

    /**
     * Try to upload media file
     *
     * @return RushMedia
     */
    public function tryUpload($file)
    {
        //upload photo on server
        $photoName = (new MediaService())->uploadPhoto($file, 'rush');

        $this->path      = $photoName;
        $this->extension = '';
        $this->status    = 'processed';

        return $this;
    }

    /**
     * Format data for view
     * @return array
     */
    public function formatForView()
    {
        return [
            'id'    => $this->id,
            'image' => $this->path,
            'small' => UserPhoto::getPhotoUrl($this->path, '180x180', 'rush'),
            'orig'  => UserPhoto::getPhotoUrl($this->path, 'orig', 'rush'),
        ];
    }

    /**
     * Delete media file
     *
     * @return RushMedia
     */
    public function deleteImage()
    {
        $publicPath = public_path('uploads/rush');
        $types = ['_180x180', '_orig'];

        foreach ($types as $type) {
            $imagePath = $this->path . $type . '.jpg';
            $fullPath = $publicPath . '/' . $imagePath;
            if (File::exists($fullPath)) {
                File::delete($fullPath);
            }
        }

        return $this;
    }
}
