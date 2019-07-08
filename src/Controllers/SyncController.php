<?php

namespace App\Controllers;

use Plasticode\Controllers\RestController;
use Plasticode\Core\Core;
use Plasticode\Util\Date;

class SyncController extends RestController
{
	public function syncFile($request, $response, $args)
	{
		$id = $args['id'];
		
		$sync = $this->db->getSync($id);

		if (!$sync) {
		    $this->notFound($this->translate('Sync not found.'));
		}
		
		$sync = $this->builder->buildSync($sync);

    	$data = $request->getParsedBody();
    	$file = $data['file'];

    	$fileInfo = $this->synchronizer->syncFile($sync, $file);

		return Core::json($response, [
		    'size' => $fileInfo['size'],
		    'modified_at' => Date::formatIso($fileInfo['modified_at']),
		]);
	}
}
