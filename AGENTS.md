# AGENTS.md

The key words "MUST", "MUST NOT", "REQUIRED", "SHALL", "SHALL NOT", "SHOULD", "SHOULD NOT", "RECOMMENDED", "NOT RECOMMENDED", "MAY", and "OPTIONAL" in this document are to be interpreted as described in BCP 14 [RFC 2119](https://www.rfc-editor.org/rfc/rfc2119.html) [RFC 8174](https://www.rfc-editor.org/rfc/rfc8174.html) when, and only when, they appear in all capitals, as shown here.

## Purpose

This repository implements the Relying Party (client) side of [OpenID Connect Core 1.0](https://openid.net/specs/openid-connect-core-1_0.html) for PHP. The goal is a library whose behavior conforms to the specification, together with the related specifications listed in `README.md`, such as [OpenID Connect Discovery 1.0](https://openid.net/specs/openid-connect-discovery-1_0.html) and [RFC 6749](https://www.rfc-editor.org/rfc/rfc6749.html).

- The specification is the source of truth. Every MUST requirement of the specification MUST be implemented, and a validation step MUST NOT be skipped for convenience.
- Conformance is checked by [oidcphp/certification](https://github.com/oidcphp/certification) against the OpenID certification RP tests. A failing certification test SHOULD be investigated as a bug in this library first.

## Specification references in code

Code SHOULD show which part of a specification it implements.

- The class or method docblock SHOULD link the section with `@link`, using the anchor URL of the section, for example `@link https://openid.net/specs/openid-connect-core-1_0.html#IDTokenValidation` or `@link https://www.rfc-editor.org/rfc/rfc6749.html#section-2.3.1`.
- When implementing validation or processing steps, the specification text of each step SHOULD be copied into line comments right above the code. The quote MUST be verbatim and MUST keep the step number, spacing, and line breaks of the original text, for example:

  ```php
  // 2.  The Issuer Identifier for the OpenID Provider (which is
  //     typically obtained during Discovery) MUST exactly match
  //     the value of the iss (issuer) Claim.
  ```

  See `src/Jwt/Verifiers/IdTokenVerifier.php` and `src/Jwt/Verifiers/LogoutTokenVerifier.php`.
- Accessors of claims and metadata SHOULD use the definition from the specification as the description, for example `Expiration time on or after which the ID Token MUST NOT be accepted for processing` in `src/Jwt/Claims.php`.
- When the library makes a decision the specification leaves open, the decision SHOULD be explained in a separate sentence next to the quote, for example `PHP Server will be a confidential client, so secret is REQUIRED.` in `src/Metadata/ClientMetadata.php`.

## PHPDoc references

- References to code (FQSEN) MUST use `@see`, such as `@see \OpenIDConnect\Client::handleCallback()`.
- References to websites (URI), such as specifications, MUST use `@link`.
- A description after the reference is OPTIONAL.
- RFCs MUST be linked to the RFC Editor HTML page, `https://www.rfc-editor.org/rfc/rfcNNNN.html`, with `#section-X.Y` for a section, such as `https://www.rfc-editor.org/rfc/rfc6749.html#section-2.3.1`. `tools.ietf.org` MUST NOT be used, since it now redirects to an info page without the section anchor.

## Dependencies

The goal for the 1.x release is that `require` in `composer.json` SHOULD only depend on PHP itself and PSR packages (`psr/*`).

- PHP extensions (`ext-*`) that are always available in PHP and cannot be disabled, such as `ext-json` since PHP 8.0, MAY be required. Other extensions, such as `ext-openssl` or `ext-sodium`, need a reason like non-PSR packages.
- A non-PSR package MUST NOT be added to `require` without a reason. The reason MUST be stated in the pull request that adds it, and SHOULD be recorded in this section.
- Packages in `require-dev` MAY be non-PSR, since integration and testing sometimes need them.
- The current non-PSR packages in `require` are `mileschou/psr`, `paragonie/constant_time_encoding` and `web-token/jwt-library`. Before the 1.x release, each of them MUST either have a recorded reason or be removed.

## Documentation

The user documentation lives in [oidcphp/docs](https://github.com/oidcphp/docs) and MUST describe the behavior of the latest release line of this library.

- A pull request that changes the public API or user-visible behavior, such as a method signature, a default value, a validation step, a thrown exception, or a new feature, MUST be accompanied by a pull request to `oidcphp/docs` that updates the affected pages.
- The pull request in this repository SHOULD link the pull request in `oidcphp/docs`. The docs pull request SHOULD NOT be merged before this one.
- Internal refactoring without user-visible changes, tests, and CI changes do not need a docs update.
- The documentation MUST only describe the public API. Classes and methods marked `@internal` MUST NOT be documented as something users can depend on.

## Writing

- Everything in the repository and on GitHub MUST be written in English, including files, commit messages, issues, pull requests, releases, and reports.
