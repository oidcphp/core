<?php

declare(strict_types=1);

namespace Tests\Unit\Jwt\Verifiers;

use OpenIDConnect\Config;
use OpenIDConnect\Exceptions\RelyingPartyException;
use OpenIDConnect\Jwt\Factory as JwtFactory;
use OpenIDConnect\Jwt\Verifiers\LogoutTokenVerifier;
use stdClass;
use Tests\TestCase;

class LogoutTokenVerifierTest extends TestCase
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var LogoutTokenVerifier
     */
    private $target;

    protected function setUp(): void
    {
        $this->config = $this->createConfig([
            'issuer' => 'some-iss',
        ], [
            'client_id' => 'some-aud',
        ]);

        $this->target = new LogoutTokenVerifier($this->config, $this->createClock());
    }

    protected function tearDown(): void
    {
        $this->target = null;
    }

    public static function validPayload(): iterable
    {
        yield [
            [
                'iss' => 'some-iss',
                'aud' => 'some-aud',
                'iat' => time(),
                'sub' => 'some-sub',
                'events' => [
                    'http://schemas.openid.net/event/backchannel-logout' => new stdClass(),
                ],
            ],
        ];

        yield [
            [
                'iss' => 'some-iss',
                'aud' => 'some-aud',
                'iat' => time(),
                'sid' => 'some-sid',
                'events' => [
                    'http://schemas.openid.net/event/backchannel-logout' => new stdClass(),
                ],
            ],
        ];

        yield [
            [
                'iss' => 'some-iss',
                'aud' => 'some-aud',
                'iat' => time(),
                'sub' => 'some-sub',
                'sid' => 'some-sid',
                'events' => [
                    'http://schemas.openid.net/event/backchannel-logout' => new stdClass(),
                ],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider validPayload
     */
    public function shouldBeOkayWhenVerifyValidLogoutToken($validPayload): void
    {
        $this->expectNotToPerformAssertions();

        $factory = new JwtFactory($this->config);

        $this->target->verify(
            $factory->createSerializeJws($validPayload)
        );
    }

    public static function invalidPayload(): iterable
    {
        // Invalid iss
        yield [
            RelyingPartyException::class,
            [
                'iss' => 'invalid-iss',
                'aud' => 'some-aud',
                'iat' => time(),
                'sub' => 'some-sub',
                'events' => [
                    'http://schemas.openid.net/event/backchannel-logout' => new stdClass(),
                ],
            ],
        ];

        // Invalid aud
        yield [
            RelyingPartyException::class,
            [
                'iss' => 'some-iss',
                'aud' => 'invalid-aud',
                'iat' => time(),
                'sub' => 'some-sub',
                'events' => [
                    'http://schemas.openid.net/event/backchannel-logout' => new stdClass(),
                ],
            ],
        ];

        // Does not contain 'events'
        yield [
            RelyingPartyException::class,
            [
                'iss' => 'some-iss',
                'aud' => 'some-aud',
                'iat' => time(),
                'sub' => 'some-sub',
            ],
        ];

        // Does not contain sub and sid
        yield [
            RelyingPartyException::class,
            [
                'iss' => 'some-iss',
                'aud' => 'some-aud',
                'iat' => time(),
                'events' => [
                    'http://schemas.openid.net/event/backchannel-logout' => new stdClass(),
                ],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider invalidPayload
     */
    public function shouldThrowExceptionWhenIssuer($expectedException, $invalidPayload): void
    {
        $this->expectException($expectedException);

        $factory = new JwtFactory($this->config);

        $this->target->verify(
            $factory->createSerializeJws($invalidPayload)
        );
    }
}
