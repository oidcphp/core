# OpenID Connect Core Library for PHP

[![License][license-svg]][license-link]
[![Build Status][travis-svg]][travis-link]
[![Coverage Status][coveralls-svg]][coveralls-link]
[![Latest Stable Version][latest-stable-svg]][packagist-link]
[![Total Downloads][total-download-svg]][packagist-link]

OpenID Connect Core Library implementation for PHP.

## Required Packages

* `guzzlehttp/guzzle=^6.0` for send HTTP request
* `league/oauth2-client=^2.4` for execute the OAuth 2.0 flow
* `web-token/jwt-framework=^1.3` for verify JWT

## Implemented specs

* [OpenID Connect Core 1.0][spec-openid-core]
* [OpenID Connect Discovery 1.0][spec-openid-discovery]
* [RFC 6749 - The OAuth 2.0 Authorization Framework][spec-rfc6749] (based on [`league/oauth2-client`](https://oauth2-client.thephpleague.com/))

[spec-openid-core]: https://openid.net/specs/openid-connect-core-1_0.html
[spec-openid-discovery]: https://openid.net/specs/openid-connect-discovery-1_0.html
[spec-rfc6749]: https://tools.ietf.org/html/rfc6749

## References

### OpenID Connect Discovery examples

| IdP | well known |
| --- | --- |
| [Apple ID](https://developer.apple.com/sign-in-with-apple/) | [Discovery](https://appleid.apple.com/auth/.well-known/openid-configuration) |
| [Google](https://developers.google.com/identity/protocols/OpenIDConnect) | [Discovery](https://accounts.google.com/.well-known/openid-configuration) |
| [Line](https://developers.line.biz/en/docs/line-login/web/integrate-line-login/) | [Discovery](https://access.line.me/.well-known/openid-configuration) |

[license-svg]: https://img.shields.io/badge/license-MIT-brightgreen.svg
[travis-svg]: https://travis-ci.com/oidcphp/core.svg?branch=master
[coveralls-svg]: https://coveralls.io/repos/github/oidcphp/core/badge.svg?branch=master
[latest-stable-svg]: https://poser.pugx.org/oidc/core/v/stable
[total-download-svg]: https://poser.pugx.org/oidc/core/d/total.svg
[license-link]: https://github.com/oidcphp/core/blob/master/LICENSE
[travis-link]: https://travis-ci.com/oidcphp/core
[coveralls-link]: https://coveralls.io/github/oidcphp/core
[packagist-link]: https://packagist.org/packages/oidc/core
