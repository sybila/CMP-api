<?php

declare(strict_types=1);

namespace App\Controllers;

final class DefaultController extends AbstractController
{
	public function actionRead()
	{
		$this->redirect(301, 'Version:read');
	}
}
