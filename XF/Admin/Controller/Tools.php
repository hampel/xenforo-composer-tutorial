<?php

namespace ComposerTutorial\XF\Admin\Controller;

use Carbon\Carbon;

class Tools extends XFCP_Tools
{
	public function actionTestComposerTutorial()
	{
		$output = [];

		$output[] = sprintf("Right now is %s", Carbon::now()->toDateTimeString());
		$output[] = sprintf("Right now in Sydney is %s", Carbon::now('Australia/Sydney'));  //implicit __toString()
		$output[] = sprintf("Tomorrow is %s", Carbon::now()->addDay()->toDateTimeString());
		$output[] = sprintf("Last week is %s", Carbon::now()->subWeek()->toDateTimeString());
		$output[] = sprintf("Noon Today LondonTime is %s", Carbon::createFromTime(12, 0, 0, 'Europe/London')->toDateTimeString());

		$viewParams = compact('output');
		return $this->view('XF:Tools\TestComposerTutorial', 'composertutorial_test', $viewParams);
	}
}