<?php

namespace App\Generators;

use Respect\Validation\Validator as v;

use Plasticode\Generators\ChildEntityGenerator;

class SyncsGenerator extends ChildEntityGenerator
{
	public function __construct($container, $entity)
	{
		parent::__construct($container, $entity, [
		    'parent' => [ 'name' => 'project', 'label' => 'Проекты' ],
		    'child' => [ 'name' => 'sync', 'label' => 'Синхронизации' ],
		]);
	}

	public function getRules($data, $id = null)
	{
	    $rules = parent::getRules($data, $id);
	    
	    $rules['primary_id'] = v::notEmpty();
	    $rules['replica_id'] = v::notEmpty();

	    return $rules;
	}
}
