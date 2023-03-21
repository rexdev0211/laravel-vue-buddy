<?php

namespace App\Http\Controllers\Web\Admin\Moderation;

use DB;
use App\Models\Words\WordsFilter;

class WordFilterController extends \App\Http\Controllers\Web\Controller
{
    /**
     * Get Words Filter page
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin.moderation.wordFilter', [
            'restricted' => WordsFilter::where('type', 'restricted')->pluck('phrase')->implode(PHP_EOL),
            'prohibited' => WordsFilter::where('type', 'prohibited')->pluck('phrase')->implode(PHP_EOL),
        ]);
    }

    /**
     * Get Words Filter page
     * @return \Illuminate\Routing\Redirector
     */
    public function save()
    {
        $this->validate(request(), [
            'type'  => 'required|in:restricted,prohibited',
            'words' => 'nullable|string',
        ]);

        $type  = request()->get('type');
        $words = request()->get('words');
        if (!$words) {
            WordsFilter::where('type', $type)->delete();
        } else {
            $now     = DB::raw('NOW()');
            $exists  = WordsFilter::where('type', $type)->pluck('id', 'phrase')->toArray();
            $phrases = explode(PHP_EOL, $words);

            $toDelete = [];
            $toInsert = [];
            $trimmed  = [];
            foreach ($phrases as $phrase) {
                $phrase    = trim($phrase);
                $trimmed[] = $phrase;

                if (!isset($exists[$phrase])) {
                    $toInsert[] = [
                        'type'       => $type,
                        'phrase'     => $phrase,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            foreach ($exists as $phrase => $id) {
                if (!in_array($phrase, $trimmed)) {
                    $toDelete[] = $id;
                }
            }

            $chunks = collect($toDelete)->chunk(6000);
            foreach ($chunks as $chunk) {
                WordsFilter::where('type', $type)->whereIn('id', $chunk->toArray())->delete();
            }

            if (count($toInsert)) {
                WordsFilter::insert($toInsert);
            }
        }

        return redirect(route('admin.moderation.wordFilter'))->with('successMessage', 'Changes successfully saved.');
    }
}
