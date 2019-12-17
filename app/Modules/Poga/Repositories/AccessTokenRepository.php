<?php

namespace Raffles\Modules\Poga\Repositories;

use Raffles\Modules\Poga\Bridge\AccessToken;

use DateTime;
use Illuminate\Contracts\Events\Dispatcher;
use Laravel\Passport\Events\AccessTokenCreated;
use Laravel\Passport\TokenRepository;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;

class AccessTokenRepository extends \Laravel\Passport\Bridge\AccessTokenRepository
{
    /**
     * {@inheritdoc}
     */
    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null)
    {
        return new AccessToken($userIdentifier, $scopes);
    }
}
