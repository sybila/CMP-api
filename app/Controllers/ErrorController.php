<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\ApiResponse;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\IResponse;
use Nette\Application\Request;
use Nette\Http\IResponse as IHttpResponse;
use Tracy\Debugger;
use Tracy\ILogger;

final class ErrorController extends AbstractController
{
	public function run(Request $request): IResponse
	{
		$e = $request->getParameter('exception');
		Debugger::log($e, ILogger::EXCEPTION);

		if ($e instanceof BadRequestException)
		{
			$this->getHttpResponse()->setCode(IHttpResponse::S404_NOT_FOUND);
			$response = $this->apiResponseFormatter->formatHttpError('Resource was not found', IHttpResponse::S404_NOT_FOUND);
		}
		elseif ($e instanceof ForbiddenRequestException)
		{
			$this->getHttpResponse()->setCode(IHttpResponse::S403_FORBIDDEN);
			$response = $this->apiResponseFormatter->formatHttpError('Forbidden request', IHttpResponse::S403_FORBIDDEN);
		}
		else
		{
			$this->getHttpResponse()->setCode(IHttpResponse::S500_INTERNAL_SERVER_ERROR);
			$response = $this->apiResponseFormatter->formatInternalError($e);
		}

		return new ApiResponse($response);
	}
}
