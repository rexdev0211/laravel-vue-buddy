<?php

/**
    NOTES:
    - API versions should be registered here in descending order of version
    - New versions should be registered in config/apiVersioning.php in descending order, too
    - Fallback to previous API versions is enabled
 */

//Route::prefix('v2.1')->group(base_path('routes/mobile/versions/v2_1.php'));

//Route::prefix('v2')->group(base_path('routes/mobile/versions/v2.php'));

Route::prefix('v1.1')->group(base_path('routes/mobile/versions/v1.php'));
//Route::prefix('v1')->group(base_path('routes/mobile/versions/v1.php'));