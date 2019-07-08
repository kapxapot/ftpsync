<?php

namespace App\Controllers;

class ProjectController extends Controller
{
	public function item($request, $response, $args)
	{
		$id = $args['id'];

		$showAll = $request->getQueryParam('show_all', false);

		$row = $this->db->getProject($id);

		if (!$row) {
			return $this->notFound($request, $response);
		}

		$project = $this->builder->buildProject($row, true);
		$results = $this->builder->buildProjectSyncResults($project);
		
		foreach ($project['syncs'] as &$sync) {
		    $sync['result'] = $results[$sync['id']];
		}

		$params = $this->buildParams([
			'params' => [
				'project' => $project,
		        'title' => $project['name'],
		        'show_all' => $showAll,
				'no_disqus' => 1,
				'no_social' => 1,
			],
		]);

		return $this->view->render($response, 'main/projects/item.twig', $params);
	}
}
