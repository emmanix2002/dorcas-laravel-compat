<?php

namespace Hostville\Dorcas\LaravelCompat\Auth;


use Hostville\Dorcas\DorcasResponse;
use Hostville\Dorcas\Sdk;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class DorcasUserProvider implements UserProvider
{
    /** @var Sdk  */
    private $sdk;

    /** @var array */
    private $config;

    /**
     * DorcasUserProvider constructor.
     *
     * @param Sdk        $sdk
     * @param array|null $config
     */
    public function __construct(Sdk $sdk, array $config = null)
    {
        $this->sdk = $sdk;
        $this->config = $config ?: [];
    }

    /**
     * Returns the Dorcas SDK instance in use by the provider.
     *
     * @return Sdk
     */
    public function getSdk(): Sdk
    {
        return $this->sdk;
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed $identifier
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        $resource = $this->sdk->createUserResource($identifier);
        $response = $resource->relationships('company')->send('get');
        if (!$response->isSuccessful()) {
            return null;
        }
        return new DorcasUser($response->getData(), $this->sdk);
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param  mixed  $identifier
     * @param  string $token
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {
        $resource = $this->sdk->createUserResource($identifier);
        $response = $resource->relationships('company')
                                ->addQueryArgument('column', 'remember_token')
                                ->addQueryArgument('value', $token)
                                ->send('get');
        if (!$response->isSuccessful()) {
            return null;
        }
        return new DorcasUser($response->getData(), $this->sdk);
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  string                                     $token
     *
     * @return void
     */
    public function updateRememberToken(Authenticatable $user, $token)
    {
        $resource = $this->sdk->createUserResource($user->getAuthIdentifier());
        $resource->addBodyParam('token', $token)->send('put');
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array $credentials
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        $token = login_via_password($this->sdk, $credentials['email'] ?? '', $credentials['password'] ?? '');
        # we get the authentication token
        if ($token instanceof DorcasResponse) {
            return null;
        }
        Cache::put('dorcas.auth_token.'.$credentials['email'], $token, 120);
        $this->sdk->setAuthorizationToken($token);
        # set the authorization token
        $service = $this->sdk->createProfileService();
        $response = $service->addQueryArgument('include', 'company')->send('get');
        if (!$response->isSuccessful()) {
            return null;
        }
        return new DorcasUser($response->getData(), $this->sdk);
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  array                                      $credentials
     *
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        $plain = $credentials['password'];
        return Hash::check($plain, $user->getAuthPassword());
    }
}