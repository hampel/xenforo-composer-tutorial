# Testing ComposerTutorial

Structure: **Surfaces** is the inventory of what this add-on touches — the input to deciding
what to test. **Automated** is what a session or CI can settle on its own. **Needs a human** is
what it cannot, and why.

Keep the last section honest. Every time a session says "this bit needs a browser", the line
belongs here rather than in the chat log.


## Surfaces

* Class extensions
	* `XF\Admin\Controller\Tools`
		* adds `actionTestComposerTutorial` — renders five Carbon-formatted dates, purely
		  to prove a Composer package autoloads

* Code event listeners
	* none

* Template modifications
	* none

* Other artifacts
	* admin navigation `testComposerTutorial`, under `checksAndTests`
	* template `admin/composertutorial_test`
	* phrases `composertutorial_test`, `admin_navigation.testComposerTutorial`
	* Composer autoload — `vendor/composer`, providing `nesbot/carbon ^2`
	* no schema; `Setup.php` only guards that `vendor/` exists


## Fragile points

* **The class extension is not exclusive.** `XF\Admin\Controller\Tools` is a popular thing
  to extend, so on a real forum this add-on is usually one link in a chain of several.
  `XF::extendClass()` resolves to whichever extension sits last, and this add-on's action is
  then reached through inheritance — correct XFCP behaviour, but it means the action can be
  affected by an unrelated add-on extending the same controller. Worth testing on an install
  that has other add-ons present, not only a clean one.
* **The build cannot fail.** `build.json` exec steps run through `passthru()` with the exit
  status discarded, so a `composer install` that fails still produces a zip — with no
  `vendor/`. `Setup.php::checkRequirements()` is the only thing that catches it, on the user's
  side, at install time.
* **The PHP floor comes from Carbon, not XenForo.** `^7.1.8 || ^8.0`. The `config.platform.php`
  pin in `composer.json` must track it, or the vendored tree stops matching the declared floor.


## Automated — no human needed

Run from the install root unless stated. None of this needs a browser or a login.

* `composer validate` in the add-on directory — manifest is well-formed.
* `php cmd.php xf-dev:class-lint` — exits 0, all classes load without conflict.
* Boot XF and assert Carbon autoloads **through XF's autoloader**, not the add-on's own
  `vendor/autoload.php` — this is the mechanism the add-on exists to demonstrate, and loading
  the autoloader directly proves nothing:

  ```php
  require '<install>/src/XF.php'; XF::start('<install>'); XF::setupApp('XF\Pub\App');
  assert(class_exists('Carbon\Carbon'));
  // and that it resolved from src/addons/ComposerTutorial/vendor/
  ```
* Render `admin:composertutorial_test` with the controller's own `$output` array and assert
  five lines with correct timezone arithmetic (UTC vs Australia/Sydney vs Europe/London).
* `XF::extendClass('XF\Admin\Controller\Tools')` — the resolved class has
  `actionTestComposerTutorial`.
* After exercising: `xf_error_log` has no new rows.
* Build a release and inspect the zip — root is `upload` plus `README.md`/`CHANGELOG.md`;
  no `CLAUDE.md`, `CLAUDE.local.md` or `TESTING.md`; `vendor/` complete; `hashes.json` present.


## Needs a human — an agent cannot do these

* **Look at the admin page.** `admin.php?tools/test-composertutorial` requires an authenticated
  admin session; an unauthenticated request returns the login page with HTTP 200, so nothing
  about it fails loudly. Confirm the five dated lines render, are readable, and that the dates
  are plausible for your timezone.
* **Confirm the navigation entry appears** under Tools → Checks and Tests in the admin sidebar,
  with the phrase "Test Composer Tutorial" rather than a raw phrase key.
* **Test the upgrade path.** Install the built zip over the previous version, on a separate
  install, as a user would — rather than only installing fresh. A `Setup.php` step that works
  on a fresh install and fails on upgrade is the classic failure, and nothing above would
  catch it.
* **Check the tutorial still matches the code.** This add-on backs a published article; if the
  mechanism changed, the article is wrong until someone edits it.
