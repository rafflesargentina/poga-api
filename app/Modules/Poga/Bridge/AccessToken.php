<?php

namespace Raffles\Modules\Poga\Bridge;

use Raffles\Modules\Poga\Models\User;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use League\OAuth2\Server\CryptKey;

class AccessToken extends \Laravel\Passport\Bridge\AccessToken
{
    /**
     * Generate a JWT from the access token
     *
     * @param CryptKey $privateKey
     *
     * @return Token
     */
    public function convertToJWT(CryptKey $privateKey)
    {
        $builder = new Builder();
        $builder->setAudience($this->getClient()->getIdentifier())
            ->setId($this->getIdentifier(), true)
            ->setIssuedAt(time())
            ->setNotBefore(time())
            ->setExpiration($this->getExpiryDateTime()->getTimestamp())
            ->setSubject($this->getUserIdentifier())
            ->set('scopes', $this->getScopes())
            ->getToken();

        if ($user = User::find($this->getUserIdentifier())) {
            $fields = [
                'nombre' => $user->first_name ?: $user->idPersona->nombre,
            'apellido' => $user->last_name ?: $user->idPersona->apellido,
            'email' => $user->email,
            'ci' => $user->idPersona->ci,
            'ruc' => $user->idPersona->ruc,
            'telefono' => $user->idPersona->telefono,
            ];

            $builder->withClaim('user', $fields);
        }

        return $builder->getToken(new Sha256(), new Key($privateKey->getKeyPath(), $privateKey->getPassPhrase()));
    }
}
