<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    'cloud' => env('FILESYSTEM_CLOUD', 's3'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "s3", "rackspace"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_KEY'),
            'secret' => env('AWS_SECRET'),
            'region' => env('AWS_REGION'),
            'bucket' => env('AWS_BUCKET'),
        ],

        'video' => [
            // Do everything at the single server (uploading, conversion, deletion)
            'single_server_mode' => true,

            // Public video storage
            // public/storage dir should be symlinked to storage/app/public/instance_[n] at the video server
            'public' => [
                'instance_1' => [
                    'driver' => 'local',
                    'root' => env('VIDEO_SERVER_1_PUBLIC_ROOT', public_path('uploads')),
                    'url' => env('VIDEO_SERVER_1_URL', env('APP_URL') . '/uploads'),
                    'visibility' => 'public',
                ],
                /*'instance_2' => [
                    'driver' => 'local',
                    'root' => env('VIDEO_SERVER_2_PUBLIC_ROOT', public_path('uploads')),
                    'url' => env('VIDEO_SERVER_2_URL', env('APP_URL') . '/uploads'),
                    'visibility' => 'public',
                ],*/
            ],

            // Source video storage
            'source' => [
                'instance_1' => [
                    'driver' => 'local',
                    'root' => env('VIDEO_SERVER_1_UPLOAD_ROOT', storage_path('app/upload')),
                ],
                /*'instance_2' => [
                    'driver' => 'local',
                    'root' => env('VIDEO_SERVER_2_UPLOAD_ROOT', storage_path('app/upload')),
                ],*/
            ]
        ],

        'ovh' => [
            /*
            buddymedia
            container URL:
            https://storage.gra.cloud.ovh.net/v1/AUTH_9402ced70a28463aa9d9b444acd1aa00/buddymedia

            maybe this guide will help:
            https://docs.ovh.com/us/en/storage/

            I'm supposed to add this cname/txt record value:
            buddymedia.auth-9402ced70a28463aa9d9b444acd1aa00.storage.gra.cloud.ovh.net
            */

            'driver' => 'ovh',
            'authUrl' => env('OS_AUTH_URL', 'https://auth.cloud.ovh.net/v3/'),
            'projectId' => env('OS_PROJECT_ID'),
            'region' => env('OS_REGION_NAME'),
            'userDomain' => env('OS_USER_DOMAIN_NAME', 'Default'),
            'username' => env('OS_USERNAME'),
            'password' => env('OS_PASSWORD'),
            'containerName' => env('OS_CONTAINER_NAME'),

            // Since v1.2
            // Optional variable and only if you are using temporary signed urls.
            // You can also set a new key using the command 'php artisan ovh:set-temp-url-key'.
            'tempUrlKey' => env('OS_TEMP_URL_KEY'),

            // Since v2.1
            // Optional variable and only if you have setup a custom endpoint.
            'endpoint' => env('OS_CUSTOM_ENDPOINT'),

            // Optional variables for handling large objects.
            // Defaults below are 300MB threshold & 100MB segments.
            'swiftLargeObjectThreshold' => env('OS_LARGE_OBJECT_THRESHOLD', 300 * 1024 * 1024),
            'swiftSegmentSize' => env('OS_SEGMENT_SIZE', 100 * 1024 * 1024),
            'swiftSegmentContainer' => env('OS_SEGMENT_CONTAINER', null),

            // Optional variable and only if you would like to DELETE all uploaded object by DEFAULT.
            // This allows you to set an 'expiration' time for every new uploaded object to
            // your container. This will not affect objects already in your container.
            //
            // If you're not willing to DELETE uploaded objects by DEFAULT, leave it empty.
            // Really, if you don't know what you're doing, you should leave this empty as well.
            'deleteAfter' => env('OS_DEFAULT_DELETE_AFTER', null),

            // Optional variable to cache your storage objects in memory
            // You must require league/flysystem-cached-adapter to enable caching
            'cache' => true, // Defaults to false

            // Optional variable to set a prefix on all paths
            'prefix' => null,
        ],
    ],
];
