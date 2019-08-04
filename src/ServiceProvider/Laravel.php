<?php

namespace OpenIDConnect\ServiceProvider;

use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;
use League\OAuth2\Client\OptionProvider\HttpBasicAuthOptionProvider;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\GenericProvider;
use OpenIDConnect\ClientMetadata;
use OpenIDConnect\ProviderMetadata;

/**
 * The service provider written for Laravel
 *
 * @see https://laravel.com/docs/master/providers
 */
class Laravel extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AbstractProvider::class, GenericProvider::class);

        $this->app->singleton(GenericProvider::class, function () {
            $clientMetadata = $this->app->make(ClientMetadata::class);
            $providerMetadata = $this->app->make(ProviderMetadata::class);

            $collaborators = [
                'httpClient' => $this->app->make(Client::class),
                'optionProvider' => new HttpBasicAuthOptionProvider(),
            ];

            return new GenericProvider([
                'clientId' => $clientMetadata->id(),
                'clientSecret' => $clientMetadata->secret(),
                'redirectUri' => $clientMetadata->redirectUri(),
                'urlAuthorize' => $providerMetadata->authorizationEndpoint(),
                'urlAccessToken' => $providerMetadata->tokenEndpoint(),
                'urlResourceOwnerDetails' => $providerMetadata,
            ], $collaborators);
        });
    }
}
