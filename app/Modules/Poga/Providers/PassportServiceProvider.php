<?php

namespace Raffles\Modules\Poga\Providers;

use League\OAuth2\Server\AuthorizationServer;

class PassportServiceProvider extends \Laravel\Passport\PassportServiceProvider
{

    /**
     * Make the authorization service instance.
     * This should keep in step with upgrades to the parent (yikes!):
     *
     * @link https://github.com/laravel/passport/blob/master/src/PassportServiceProvider.php#L196
     *
     * @return League\OAuth2\Server\AuthorizationServer
     */
    public function makeAuthorizationServer()
    {
        return new AuthorizationServer(
            $this->app->make(\Laravel\Passport\Bridge\ClientRepository::class),
            $this->app->make(\Raffles\Modules\Poga\Repositories\AccessTokenRepository::class),
            $this->app->make(\Laravel\Passport\Bridge\ScopeRepository::class),
            $this->makeCryptKey('private'),
            app('encrypter')->getKey()
        );
    }
}
