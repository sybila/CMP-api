<?php

namespace App\Helpers;

use Consistence\Enum\Enum;

abstract class ConsistenceEnum extends Enum
{
	public function __toString()
	{
		return $this->getValue();
	}
}
