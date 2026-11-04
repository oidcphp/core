# AGENTS.md

## Purpose

This repository implements the Relying Party (client) side of [OpenID Connect Core 1.0](https://openid.net/specs/openid-connect-core-1_0.html) for PHP. The goal is a library whose behavior conforms to the specification, together with the related specifications listed in `README.md`, such as [OpenID Connect Discovery 1.0](https://openid.net/specs/openid-connect-discovery-1_0.html) and [RFC 6749](https://tools.ietf.org/html/rfc6749).

- Treat the specification as the source of truth. Implement every MUST requirement, and do not skip a validation step for convenience.
- Conformance is checked by [oidcphp/certification](https://github.com/oidcphp/certification) against the OpenID certification RP tests. Investigate a failing certification test as a bug in this library first.

## Specification references in code

Every piece of code should show which part of a specification it implements.

- Link the section in the class or method docblock with `@see`, using the anchor URL of the section, for example `@see https://openid.net/specs/openid-connect-core-1_0.html#IDTokenValidation` or `@see https://tools.ietf.org/html/rfc6749#section-2.3.1`.
- When implementing validation or processing steps, copy the specification text verbatim into line comments right above the code for each step. Keep the step number, spacing, and line breaks of the original text, for example:

  ```php
  // 9.  The current time MUST be before the time represented by the exp
  //     Claim.
  $builder->add(ExpirationTimeChecker::class, $this->clockTolerance());
  ```

  See `src/Jwt/Verifiers/IdTokenVerifier.php` and `src/Jwt/Verifiers/LogoutTokenVerifier.php`.
- For accessors of claims and metadata, use the definition from the specification as the description, for example `Expiration time on or after which the ID Token MUST NOT be accepted for processing` in `src/Jwt/Claims.php`.
- When the library makes a decision the specification leaves open, explain it in a separate sentence next to the quote, for example `PHP Server will be a confidential client, so secret is REQUIRED.` in `src/Metadata/ClientMetadata.php`.

## Writing

- Write everything in the repository and on GitHub in English, including files, commit messages, issues, pull requests, releases, and reports.
