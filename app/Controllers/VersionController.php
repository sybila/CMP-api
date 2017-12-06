<?php

declare(strict_types=1);

namespace App\Controllers;

use Ublaboo\ApiRouter\ApiRoute;

/**
 * Returns API version information
 *
 * @ApiRoute(
 * 	"/version",
 *  presenter="Version",
 *  format="json"
 * )
 */
final class VersionController extends AbstractController
{
	public function actionRead()
	{
		$this->payload->version = '0.1';
	}
}
