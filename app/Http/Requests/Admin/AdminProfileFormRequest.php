<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminProfileFormRequest extends FormRequest
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
        $id = \Auth::getUser()->id;

        $rules = [
            'email' => 'required|email|unique:users,email,'.$id,
            'name' => 'required',
        ];

        if($this->password)
        {
            $rules['password'] = 'required|min:6';
            $rules['password2'] = 'required|same:password';
        }

        return $rules;
    }
}
