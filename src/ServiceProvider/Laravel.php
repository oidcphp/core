<?php

namespace OpenIDConnect\ServiceProvider;

use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;
use League\OAuth2\Client\OptionProvider\HttpBasicAuthOptionProvider;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\GenericProvider;
use OpenIDConnect\Client as OpenIDConnectClient;
use OpenIDConnect\Metadata\ClientMetadata;
use OpenIDConnect\Metadata\ProviderMetadata;

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

        $this->app->singleton(OpenIDConnectClient::class, function () {
            $clientMetadata = $this->app->make(ClientMetadata::class);
            $providerMetadata = $this->app->make(ProviderMetadata::class);

            $collaborators = [
                'httpClient' => $this->app->make(Client::class),
                'optionProvider' => new HttpBasicAuthOptionProvider(),
            ];

            return new OpenIDConnectClient($providerMetadata, $clientMetadata, $collaborators);
        });
    }
}
