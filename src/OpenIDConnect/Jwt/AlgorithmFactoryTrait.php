<?php

namespace OpenIDConnect\Jwt;

use Jose\Component\Core\Algorithm;
use OutOfRangeException;

trait AlgorithmFactoryTrait
{
    /**
     * @var array
     */
    private $algorithmsInstance = [];

    /**
     * Create instance use alias
     *
     * @param string $alias
     * @return Algorithm
     */
    public function createAlgorithm(string $alias): Algorithm
    {
        static $aliases = [
            // See https://tools.ietf.org/html/rfc7518#section-3.1
            'ES256' => 'Jose\\Component\\Signature\\Algorithm\\ES256',
            'ES384' => 'Jose\\Component\\Signature\\Algorithm\\ES384',
            'ES512' => 'Jose\\Component\\Signature\\Algorithm\\ES512',
            'HS256' => 'Jose\\Component\\Signature\\Algorithm\\HS256',
            'HS384' => 'Jose\\Component\\Signature\\Algorithm\\HS384',
            'HS512' => 'Jose\\Component\\Signature\\Algorithm\\HS512',
            'PS256' => 'Jose\\Component\\Signature\\Algorithm\\PS256',
            'PS384' => 'Jose\\Component\\Signature\\Algorithm\\PS384',
            'PS512' => 'Jose\\Component\\Signature\\Algorithm\\PS512',
            'RS256' => 'Jose\\Component\\Signature\\Algorithm\\RS256',
            'RS384' => 'Jose\\Component\\Signature\\Algorithm\\RS384',
            'RS512' => 'Jose\\Component\\Signature\\Algorithm\\RS512',
            'none' => 'Jose\\Component\\Signature\\Algorithm\\None',

            // See https://tools.ietf.org/html/rfc7518#section-4.1
            'RSA1_5' => 'Jose\\Component\\Encryption\\Algorithm\\KeyEncryption\\RSA15',
            'RSA-OAEP' => 'Jose\\Component\\Encryption\\Algorithm\\KeyEncryption\\RSAOAEP',
            'RSA-OAEP-256' => 'Jose\\Component\\Encryption\\Algorithm\\KeyEncryption\\RSAOAEP256',
            'A128KW' => 'Jose\\Component\\Encryption\\Algorithm\\KeyEncryption\\A128KW',
            'A192KW' => 'Jose\\Component\\Encryption\\Algorithm\\KeyEncryption\\A192KW',
            'A256KW' => 'Jose\\Component\\Encryption\\Algorithm\\KeyEncryption\\A256KW',
            'ECDH-ES' => 'Jose\\Component\\Encryption\\Algorithm\\KeyEncryption\\ECDHES',
            'ECDH-ES+A128KW' => 'Jose\\Component\\Encryption\\Algorithm\\KeyEncryption\\ECDHESA128KW',
            'ECDH-ES+A192KW' => 'Jose\\Component\\Encryption\\Algorithm\\KeyEncryption\\ECDHESA192KW',
            'ECDH-ES+A256KW' => 'Jose\\Component\\Encryption\\Algorithm\\KeyEncryption\\ECDHESA256KW',
            'A128GCMKW' => 'Jose\\Component\\Encryption\\Algorithm\\KeyEncryption\\A128GCMKW',
            'A192GCMKW' => 'Jose\\Component\\Encryption\\Algorithm\\KeyEncryption\\A192GCMKW',
            'A256GCMKW' => 'Jose\\Component\\Encryption\\Algorithm\\KeyEncryption\\A256GCMKW',
            'PBES2-HS256+A128KW' => 'Jose\\Component\\Encryption\\Algorithm\\KeyEncryption\\PBES2HS256A128KW',
            'PBES2-HS384+A192KW' => 'Jose\\Component\\Encryption\\Algorithm\\KeyEncryption\\PBES2HS384A192KW',
            'PBES2-HS512+A256KW' => 'Jose\\Component\\Encryption\\Algorithm\\KeyEncryption\\PBES2HS512A256KW',

            // See https://tools.ietf.org/html/rfc7518#section-5.1
            'A128CBC-HS256' => 'Jose\\Component\\Encryption\\Algorithm\\ContentEncryption\\A128CBCHS256',
            'A192CBC-HS384' => 'Jose\\Component\\Encryption\\Algorithm\\ContentEncryption\\A192CBCHS384',
            'A256CBC-HS512' => 'Jose\\Component\\Encryption\\Algorithm\\ContentEncryption\\A256CBCHS512',
            'A128GCM' => 'Jose\\Component\\Encryption\\Algorithm\\ContentEncryption\\A128GCM',
            'A192GCM' => 'Jose\\Component\\Encryption\\Algorithm\\ContentEncryption\\A192GCM',
            'A256GCM' => 'Jose\\Component\\Encryption\\Algorithm\\ContentEncryption\\A256GCM',
        ];

        if (!isset($aliases[$alias])) {
            throw new OutOfRangeException("Algorithm alias '$alias' is not found");
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
    public function createAlgorithms(array $aliases): array
    {
        return array_map(function ($alias) {
            return $this->createAlgorithm($alias);
        }, $aliases);
    }
}
