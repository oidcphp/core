---
name: draft-release
description: Draft the GitHub release notes of the next oidc/core version and create them as a draft release. Use when the user asks to prepare, write or draft a release, release notes or a changelog for a version, such as "draft release 0.19.0".
---

# Draft release

Create a **draft** GitHub release for the next version of `oidcphp/core`. The notes follow the `## Releases` section of `AGENTS.md`; read it first, since it is the source of the rules. This skill never publishes the release: the maintainer reviews the draft and publishes it.

## 1. Collect the inputs

1. Update the release branch: `git switch 0.x && git pull --ff-only origin 0.x`. The release branch is `0.x` unless the user names another one.
2. Find the previous release: `gh release list --exclude-drafts -L 1 --json tagName -q '.[0].tagName'`.
3. If the user did not give the version, propose one and ask. In `0.x`, a breaking change bumps the minor version (`0.18.0` → `0.19.0`), and a release with only fixes bumps the patch version.
4. If a draft release for the version already exists (`gh release view v<version>`), show it and ask whether to update it instead of creating another.

## 2. List the merged pull requests

1. List the commits: `git log --oneline v<previous>..origin/0.x`. Pull requests are squash merged, so each subject ends with `(#<number>)`.
2. For each pull request, read its title, author, body and linked issues:
   `gh pr view <number> --json number,title,author,body,closingIssuesReferences`
3. Read the diff (`gh pr diff <number>`) when the title and body do not make the user-visible effect clear. Do not describe a change you have not checked.

## 3. Check before writing

- **Docs**: `AGENTS.md` requires the docs to describe the latest release. For each pull request that links an `oidcphp/docs` pull request, check that it is merged (`gh pr view <number> -R oidcphp/docs --json state`). Report the open ones to the user, since they should be merged before the release is published.
- **Security advisories**: `gh api repos/oidcphp/core/security-advisories --jq '.[] | [.ghsa_id, .state, .summary] | @tsv'`. Add the GHSA ID of an advisory that matches a security fix to its entry. If a security fix has no advisory, tell the user and ask whether one should be created; do not create it.
- **First-time contributors**: take them from the generated notes, `gh api repos/oidcphp/core/releases/generate-notes -f tag_name=v<version> -f target_commitish=0.x -f previous_tag_name=v<previous> -q .body`, section `## New Contributors`.

## 4. Write the notes

Follow `## Releases` in `AGENTS.md`. In short:

- Headings in this order, without empty ones: `### Security`, `### Added`, `### Changed`, `### Deprecated`, `### Removed`, `### Fixed`.
- One short sentence per entry about what changes for the user, then the pull request links. Pull requests that make one change together, such as a change and its follow-up, share one entry.
- A change that can break existing code, including a removed or newly `@internal` API, gets an indented line under its entry.
- Leave out CI, tests, internal refactoring and AGENTS.md changes.
- Thank first-time contributors, and end with `**Full Changelog**: https://github.com/oidcphp/core/compare/v<previous>...v<version>`.

Example, from the notes of v0.19.0:

```markdown
### Security

- Escape the parameters and the endpoint URI in form post responses (#43) [#50](https://github.com/oidcphp/core/pull/50)

### Added

- Support `web-token/jwt-library` 4.x [#46](https://github.com/oidcphp/core/pull/46) [#47](https://github.com/oidcphp/core/pull/47)

### Changed

- Encode token and revocation request bodies as `application/x-www-form-urlencoded` (#23) [#51](https://github.com/oidcphp/core/pull/51)
  - `OpenIDConnect\Http\Query` is now internal, and `Query::build()` is removed

Thanks to @KazeNoYumeX for the first contribution in #46.

**Full Changelog**: https://github.com/oidcphp/core/compare/v0.18.0...v0.19.0
```

## 5. Create the draft

1. Show the notes to the user, together with the pull requests that were left out and why, and the results of step 3. Wait for the user to confirm or edit.
2. Write the confirmed notes to a temporary file outside the repository, and create the draft:

   ```sh
   gh release create v<version> --draft --target 0.x --title v<version> --notes-file <file>
   ```

   To update an existing draft instead: `gh release edit v<version> --notes-file <file>`.
3. Report the URL of the draft. Do not run `gh release edit --draft=false` or publish the release in any other way.
