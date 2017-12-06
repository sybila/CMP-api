<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\ApiResponse;
use Nette\Application\IResponse;
use Nette\Application\Request;
use Nette\Application\UI\Presenter;
use Ublaboo\ApiRouter\ApiRoute;

/**
 * API for logging users in
 * 
 * @ApiRoute(
 * 	"/login",
 * 	methods={
 * 		"POST"="run"
 * 	},
 *  presenter="Login",
 *  format="json"
 * )
 */
final class LoginController extends Presenter
{
	// public function run(Request $request): IResponse
	// {
	// 	var_dump($request);
	// 	return new ApiResponse($this->apiResponseFormatter->formatMessage('Hello'));
	// }

	/**
	 * Get user detail
	 *
	 * @ApiRoute(
	 * 	"/login/users/<id>[/<foo>-<bar>]",
	 * 	parameters={
	 * 		"id"={
	 * 			"requirement": "\d+"
	 * 		}
	 * 	},
	 * 	method="GET"
	 * )
	 */
	public function actionRead($id, $foo = NULL, $bar = NULL)
	{
		$this->sendJson(['id' => $id, 'foo' => $foo, 'bar' => $bar]);
	}
}
