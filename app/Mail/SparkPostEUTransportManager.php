<?php

namespace App\Mail;

use Illuminate\Mail\TransportManager;
use Illuminate\Support\Arr;

class SparkPostEUTransportManager extends TransportManager {

    /**
     * @return SparkPostEUTransport
     */
    protected function createSparkPostEUDriver()
    {
        $config = $this->app['config']->get('services.sparkposteu', []);

        return new SparkPostEUTransport(
            $this->guzzle($config), $config['secret'], Arr::get($config, 'options', [])
        );
    }
}