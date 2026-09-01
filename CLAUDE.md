# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Read the install's guidance first

This add-on is developed inside a XenForo install, at `src/addons/ComposerTutorial/`. Before
starting work here, read — from the install root, three levels up:

- `AGENTS.md` — the install: XF version, `cmd.php` commands, the `_output/`↔`_data/` boundary,
  and the rules that bite.
- `.agents/skills/xf-shared-policy/SKILL.md` and `.agents/skills/xf-addon-dev/SKILL.md` —
  **not** auto-registered by the Skill tool, so open them with the Read tool.

Machine-specific details — the checkout path, the dev vhost name, the audit record — live in
`CLAUDE.local.md`, which is gitignored. Keep them out of this file: it is committed and public.

## What this add-on is for

It is a **teaching artifact**, not a feature. Its entire job is to demonstrate the mechanism
for shipping Composer packages inside a XenForo 2 add-on, and it backs a published tutorial
([resource 6588](https://xenforo.com/community/resources/using-composer-packages-in-xenforo-2-addons-tutorial.6588/)
for XF 2.0, rewritten as
[resource 7432](https://xenforo.com/community/resources/using-composer-packages-in-xenforo-2-1-addons-tutorial.7432/)
once XF 2.1 gained built-in Composer support).

The Carbon date formatting in `XF/Admin/Controller/Tools.php` is filler — it exists only to
prove a third-party package autoloads. Keep changes minimal and legible; a clever refactor
that obscures the mechanism makes the add-on worse at its only job. Anything that changes the
mechanism has a matching tutorial to update on xenforo.com.

Because it is a tutorial, it stays deliberately **generic**: it should remain installable on an
old XF 2.1 site. That is not the approach the production add-ons take.

## The mechanism the add-on demonstrates

Four pieces, and they only work together:

1. `composer.json` requires `nesbot/carbon: ^2`, with `config.platform.php` pinned to `7.1.8`
   so dependencies resolve against the add-on's declared floor rather than whatever PHP the
   development box happens to run. Carbon's own floor is `^7.1.8 || ^8.0`, which is where the
   add-on's PHP requirement comes from — not from XenForo.
2. `addon.json` sets `"composer_autoload": "vendor/composer"` — this is what makes XenForo
   register the add-on's private autoloader, and it is why `require.XF` is `2.1.0+`.
3. `Setup.php::checkRequirements()` fails the install when `vendor/` is missing. This is not
   boilerplate: `build.json` exec steps run through `passthru()` with their exit status
   discarded, so a failed `composer install` still produces a release zip — just one with no
   dependencies in it. This guard is the only thing between that zip and the user.
4. `build.json` `exec` re-runs `composer install --no-dev --optimize-autoloader` into the
   build output, strips the dev-only docs, and relocates the rest.

The single class extension (`XF\Admin\Controller\Tools` → `ComposerTutorial\XF\...`) plus an
admin nav entry under `checksAndTests` exist to give the package somewhere visible to run.

## Add-on ID has no vendor prefix

The ID is `ComposerTutorial` — not `Vendor/ComposerTutorial` — so the path is
`src/addons/ComposerTutorial` and the PHP root namespace is `ComposerTutorial\`. Every
`cmd.php` invocation uses the bare ID.

## Commands

`cmd.php` lives at the install root, not here:

```bash
php cmd.php xf-dev:import --addon=ComposerTutorial    # after hand-editing _output/
php cmd.php xf:addon-install ComposerTutorial
php cmd.php xf-addon:bump-version ComposerTutorial    # ask first
php cmd.php xf-addon:build-release ComposerTutorial   # release only — runs xf-dev:export
```

`vendor/` is gitignored, and both `checkRequirements()` and the class extension need it, so
after a fresh clone run `composer install` in the add-on directory.

## Verification is manual — there is no test suite

No `phpunit.xml`, no `tests/`, and deliberately so: a test suite would bury the one mechanism
this add-on exists to show. `TESTING.md` is the whole check.

## What ships, and what does not

The release zip's root is `_build/`, **not** `_build/upload/`. Files under `_build/upload/` are
copied onto the user's server; files at `_build/` are visible when the zip is opened but not
uploaded. Both are inside the release.

XenForo's builder excludes `_*` directories, `.git`, `.svn` and any file whose name begins with
a dot — but **not** `CLAUDE.md`, `CLAUDE.local.md` or `TESTING.md`. Those are named explicitly
in `build.json`'s `rm` line, and `.gitattributes` marks them `export-ignore` so they are absent
from GitHub archive downloads too. A new dev-only file at the add-on root needs both, or it
gets published.

## Releasing

Version lives in three places that must agree: `addon.json` `version_id` and `version_string`,
and a new `CHANGELOG.md` section. `version_id` is XF's `abbccde` format — `2.1.2` stable is
`2010270`. Use `xf-addon:bump-version` rather than editing by hand; it writes both the file and
the database.

The changelog is dated and newest-first, one terse bullet per change, and records only
user-visible changes.
