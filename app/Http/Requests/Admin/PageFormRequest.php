<?php namespace App\Http\Requests\Admin;

use App\Page;
use App\Repositories\PageRepository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PageFormRequest extends FormRequest {

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
    public function rules(PageRepository $pageRepository)
    {
        $developerUsedPagesUrls = implode(',', Page::$developerUsedPagesUrls);

        $rules = [
            'title' => 'required',
//            'url' => 'required|not_in:'.$developerUsedPagesUrls.'|regex:/^[\w\-]+\.html$/|unique:pages,url',
//            'url' => 'required|not_in:'.$developerUsedPagesUrls.'|unique:pages,url',
//            'lang' => 'required|in:de,en',
//            'lang' => 'required',
            'lang' => 'required|regex:/^[a-z]{2}$/i',
            'url' => [
                'required',
                'not_in:'.$developerUsedPagesUrls,
            ],
            'content' => 'required',
//            'meta_keywords' => 'required',
//            'meta_description' => 'required',
        ];

        $urlUniqueRule =
                Rule::unique('pages')->where(function ($query) {
                    $query->where('lang', $this->lang);
                });

        //edit
        if($this->id)
        {
//            $rules['url'] .= ',' . $this->id;

//            $rules['url'] = [
//                'required',
//                'not_in:'.$developerUsedPagesUrls,
//                Rule::unique('pages')->ignore($this->id)->where(function ($query) {
//                    $query->where('lang', $this->lang);
//                })
//            ];

            $rules['url'][] = $urlUniqueRule->ignore($this->id);
        }
        //add
        else
        {
//            $rules['url'] = [
//                'required',
//                'not_in:'.$developerUsedPagesUrls,
//                Rule::unique('pages')->where(function ($query) {
//                    $query->where('lang', $this->lang);
//                })
//            ];

            $rules['url'][] = $urlUniqueRule;
        }

        //check existing pages if it's a required page - do not change it's url
        if($this->id)
        {
            $page = $pageRepository->find($this->id);

            if($page->is_required == 'yes')
            {
                unset($rules['url']);
            }
        }

        return $rules;
    }

    public function messages() {
        return [
            'url.regex' => 'The url extension must be .html'
        ];
    }

}
