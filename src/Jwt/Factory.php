<?php

declare(strict_types=1);

namespace OpenIDConnect\Jwt;

use Jose\Component\Core\JWK;
use Jose\Component\Core\Util\JsonConverter;
use Jose\Component\Signature\JWS;
use Jose\Component\Signature\Serializer\CompactSerializer;
use OpenIDConnect\Config;
use OpenIDConnect\Traits\ConfigAwareTrait;

class Factory
{
    use Concerns\WebToken;
    use ConfigAwareTrait;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param array $payload
     * @param array $header
     * @param int $jwk
     * @return JWS
     */
    public function createJws(array $payload = [], $header = [], int $jwk = 0): JWS
    {
        // Default use the RS256 alg
        if (empty($header)) {
            $header = ['alg' => 'RS256'];
        }

        if (!isset($header['alg'])) {
            $header['alg'] = 'RS256';
        }

        return $this->createJwsBuilder()
            ->withPayload(JsonConverter::encode($payload))
            ->addSignature(new JWK($this->config->jwkSet()->get($jwk)), $header)
            ->build();
    }

    public function createSerializeJws(array $payload = [], $header = [], int $jwk = 0): string
    {
        return (new CompactSerializer())->serialize(
            $this->createJws($payload, $header, $jwk)
        );
    }

    protected function resolveAlgorithms(): array
    {
        $providerMetadata = $this->config->providerMetadata();

        return array_unique(array_merge(
            $providerMetadata->require('id_token_signing_alg_values_supported'),
            $providerMetadata->get('id_token_encryption_alg_values_supported', []),
            $providerMetadata->get('id_token_encryption_enc_values_supported', [])
        ));
    }
}
