<?php
namespace App\Providers;

use Illuminate\Mail\MailServiceProvider as MailProvider;
use App\Mail\SparkPostEUTransportManager as TransportManager;

class MailServiceProvider extends MailProvider
{
    protected function registerSwiftTransport()
    {
        $this->app->singleton('swift.transport', function ($app) {
            return new TransportManager($app);
        });
    }
}