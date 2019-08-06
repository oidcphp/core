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
    public function authorizationPost(array $options = []): string
    {
        $baseAuthorizationUrl = $this->leagueOAuth2Client->getBaseAuthorizationUrl();

        parse_str((string)parse_url($this->authorizationUrl($options), PHP_URL_QUERY), $query);

        $formInput = implode('', array_map(function ($v, $k) {
            return "<input type=\"hidden\" name=\"${k}\" value=\"${v}\"/>";
        }, $query, array_keys($query)));

        return <<< HTML
<!DOCTYPE html>
<head><title>Requesting Authorization</title></head>
<body onload="javascript:document.forms[0].submit()">
<form method="post" action="${baseAuthorizationUrl}">${formInput}</form>
</body>
</html>
HTML;
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
