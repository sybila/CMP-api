<?php

declare(strict_types=1);

namespace App\Http;

use Nette;
use Nette\Application\Responses\JsonResponse;
use Tracy\Debugger;

final class ApiResponse extends JsonResponse
{
	public function send(Nette\Http\IRequest $httpRequest, Nette\Http\IResponse $httpResponse)
	{
		if (Debugger::isEnabled())
		{
			$httpResponse->setContentType('text/html', 'utf-8');
			echo '<pre>';
		}

		echo Nette\Utils\Json::encode($this->getPayload(), Debugger::isEnabled() ? Nette\Utils\Json::PRETTY : 0);
	}
}
