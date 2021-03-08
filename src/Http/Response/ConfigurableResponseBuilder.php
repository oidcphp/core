<?php

namespace OpenIDConnect\Http\Response;

use MilesChou\Psr\Http\Client\HttpClientInterface;
use OpenIDConnect\Config;
use OpenIDConnect\Contracts\ResponseBuilder;
use OpenIDConnect\Traits\Psr7ResponseBuilder;
use Psr\Http\Message\ResponseInterface;

class ConfigurableResponseBuilder implements ResponseBuilder
{
    use Psr7ResponseBuilder;

    protected const METHOD_REDIRECT = 'redirect';
    protected const METHOD_FORM = 'form';

    /**
     * @var string
     */
    protected $method;

    /**
     * @var string
     */
    protected $key;

    /**
     * @param Config $config
     * @param HttpClientInterface $httpClient
     */
    public function __construct(Config $config, HttpClientInterface $httpClient)
    {
        $this->setConfig($config);
        $this->setHttpClient($httpClient);
    }

    public function build(array $parameters): ResponseInterface
    {
        switch ($this->method) {
            case self::METHOD_REDIRECT:
                return $this->httpClient->createResponse(302)
                    ->withHeader(
                        'Location',
                        (string)$this->generateRedirectUriWithProviderConfig($this->key, $parameters)
                    );
            case self::METHOD_FORM:
                return $this->httpClient->createResponse()
                    ->withBody($this->httpClient->createStream(
                        $this->generateFormPostHtmlWithProviderConfig($this->key, $parameters)
                    ));
            default:
                throw new \DomainException("Undefined response method: {$this->method}");
        }
    }

    /**
     * Using provider config key
     *
     * @param string $key
     * @return $this
     */
    public function withConfig(string $key): ConfigurableResponseBuilder
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Using GET method to request provider
     *
     * @return $this
     */
    public function asRedirect(): ConfigurableResponseBuilder
    {
        $this->method = self::METHOD_REDIRECT;

        return $this;
    }

    /**
     * Using POST method to request provider
     *
     * @return $this
     */
    public function asForm(): ConfigurableResponseBuilder
    {
        $this->method = self::METHOD_FORM;

        return $this;
    }
}
