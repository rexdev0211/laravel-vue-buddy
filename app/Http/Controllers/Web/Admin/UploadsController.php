<?php namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Web\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class UploadsController extends Controller
{
    private $dir = 'uploads/pages';

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    function index()
    {
        if (!file_exists($this->dir)) {
            mkdir($this->dir);
        }

        $filesDir = scandir($this->dir);

        unset($filesDir[0], $filesDir[1]);

        $files = [];
        foreach ($filesDir as $file) {
            $extension = explode('.', $file)[1];

            $files[] = [
                'name' => $file,
                'path' => '/'.$this->dir.'/' . $file,
                'image' => in_array($extension, ['jpg', 'jpeg', 'png', 'gif']) ? 1 : 0
            ];
        }

        return view('admin.uploads.index', compact('files'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    function save(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|file'
        ]);

        $file = $request->file('file');

        File::move($file, public_path('uploads/pages/') . $file->getClientOriginalName());

        return redirect()->route('admin.uploads');
    }

    /**
     * @param $name
     * @return mixed
     */
    function delete($name)
    {
        $name = str_replace('/', '', $name);

        $filePath = $this->dir . '/' . $name;

        if (!file_exists($filePath)) {
            dd("file doesn't exist");
        }

        unlink($filePath);

        return redirect()->route('admin.uploads');
    }
}