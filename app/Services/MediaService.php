<?php namespace App\Services;

use App\User;
use App\UserPhoto;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Facades\Image;

class MediaService
{
    /**
     * @param $photoNameWithSubdirs - a/ab/absdfsfsfsdfs
     * @param $mainSubdirectory - users | planes
     */
    public function deletePhotosByExpression($photoNameWithSubdirs, $mainSubdirectory)
    {
        $files = $this->getFilesByExpression($photoNameWithSubdirs, $mainSubdirectory);
        foreach ($files as $file) {
            @unlink($file);
        }
    }

    /**
     * @param $photoNameWithSubdirs
     */
    public function deleteUserPhoto($photoNameWithSubdirs)
    {
        $this->deletePhotosByExpression($photoNameWithSubdirs, 'users');
    }

    /**
     * @param $photoNameWithSubdirs
     */
    public function addSuffixToPhotoName($photoNameWithSubdirs, $mainSubdirectory, $suffix)
    {
        $files = $this->getFilesByExpression($photoNameWithSubdirs, $mainSubdirectory);
        foreach ($files as $file) {
            $fileArr = explode('_', $file);
            $fileArr[0] .= $suffix;
            $newFile = implode('_', $fileArr);
            $command = "mv $file $newFile";
            exec($command);
        }
    }

    public function getFilesByExpression($photoNameWithSubdirs, $mainSubdirectory)
    {
        $photoArray = explode('/', $photoNameWithSubdirs);
        $photoBaseName = array_pop($photoArray);
        $secondSubdirectory = implode('/', $photoArray);

        $searchDir = 'uploads/' . $mainSubdirectory . '/' . $secondSubdirectory;
        $command = ' find ' . $searchDir . ' -name "' . $photoBaseName . '_*" -or -name "' . $photoBaseName . '.*" ';

        exec($command, $files);

        return $files;
    }

    /**
     * @param UploadedFile $tempFile
     * @return bool
     */
    public function uploadUserPhoto(UploadedFile $tempFile)
    {
        return $this->uploadPhoto($tempFile, 'users');
    }

    public function uploadAdminPhoto(UploadedFile $tempFile, $userId)
    {
        return $this->uploadUniquePhoto($tempFile, 'admins', $userId);
    }

    /**
     * @param User $user
     * @param $tempFile
     * @param bool $isDefault
     * @param int $rotation
     * @param array $crop
     *
     * @return UserPhoto
     * @throws \Exception
     */
    public function uploadGalleryPhoto(User $user, $tempFile, $isDefault = false, int $rotation = 0, array $crop = []): UserPhoto
    {
        //upload photo on server
        $photoName = $this->uploadPhoto($tempFile, 'users', $rotation, $crop);

        //insert photo in db
        $userPhotoRepository = app('App\Repositories\PhotoRepository');

        /** @var UserPhoto $userPhoto */
        $userPhoto = $userPhotoRepository->createUserPhoto([
            'user_id' => $user->id,
            'photo' => $photoName,
            'is_default' => $isDefault ? 'yes' : 'no',
            'visible_to' => $isDefault ? 'public' : 'private',
            'slot' => $isDefault ? 'clear' : null
        ]);

        if ($isDefault) {
            $userPhoto->updateNudityRating();
        }

        return $userPhoto;
    }

    public function uploadUniquePhoto(UploadedFile $tempFile, $subdir, $itemId = false)
    {
        //if we already have a set itemId, like user id for example, then use it even if directory exists already
        if ($itemId) {
            $destinationPath = 'uploads/' . $subdir . '/' . substr($itemId, 0, 1) . '/' . substr($itemId, 0, 2) . '/';
            $photoFull = $destinationPath . $itemId . '_orig.jpg';
        } //otherwise generate a unique item id
        else {
            do {
                $itemId = str_random(40);
                $destinationPath = 'uploads/' . $subdir . '/' . substr($itemId, 0, 1) . '/' . substr($itemId, 0, 2) . '/';
                $photoFull = $destinationPath . $itemId . '_orig.jpg';
            } while (\File::exists($photoFull));
        }

        if (!\File::exists($destinationPath)) {
            \File::makeDirectory($destinationPath, 0755, true, false);
        }

        $thumb = \PhpThumbFactory::create($tempFile->getRealPath());

        //original photo
        $thumb->resize(1200, 1200);
        $thumb->setOptions(['jpegQuality' => 85]);
        $thumb->save($photoFull, 'jpg');

        return $itemId;
    }

    /**
     * @param $base64String
     * @return string
     */
    public function extractBase64ImageData($base64String)
    {
        list($type, $data) = explode(';', $base64String);
        list(, $data) = explode(',', $data);
        return base64_decode($data);
    }

    /**
     * @param UploadedFile $tempFile
     * @param string $subdir
     * @param int $rotation
     * @param array $crop
     *
     * @return string
     */
    public function uploadPhoto(UploadedFile $tempFile, string $subdir, int $rotation = 0, array $crop = []): string
    {
        do {
            $fileName = str_random(40);
            $dir1 = 'uploads/' . $subdir . '/';
            $dir2 = substr($fileName, 0, 1) . '/' . substr($fileName, 0, 2) . '/';
            $photoFull = $dir1 . $dir2 . $fileName . '_orig.jpg';
        } while (\File::exists($photoFull));

        if (!\File::exists($dir1 . $dir2)) {
            \File::makeDirectory($dir1 . $dir2, 0755, true, false);
        }

        if (is_string($tempFile)) {
            File::put(public_path($photoFull), $tempFile);
            $imagePath = public_path($photoFull);
        } else {
            $imagePath = $tempFile; //UploadedFile
        }

        $img = Image::make($imagePath)->orientate();
        if ($rotation) {
            $img->rotate($rotation);
        }

        if ($crop && $crop['width']) {
            $img->crop((int)$crop['width'], (int)$crop['height'], (int)$crop['x'], (int)$crop['y']);
        }

        $img->resize(1200, 1200, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        $img->save($photoFull, 85);

        return $dir2 . $fileName;
    }

    /**
     * @param User $user
     * @param int $photoId
     * @param UploadedFile $tempFile
     * @param int $rotation
     * @param array $crop
     *
     * @return UserPhoto
     * @throws \Exception
     */
    public function changeGalleryPhoto(User $user, int $photoId, UploadedFile $tempFile, int $rotation = 0, array $crop = []): UserPhoto
    {
        //make required checks
        $userPhotoRepository = app('App\Repositories\PhotoRepository');
        $oldPhoto = $userPhotoRepository->findUserPhoto($user->id, $photoId);
        if (is_null($oldPhoto)) {
            throw new \Exception('Photo not found');
        }

        //upload new user photo
        $photoName = $this->uploadPhoto($tempFile, 'users', $rotation, $crop);

        //update new name for photo db
        $userPhoto = $userPhotoRepository->updatePhoto($photoId, [
            'photo' => $photoName,
        ]);

        return $userPhoto;
    }
}