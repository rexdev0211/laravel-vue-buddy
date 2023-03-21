<?php namespace App\Repositories;

use App\Newsletter;
use App\User;

class NewsletterRepository extends BaseRepository
{
    public function __construct(Newsletter $model = null)
    {
        if (empty($model)){
            $model = new Newsletter();
        }
        parent::__construct($model);
    }

    /**
     * @param $email
     * @return mixed
     */
    public function getNewsletterByMail($email)
    {
        return $this->where('email', $email)->first();
    }

    /**
     * @param $email
     * @param $data
     * @return bool|\Illuminate\Database\Eloquent\Model
     */
//    public function updateNewsletterByEmail($email, $data) {
//        $newsletter = $this->getNewsletterByMail($email);
//
//        if (!is_null($newsletter)) {
//            return $this->update($newsletter->id, $data);
//        }
//
//        return false;
//    }

    /**
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createOrUpdateUserNewsletter(User $user, $fallbackEmail = false)
    {
        //first check by id
        $newsletter = $user->newsletter;

        //if user doesn't have an assigned newsletter, check maybe he was added before
        if (is_null($newsletter)) {
            //fallback is used when user updates his email
            if ($fallbackEmail) {
                $newsletter = $this->getNewsletterByMail($fallbackEmail);
            } else {
                $newsletter = $this->getNewsletterByMail($user->email);
            }
        }

        $newData = [
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'language' => $user->language,
            'subscribed' => $user->subscribed,
            'country_code' => $user->country_code
        ];

        try {
            if (is_null($newsletter)) {
                $newData['hash_key'] = str_random(40);

                return $this->create($newData);
            } else {
                return $this->update($newsletter->id, $newData);
            }
        } catch (\Exception $e) {
            //do nothing, it may fail when account tried to update email, but this email is already in use with
            //another account which was deleted previously, but his newsletter account remained active
        }
    }

    /**
     * @param $email
     * @param $name
     * @param string $language
     * @param string $subscribed
     * @return bool|\Illuminate\Database\Eloquent\Model
     */
    public function createOrUpdateOutsideNewsletter($email, $name, $language = null, $countryCode = null, $subscribed='yes')
    {
        $newsletter = $this->getNewsletterByMail($email);

        $newData = [
            'user_id' => null,
            'name' => $name,
            'email' => $email,
            'language' => $language,
            'subscribed' => $subscribed !== 'yes' && $subscribed !== 'no'
                ? 'yes'
                : $subscribed,
            'country_code' => $countryCode,
        ];

        if (is_null($newsletter)) {
            $newData['hash_key'] = str_random(40);

            return $this->create($newData);
        } else {
            if ($newsletter->user_id) {
                return false;
            }

            return $this->update($newsletter->id, $newData);
        }
    }

    /**
     * @param $newsletterId
     * @param $newsletterHashKey
     * @return bool
     */
    public function unsubscribeByIdAndUniqueKey($newsletterId, $newsletterHashKey)
    {
        $newsletter = $this->where('hash_key', $newsletterHashKey)->where('id', $newsletterId)->first();

        if (is_null($newsletter)) {
            return false;
        }

        $user = $newsletter->user;

        if (is_null($user)) {
            $user = User::where('email', $newsletter->email)->first();
        }

        if (is_null($user)) {
            $this->update($newsletter->id, [
                'user_id' => null,
                'subscribed' => 'no'
            ]);
        } else {
            User::where('id', $user->id)->update(['subscribed' => 'no']);

            $this->update($newsletter->id, [
                'user_id' => $user->id,
                'subscribed' => 'no'
            ]);
        }

        return true;
    }

    /**
     * @return Newsletter[]
     */
    public function getAllNewsletterUsers($filterCountry, $filterLanguage)
    {
        return $this
            ->filterBy('country_code', $filterCountry)
            ->filterBy('language', $filterLanguage)
            ->filterBy('subscribed', 'yes')
            ->where(function ($query) {
                $query->where('email_validation', '<>', 'bounce')
                    ->orWhereNull('email_validation');
            })
            ->get();
    }
}
