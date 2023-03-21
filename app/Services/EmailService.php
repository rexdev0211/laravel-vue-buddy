<?php namespace App\Services;

use App\Mail\EmailTemplateBuilder;
use App\Repositories\EmailTemplateLangRepository;
use App\Repositories\EmailTemplateRepository;
use Illuminate\Support\Facades\Mail;
use SparkPost\SparkPost;
use GuzzleHttp\Client;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;

class EmailService
{
    public function __construct(EmailTemplateRepository $emailTemplateRepository, EmailTemplateLangRepository $emailTemplateLangRepository)
    {
        $this->emailTemplateRepository = $emailTemplateRepository;
        $this->emailTemplateLangRepository = $emailTemplateLangRepository;
    }

    /**
     * @param $email
     * @param $name
     * @param $subject
     * @param $body
     * @return bool
     */
    public static function sendMailStatic($email, $name, $subject, $body) {
        Mail::to([['email'=>$email, 'name'=>$name]])->send(new EmailTemplateBuilder($subject, $body));

        return true;
    }

    /**
     * @param $email
     * @param $name
     * @param $subject
     * @param $body
     * @return bool
     */
    public function sendMail($email, $name, $subject, $body) {
        Mail::to([['email'=>$email, 'name'=>$name]])->send(new EmailTemplateBuilder($subject, $body));

        return true;
    }

    /**
     * @param $email
     * @param $password
     * @param $fullName
     * @param string $language
     * @return bool
     */
    public function sendUserRegistrationEmail($email, $password, $fullName, $language = 'en') {
        $template = $this->emailTemplateRepository->findByName('user_registration');
        if (empty($template)) {
            return false;
        }

        $templateLang = $this->emailTemplateLangRepository->findByEmailTemplate($template->id, $language);

        if (is_null($templateLang) || !$templateLang->subject || !$templateLang->body) {
            $templateLang = $this->emailTemplateLangRepository->findByEmailTemplate($template->id, 'en');

            if (is_null($templateLang)) {
                \Log::error("user registration template for lang: $language (and en) is missing");

                return false;
            }
        }

        $templateVars = ['{EMAIL}', '{PASSWORD}', '{FULL_NAME}'];
        $templateValues = [$email, $password, $fullName];

        $subject = str_replace($templateVars, $templateValues, $templateLang['subject']);
        $body = str_replace($templateVars, $templateValues, $templateLang['body']);

        Mail::to([['email'=>$email, 'name'=>$fullName]])->send(new EmailTemplateBuilder($subject, $body));

        return true;
    }

    /**
     * @param $user
     * @param $resetUrl
     * @param string $language
     * @return bool
     */
    public function sendForgotPasswordEmail($user, $resetUrl, $language = 'en')
    {
        $template = $this->emailTemplateRepository->findByName('forgot_password');

        $templateLang = $this->emailTemplateLangRepository->findByEmailTemplate($template->id, $language);

        if (is_null($templateLang) || !$templateLang->subject || !$templateLang->body) {
            $templateLang = $this->emailTemplateLangRepository->findByEmailTemplate($template->id, 'en');

            if (is_null($templateLang)) {
                \Log::error("forgot password template for lang: $language (and en) is missing");

                return false;
            }
        }

        $resetLink = '<a href="'.$resetUrl.'">'.$resetUrl.'</a>';

        $templateVars = ['{FULL_NAME}', '{RESET_LINK}'];
        $templateValues = [$user['name'], $resetLink];

        $subject = str_replace($templateVars, $templateValues, $templateLang['subject']);
        $body = str_replace($templateVars, $templateValues, $templateLang['body']);

        Mail::to($user)->send(new EmailTemplateBuilder($subject, $body));

        return true;
    }

    /**
     * @param $email
     * @param $fullName
     * @param $messagesCount
     * @param string $language
     * @param $templateName - daily_reminders|weekly_reminders|monthly_reminders
     * @return bool
     */
    public function sendIntervalNotificationsStatistics($email, $fullName, $messagesCount, $language = 'en', $templateName) {
        $template = $this->emailTemplateRepository->findByName($templateName);

        $templateLang = $this->emailTemplateLangRepository->findByEmailTemplate($template->id, $language);

        if (is_null($templateLang) || !$templateLang->subject || !$templateLang->body) {
            $templateLang = $this->emailTemplateLangRepository->findByEmailTemplate($template->id, 'en');

            if (is_null($templateLang)) {
                \Log::error("notification template $templateName for lang: $language (and en) is missing");

                return false;
            }
        }

        $templateVars = ['{EMAIL}', '{FULL_NAME}', '{MESSAGES_COUNT}'];
        $templateValues = [$email, $fullName, $messagesCount];

        $subject = str_replace($templateVars, $templateValues, $templateLang['subject']);
        $body = str_replace($templateVars, $templateValues, $templateLang['body']);

        Mail::to([['email'=>$email, 'name'=>$fullName]])->send(new EmailTemplateBuilder($subject, $body));

        return true;
    }

    /**
     * @param $subject
     * @param $body
     * @param $recipientsArray - must have format
            [
                [
                    'address' => [
                        'name' => 'TO_NAME',
                        'email' => 'TO_EMAIL',
                    ],
                    'substitution_data' => [
                        'var1' => 'VALUE1'
                    ]
                ]
                [...]
            ]
     * @return array
     */
    public function sendSparkpostMassMail($subject, $body, $recipientsArray) {
        $sparkPostApiKey = config('const.SPARKPOST_EU_SECRET');

        $fromName = config('const.MAIL_FROM_NAME');

        $fromEmail = config('const.MAIL_FROM_ADDRESS');

        //basic check that recipients array format is correct
        if (!count($recipientsArray) || empty($recipientsArray[0]['address']['name']) || empty($recipientsArray[0]['address']['email']) || !count($recipientsArray[0]['substitution_data'])) {
            $error = 'Recipients or required values not defined for bulk mail';

            \Log::error($error . print_r($recipientsArray, 1));

            return [
                'status' => 'error',
                'code' => '0',
                'message' => 'Recipients or required values not defined for bulk mail'
            ];
        }

        $httpClient = new GuzzleAdapter(new Client());

        $sparky = new SparkPost($httpClient, [
            "key" => $sparkPostApiKey,
            'host' => 'api.eu.sparkpost.com'
        ]);

        $transmissionData = [
            'content' => [
                'from' => [
                    'name' => $fromName,
                    'email' => $fromEmail,
                ],
                'subject' => $subject,
//                'html' => '<html><body><h1>Congratulations, {{name}}!</h1><p>You just sent your very first mailing!</p></body></html>',
                'html' => $body,
//                'text' => 'Congratulations, {{name}}! You just sent your very first mailing!',
            ],
//            'substitution_data' => ['name' => 'YOUR_FIRST_NAME'],
//            'recipients' => [
//                [
//                    'address' => [
//                        'name' => 'YOUR_NAME',
//                        'email' => 'YOUR_EMAIL',
//                    ],
//                    'substitution_data' => [
//                        'name' => 'YONAME'
//                    ]
//                ],
//            ],
            'recipients' => $recipientsArray
        ];

        //verification that I won't send accidentally mass mail from localhost
        if (\App::environment() == 'local' && count($recipientsArray) > 0) {
            \Log::info(print_r($transmissionData,1));

            return [
                'status' => 'ok',
                'code' => '200',
                'message' => ' (just logged) '
            ];
        }

        $promise = $sparky->transmissions->post($transmissionData);

        try {
            $response = $promise->wait();

//            echo $response->getStatusCode()."\n";
//            print_r($response->getBody())."\n";

//            \Log::info($response->getStatusCode());
//            \Log::info(print_r($response->getBody(), 1));

            $message = isset($response->getBody()['results']['total_accepted_recipients']) ? $response->getBody()['results']['total_accepted_recipients'] : '';

            return [
                'status' => 'ok',
                'code' => $response->getStatusCode(),
                'message' => $message
            ];
        } catch (\Exception $e) {
//            echo $e->getCode()."\n";
//            echo $e->getMessage()."\n";

            \Log::error($e->getCode());
            \Log::error($e->getMessage());

            return [
                'status' => 'error',
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            ];
        }
    }
}
