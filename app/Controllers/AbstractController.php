<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\ApiResponse;
use App\Http\ErrorException;
use App\Model\InvalidTypeException;
use Nette;
use App\Http\ApiResponseFormatter;
use Nette\Application\Request;
use Nette\Database\Connection;
use Nette\Application;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Presenter;

/**
 * @property-read Request $request
 * @property-read \stdClass $payload
 */
abstract class AbstractController extends Presenter
{
	/** @var Connection @inject */
	public $db;

	/**
	 * @var ApiResponseFormatter
	 */
	protected $apiResponseFormatter;

	/** @var Request|null */
	private $request;

	/** @var \stdClass */
	protected $payload;

	/** @var Nette\Caching\IStorage @inject */
	public $cacheStorage;

	public function run(Request $request)
	{
		try {
			$this->request = $request;
			$this->payload = new \stdClass;
			$this->setParent($this->getParent(), $request->getPresenterName());

			$this->init();
			$this->checkRequirements($this->getReflection());
			$this->startup();
			// calls $this->action<Action>()
			$this->tryCall($this->formatActionMethod($this->action), $this->params);
			$this->terminate();
		} catch (Application\AbortException $e) { // intentionally empty, handle after try-catch block
		} catch (BadRequestException $e) {
			return new ApiResponse($this->apiResponseFormatter->formatError('Bad request'));
		} catch (ErrorException $e) {
			return new ApiResponse($this->apiResponseFormatter->formatError($e->getMessage(), $e->getCode()));
		} /*catch (\Exception $e) {
			$this->onShutdown($this->getHttpResponse());
			return new ApiResponse($this->apiResponseFormatter->formatException($e));
		}*/

		return new ApiResponse($this->apiResponseFormatter->formatPayload((array)$this->payload));
	}

	public function getPayload()
	{
		return $this->payload;
	}

	public function getRequest(): Request
	{
		return $this->request;
	}

	public function __construct(ApiResponseFormatter $apiResponseFormatter)
	{
		parent::__construct();
		$this->apiResponseFormatter = $apiResponseFormatter;
	}

	/**
	 * Initializes $this->action, $this->view. Called by run().
	 * Code taken from UI\Presenter and deleted unused code
	 * @return void
	 * @throws BadRequestException if action name is not valid
	 */
	private function init()
	{
		$selfParams = [];

		$params = $this->request->getParameters();
		if (($tmp = $this->request->getPost('_' . self::SIGNAL_KEY)) !== null) {
			$params[self::SIGNAL_KEY] = $tmp;
		} elseif ($this->isAjax()) {
			$params += $this->request->getPost();
			if (($tmp = $this->request->getPost(self::SIGNAL_KEY)) !== null) {
				$params[self::SIGNAL_KEY] = $tmp;
			}
		}

		foreach ($params as $key => $value) {
			if (!preg_match('#^((?:[a-z0-9_]+-)*)((?!\d+\z)[a-z0-9_]+)\z#i', $key, $matches)) {
				continue;
			} elseif (!$matches[1]) {
				$selfParams[$key] = $value;
			}
		}

		// init & validate $this->action & $this->view
		$this->changeAction(isset($selfParams[self::ACTION_KEY]) ? $selfParams[self::ACTION_KEY] : self::DEFAULT_ACTION);
		$this->loadState($selfParams);
	}
}
