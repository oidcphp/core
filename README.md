# OpenID Connect Core Library for PHP

[![Software License][license-svg]][license-link]
[![Build Status][travis-svg]][travis-link]
[![Coverage Status][coveralls-svg]][coveralls-link]

OpenID Connect Core Library implementation for PHP.

## Required Packages

* `guzzlehttp/guzzle=^6.0` for send HTTP request
* `league/oauth2-client=^2.4` for execute the OAuth 2.0 flow
* `web-token/jwt-framework=^1.3` for verify JWT

## References

### OpenID Connect Discovery examples

| IdP | well known |
| --- | --- |
| [Google](https://developers.google.com/identity/protocols/OpenIDConnect) | [Discovery](https://accounts.google.com/.well-known/openid-configuration) |
| [Line](https://developers.line.biz/en/docs/line-login/web/integrate-line-login/) | [Discovery](https://access.line.me/.well-known/openid-configuration) |


[license-svg]: https://img.shields.io/badge/license-MIT-brightgreen.svg
[license-link]: https://github.com/oidcphp/core/blob/master/LICENSE
[travis-svg]: https://travis-ci.com/oidcphp/core.svg?branch=master
[travis-link]: https://travis-ci.com/oidcphp/core
[coveralls-svg]: https://coveralls.io/repos/github/oidcphp/core/badge.svg?branch=master
[coveralls-link]: https://coveralls.io/github/oidcphp/core
