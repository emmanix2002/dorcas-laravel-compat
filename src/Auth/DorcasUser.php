<?php

namespace Hostville\Dorcas\LaravelCompat\Auth;


use Hostville\Dorcas\Sdk;
use Illuminate\Auth\GenericUser;

class DorcasUser extends GenericUser
{
    /** @var Sdk  */
    private $sdk;

    public function __construct(Sdk $sdk, array $attributes)
    {
        parent::__construct($attributes);
        $this->sdk = $sdk;
    }

    /**
     * Returns the Dorcas Sdk in use by the instance.
     *
     * @return Sdk
     */
    public function getDorcasSdk(): Sdk
    {
        return $this->sdk;
    }

    /**
     * Returns the company information, if available.
     *
     * @param bool $requestIfNotAvailable request the information from the API if it's not available
     *
     * @return array
     */
    public function company(bool $requestIfNotAvailable = true): array
    {
        if (!array_key_exists('company', $this->attributes) && $requestIfNotAvailable) {
            $service = $this->sdk->createProfileService();
            $response = $service->addQueryArgument('include', 'company')->send('get');
            # make a request to the API
            if (!$response->isSuccessful()) {
                return null;
            }
            $this->attributes = $response->getData();
        }
        return $this->attributes['company']['data'] ?? [];
    }
}