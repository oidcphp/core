# OpenID Connect Core Library for PHP

![tests](https://github.com/oidcphp/core/workflows/tests/badge.svg)
[![Coverage Status](https://codecov.io/gh/oidcphp/core/branch/master/graph/badge.svg)](https://codecov.io/gh/oidcphp/core)
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/47f59c13758f494fae2522b2fc15cb1b)](https://www.codacy.com/gh/oidcphp/core/dashboard)
[![Latest Stable Version](https://poser.pugx.org/oidc/core/v/stable)](https://packagist.org/packages/oidc/core)
[![Total Downloads](https://poser.pugx.org/oidc/core/d/total.svg)](https://packagist.org/packages/oidc/core)
[![License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](https://github.com/oidcphp/core/blob/master/LICENSE)

OpenID Connect Core Library implementation for PHP.

## Required Packages

* PSR-7、PSR-17、PSR-18 implementations for process HTTP request / response.
* PSR-11 implementation for handle service container.
* `web-token/jwt-framework=^2.2` for verify JWT.

## Implemented specs

* [OpenID Connect Core 1.0](https://openid.net/specs/openid-connect-core-1_0.html)
* [OpenID Connect Discovery 1.0](https://openid.net/specs/openid-connect-discovery-1_0.html)
* [RFC 6749 - The OAuth 2.0 Authorization Framework](https://tools.ietf.org/html/rfc6749)

## References

### OpenID Connect / OAuth 2.0 Discovery examples

| IdP | well known link |
| --- | --- |
| [Apple ID](https://developer.apple.com/sign-in-with-apple/) | [Discovery](https://appleid.apple.com/.well-known/openid-configuration) |
| [Facebook Limit Login](https://developers.facebook.com/docs/facebook-login/limited-login/token/) | [Discovery](https://www.facebook.com/.well-known/openid-configuration/) |
| [Google](https://developers.google.com/identity/protocols/OpenIDConnect) | [Discovery](https://accounts.google.com/.well-known/openid-configuration) |
| [LINE](https://developers.line.biz/en/docs/line-login/web/integrate-line-login/) | [Discovery](https://access.line.me/.well-known/openid-configuration) |
| [LinkedIn](https://www.linkedin.com/) | [Discovery](https://www.linkedin.com/oauth/.well-known/openid-configuration) |
| [Office 365](https://www.office.com/) | [Discovery](https://login.microsoftonline.com/common/.well-known/openid-configuration) |
