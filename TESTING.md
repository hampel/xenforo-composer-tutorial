
* test by visiting http://xenforo23.local/admin.php?tools/test-composertutorial and check that times are displayed 
  correctly and without generating errors

* after building a release, unzip it and confirm `upload/src/addons/ComposerTutorial/vendor/`
  is present. build.json's exec steps run through passthru() and their exit status is
  discarded, so a failed `composer install` still produces a zip - just one with no
  dependencies in it