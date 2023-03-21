<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GalleryPhotoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'photo' => 'required|image|max:10240|dimensions:min_width=400,min_height=400|mimes:jpeg,jpg,gif,png'
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'file.max' => "Image size must be under 10Mb",
            'file.dimensions' => "Image dimension should be at least 400x400 pixels",
        ];
    }
}
