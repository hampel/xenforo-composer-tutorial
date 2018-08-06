<?php namespace ComposerTutorial;

use XF\App;

class Listener
{
	public static function appSetup(App $app)
	{
		Composer::autoloadNamespaces($app);
		Composer::autoloadPsr4($app);
		Composer::autoloadClassmap($app);
		Composer::autoloadFiles($app);
	}
}