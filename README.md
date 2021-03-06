# OpenID Connect Core Library for PHP

![tests](https://github.com/oidcphp/core/workflows/tests/badge.svg)
[![Coverage Status][coverage-svg]][coverage-link]
[![Codacy Badge][codacy-svg]][codacy-link]
[![Latest Stable Version][latest-stable-svg]][packagist-link]
[![Total Downloads][total-download-svg]][packagist-link]
[![License][license-svg]][license-link]

OpenID Connect Core Library implementation for PHP.

[license-svg]: https://img.shields.io/badge/license-MIT-brightgreen.svg
[license-link]: https://github.com/oidcphp/core/blob/master/LICENSE
[coverage-svg]: https://codecov.io/gh/oidcphp/core/branch/master/graph/badge.svg
[coverage-link]: https://codecov.io/gh/oidcphp/core
[codacy-svg]: https://api.codacy.com/project/badge/Grade/d1d31fd3aa3644839e18bb929a20d993
[codacy-link]: https://www.codacy.com/manual/oidcphp/core
[latest-stable-svg]: https://poser.pugx.org/oidc/core/v/stable
[total-download-svg]: https://poser.pugx.org/oidc/core/d/total.svg
[packagist-link]: https://packagist.org/packages/oidc/core

## Required Packages

* PSR-7、PSR-17、PSR-18 implementations for process HTTP request / response.
* PSR-11 implementation for handle service container.
* `web-token/jwt-framework=^2.2` for verify JWT.

## Implemented specs

* [OpenID Connect Core 1.0][spec-openid-core]
* [OpenID Connect Discovery 1.0][spec-openid-discovery]
* [RFC 6749 - The OAuth 2.0 Authorization Framework][spec-rfc6749]

[spec-openid-core]: https://openid.net/specs/openid-connect-core-1_0.html
[spec-openid-discovery]: https://openid.net/specs/openid-connect-discovery-1_0.html
[spec-rfc6749]: https://tools.ietf.org/html/rfc6749

## References

### OpenID Connect Discovery examples

| IdP | well known |
| --- | --- |
| [Apple ID](https://developer.apple.com/sign-in-with-apple/) | [Discovery](https://appleid.apple.com/.well-known/openid-configuration) |
| [Google](https://developers.google.com/identity/protocols/OpenIDConnect) | [Discovery](https://accounts.google.com/.well-known/openid-configuration) |
| [Line](https://developers.line.biz/en/docs/line-login/web/integrate-line-login/) | [Discovery](https://access.line.me/.well-known/openid-configuration) |
| [Office 365](https://www.office.com/) | [Discovery](https://login.microsoftonline.com/common/.well-known/openid-configuration) |
