<?php namespace App\Services;

use Carbon\Carbon;
use Illuminate\Events\Dispatcher;
use Laravel\Passport\Bridge\AccessToken;
use Laravel\Passport\Bridge\AccessTokenRepository;
use Laravel\Passport\Bridge\Client;
use Laravel\Passport\Bridge\Scope;
use Laravel\Passport\TokenRepository;
use League\OAuth2\Server\CryptKey;

class TokenService
{
    /**
     * @param $userId
     * @param int $clientId
     * @return string
     */
    public function generateTokenForUser($userId, $clientId = 2) {
        $token = new AccessToken($userId);
        $token->setIdentifier(bin2hex(random_bytes(40)));
        $token->setClient(new Client($clientId, null, null));
        $token->setExpiryDateTime(Carbon::now()->addHour());
        $token->addScope(new Scope('activity'));
        $privateKey = new CryptKey('file://'.storage_path('oauth-private.key'));

        $accessTokenRepository = new AccessTokenRepository(new TokenRepository, new Dispatcher);
        $accessTokenRepository->persistNewAccessToken($token);

        $jwtAccessToken = $token->convertToJWT($privateKey);

        return $jwtAccessToken;

//        $client = new Client($clientId, null, null);
//        $token = new AccessToken($userId, [
//            new Scope('activity'),
//        ], $client);
//        $token->setIdentifier(bin2hex(random_bytes(40)));
//        $token->setClient($client); // maybe can be removed, passed in construct
//        $token->setExpiryDateTime(new \DateTimeImmutable(now()->addHour()->format('Y-m-d H:i:s')));
//        $token->addScope(new Scope('activity')); // maybe can be removed, passed in construct
//        $privateKey = new CryptKey('file://'.storage_path('oauth-private.key')); // not using
//        $token->setPrivateKey($privateKey); // new, from passport version 9
//
//        $accessTokenRepository = new AccessTokenRepository(new TokenRepository, new Dispatcher);
//        $accessTokenRepository->persistNewAccessToken($token);
//
//        return $token->__toString();
    }
}