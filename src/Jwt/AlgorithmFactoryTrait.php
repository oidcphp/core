<?php

namespace OpenIDConnect\Jwt;

use Jose\Component\Signature\Algorithm\SignatureAlgorithm;
use OutOfRangeException;

trait AlgorithmFactoryTrait
{
    /**
     * @var SignatureAlgorithm[]
     */
    private $algorithmsInstance = [];

    /**
     * Create instance use alias
     *
     * @param string $alias
     * @return SignatureAlgorithm
     */
    public function createSignatureAlgorithm(string $alias): SignatureAlgorithm
    {
        static $aliases = [
            'ES256' => 'Jose\Component\Signature\Algorithm\ES256',
            'ES384' => 'Jose\Component\Signature\Algorithm\ES384',
            'ES512' => 'Jose\Component\Signature\Algorithm\ES512',
            'HS256' => 'Jose\Component\Signature\Algorithm\HS256',
            'HS384' => 'Jose\Component\Signature\Algorithm\HS384',
            'HS512' => 'Jose\Component\Signature\Algorithm\HS512',
            'PS256' => 'Jose\Component\Signature\Algorithm\PS256',
            'PS384' => 'Jose\Component\Signature\Algorithm\PS384',
            'PS512' => 'Jose\Component\Signature\Algorithm\PS512',
            'RS256' => 'Jose\Component\Signature\Algorithm\RS256',
            'RS384' => 'Jose\Component\Signature\Algorithm\RS384',
            'RS512' => 'Jose\Component\Signature\Algorithm\RS512',
            'none' => 'Jose\Component\Signature\Algorithm\None',
        ];

        if (!isset($aliases[$alias])) {
            throw new OutOfRangeException("Signature algorithm alias '$alias' is not found");
        }

        if (!isset($this->algorithmsInstance[$alias])) {
            $class = $aliases[$alias];
            $this->algorithmsInstance[$alias] = new $class;
        }

        return $this->algorithmsInstance[$alias];
    }

    /**
     * Create instances use aliases
     *
     * @param array $aliases
     * @return array
     */
    public function createSignatureAlgorithms(array $aliases): array
    {
        return array_map(function ($alias) {
            return $this->createSignatureAlgorithm($alias);
        }, $aliases);
    }
}
