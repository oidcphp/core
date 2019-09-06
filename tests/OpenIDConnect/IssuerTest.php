<?php

namespace Tests\OpenIDConnect;

use OpenIDConnect\Issuer;
use OpenIDConnect\Metadata\ProviderMetadata;
use Tests\TestCase;

class IssuerTest extends TestCase
{
    /**
     * OpenID Connect config from Google
     *
     * @see https://accounts.google.com/.well-known/openid-configuration
     */
    private const GOOGLE_OPENID_CONNECT_CONFIG = [
        'issuer' => 'https://accounts.google.com',
        'authorization_endpoint' => 'https://accounts.google.com/o/oauth2/v2/auth',
        'token_endpoint' => 'https://oauth2.googleapis.com/token',
        'userinfo_endpoint' => 'https://openidconnect.googleapis.com/v1/userinfo',
        'revocation_endpoint' => 'https://oauth2.googleapis.com/revoke',
        'jwks_uri' => 'https://www.googleapis.com/oauth2/v3/certs',
        'response_types_supported' => [
            'code',
            'token',
            'id_token',
            'code token',
            'code id_token',
            'token id_token',
            'code token id_token',
            'none',
        ],
        'subject_types_supported' => ['public'],
        'id_token_signing_alg_values_supported' => ['RS256'],
        'scopes_supported' => [
            'openid',
            'email',
            'profile',
        ],
        'token_endpoint_auth_methods_supported' => [
            'client_secret_post',
            'client_secret_basic',
        ],
        'claims_supported' => [
            'aud',
            'email',
            'email_verified',
            'exp',
            'family_name',
            'given_name',
            'iat',
            'iss',
            'locale',
            'name',
            'picture',
            'sub',
        ],
        'code_challenge_methods_supported' => [
            'plain',
            'S256',
        ],
    ];

    /**
     * @test
     */
    public function shouldProviderMetadataWhenDiscover(): void
    {
        $mockHttpClient = $this->createHttpClient([
            $this->createHttpJsonResponse(self::GOOGLE_OPENID_CONNECT_CONFIG),
            $this->createHttpJsonResponse(['keys' => []]),
        ]);

        /** @var ProviderMetadata $actual */
        $actual = Issuer::create('http://somewhere', 'whatever', $mockHttpClient)->discover();

        $this->assertSame('https://accounts.google.com', $actual->issuer());
        $this->assertSame('https://accounts.google.com/o/oauth2/v2/auth', $actual->authorizationEndpoint());
        $this->assertSame('https://oauth2.googleapis.com/token', $actual->tokenEndpoint());
        $this->assertSame('https://www.googleapis.com/oauth2/v3/certs', $actual->jwksUri());
        $this->assertSame([
            'code',
            'token',
            'id_token',
            'code token',
            'code id_token',
            'token id_token',
            'code token id_token',
            'none',
        ], $actual->responseTypesSupported());

        $this->assertSame(['public'], $actual->subjectTypesSupported());
        $this->assertSame(['RS256'], $actual->idTokenSigningAlgValuesSupported());
    }
}
