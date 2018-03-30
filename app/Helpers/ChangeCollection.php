<?php

namespace App\Helpers;

use Doctrine\Common\Collections\Collection;

trait ChangeCollection
{
	protected static function changeCollection(Collection $collection, array $data, callable $addMethod = null, callable $removeMethod = null): void
	{
		foreach ($collection as $key => $element)
		{
			if (($dataKey = array_search($element, $data, true)) !== false)
				unset($data[$dataKey]);
			else
			{
				if ($removeMethod !== null)
					$removeMethod($element);
				else
					$collection->remove($key);
			}
		}

		foreach ($data as $element)
		{
			if ($addMethod !== null)
				$addMethod($element);
			else
				$collection->add($element);
		}
	}
}
