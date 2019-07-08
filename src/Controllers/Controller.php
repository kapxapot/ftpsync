<?php

namespace App\Controllers;

use Plasticode\Controllers\Controller as BaseController;

class Controller extends BaseController
{
	protected function buildPart($settings, $result, $part)
	{
		switch ($part) {
			case 'dummy':
				$result[$part] = $this->builder->buildDummy();
				break;

			default:
				$result = null;
				break;
		}
		
		return $result;
	}
}
