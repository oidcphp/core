<?php

namespace OpenIDConnect;

use League\OAuth2\Client\Provider\AbstractProvider;

class Client
{
    /**
     * @var AbstractProvider
     */
    private $leagueOAuth2Client;

    /**
     * @param AbstractProvider $leagueOAuth2Client
     */
    public function __construct(AbstractProvider $leagueOAuth2Client)
    {
        $this->leagueOAuth2Client = $leagueOAuth2Client;
    }

    /**
     * @param array $options
     * @return string
     */
    public function authorizationUrl(array $options = []): string
    {
        return $this->leagueOAuth2Client->getAuthorizationUrl($options);
    }
}
