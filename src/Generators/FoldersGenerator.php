<?php

namespace App\Generators;

use Plasticode\Generators\ChildEntityGenerator;

class FoldersGenerator extends ChildEntityGenerator
{
	public function __construct($container, $entity)
	{
		parent::__construct($container, $entity, [
		    'parent' => [ 'name' => 'connection', 'label' => 'Соединения' ],
		    'child' => [ 'name' => 'folder', 'label' => 'Папки' ],
		]);
	}
}
