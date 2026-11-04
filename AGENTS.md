# AGENTS.md

## Purpose

This repository implements the Relying Party (client) side of [OpenID Connect Core 1.0](https://openid.net/specs/openid-connect-core-1_0.html) for PHP. The goal is a library whose behavior conforms to the specification, together with the related specifications listed in `README.md`, such as [OpenID Connect Discovery 1.0](https://openid.net/specs/openid-connect-discovery-1_0.html) and [RFC 6749](https://tools.ietf.org/html/rfc6749).

- Treat the specification as the source of truth. Implement every MUST requirement, and do not skip a validation step for convenience.
- When code implements a specification step, quote the step in a comment and link the section, as `src/Jwt/Verifiers/IdTokenVerifier.php` does for ID Token validation.
- Conformance is checked by [oidcphp/certification](https://github.com/oidcphp/certification) against the OpenID certification RP tests. Investigate a failing certification test as a bug in this library first.

## Writing

- Write everything in the repository and on GitHub in English, including files, commit messages, issues, pull requests, releases, and reports.
