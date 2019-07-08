<?php

namespace App\Controllers;

use Plasticode\Util\Numbers;

class IndexController extends Controller
{
	public function index($request, $response, $args)
	{
	    $rows = $this->db->getProjects();
	    
	    $projects = $this->builder->buildProjects($rows);
	    
		$params = $this->buildParams([
			//'sidebar' => [ 'dummy' ],
			'params' => [
			    'projects' => $projects,
				'no_disqus' => 1,
				'no_social' => 1,
			],
		]);

		return $this->view->render($response, 'main/index.twig', $params);
	}
}
