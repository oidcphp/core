<?php

namespace OpenIDConnect\Core\Traits;

use OpenIDConnect\Core\ClientAuthentication\ClientSecretBasic;
use OpenIDConnect\Core\ClientAuthentication\RequestAppender;

trait ClientAuthenticationAwareTrait
{
    /**
     * @var RequestAppender
     */
    private $defaultRequestAppender;

    /**
     * Client authentication appender by introspection endpoint.
     *
     * @var RequestAppender
     */
    private $introspectionRequestAppender;

    /**
     * Client authentication appender by revocation endpoint.
     *
     * @var RequestAppender
     */
    private $revocationRequestAppender;

    /**
     * Client authentication appender by token endpoint.
     *
     * @var RequestAppender
     */
    private $tokenRequestAppender;

    protected function defaultRequestAppender(): RequestAppender
    {
        if (null === $this->defaultRequestAppender) {
            $this->defaultRequestAppender = new ClientSecretBasic();
        }

        return $this->defaultRequestAppender;
    }

    /**
     * @return RequestAppender
     */
    protected function getIntrospectionRequestAppender(): RequestAppender
    {
        if (null === $this->introspectionRequestAppender) {
            $this->introspectionRequestAppender = $this->defaultRequestAppender();
        }

        return $this->introspectionRequestAppender;
    }

    /**
     * @return RequestAppender
     */
    protected function getRevocationRequestAppender(): RequestAppender
    {
        if (null === $this->revocationRequestAppender) {
            $this->revocationRequestAppender = $this->defaultRequestAppender();
        }

        return $this->revocationRequestAppender;
    }

    /**
     * @return RequestAppender
     */
    protected function getTokenRequestAppender(): RequestAppender
    {
        if (null === $this->tokenRequestAppender) {
            $this->tokenRequestAppender = $this->defaultRequestAppender();
        }

        return $this->tokenRequestAppender;
    }

    /**
     * @param RequestAppender $requestAppender
     * @return static
     */
    public function setIntrospectionRequestAppender(RequestAppender $requestAppender)
    {
        $this->introspectionRequestAppender = $requestAppender;
        return $this;
    }

    /**
     * @param RequestAppender $requestAppender
     * @return static
     */
    public function setRevocationRequestAppender(RequestAppender $requestAppender)
    {
        $this->revocationRequestAppender = $requestAppender;
        return $this;
    }

    /**
     * @param RequestAppender $requestAppender
     * @return static
     */
    public function setTokenRequestAppender(RequestAppender $requestAppender)
    {
        $this->tokenRequestAppender = $requestAppender;
        return $this;
    }
}
