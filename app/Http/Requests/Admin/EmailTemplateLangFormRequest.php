<?php namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class EmailTemplateLangFormRequest extends FormRequest {

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
        $rules = [
            'subject' => 'required',
            'body' => 'required',
            'lang' => 'required',
        ];

        //edit
        if($this->id)
        {
        }
        //add
        else
        {
        }

        return $rules;
    }

}
