<?php

namespace App\Core;

use Plasticode\Core\Builder as BuilderBase;
use Plasticode\Util\Cases;
use Plasticode\Util\Strings;

class Builder extends BuilderBase
{
	/*public function buildDummy()
	{
		$usersCount = count($this->db->getUsers());
		
		return [
			'count' => $usersCount,
			'count_str' => $this->cases->caseForNumber('пользователь', $usersCount),
		];
	}*/

	public function buildProjects($rows)
	{
	    if (!$rows) {
	        return null;
	    }
	    
		return array_map(function($row) {
			return $this->buildProject($row);
		}, $rows);
	}
	
	public function buildProject($project, $withSyncs = false)
	{
	    $syncs = $this->db->getSyncs($project['id']);
	    
	    $syncCount = count($syncs);
	    $project['sync_count_str'] = $syncCount . ' ' . $this->cases->caseForNumber('синхронизация', $syncCount);
	    
	    if ($withSyncs) {
	        $syncs = array_map(function($sync) {
	            return $this->buildSync($sync, true);
	        }, $syncs);
	    }
	    
	    $project['syncs'] = $syncs;
	    
	    return $project;
	}
	
	public function buildSync($sync)
	{
        $sync['ignore'] = Strings::explode($sync['ignore']);
        $sync['ignore_size'] = Strings::explode($sync['ignore_size']);
        
        $primary = $this->db->getFolder($sync['primary_id']);
        $sync['primary'] = $this->buildFolder($primary);
        
        $replica = $this->db->getFolder($sync['replica_id']);
        $sync['replica'] = $this->buildFolder($replica);
        
        $sync['primary_path'] = $sync['primary']['path'];
        $sync['replica_path'] = $sync['replica']['path'];

	    return $sync;
	}
	
	public function buildFolder($folder)
	{
	    $conId = $folder['connection_id'];
	    $folder['connection'] = $this->db->getConnection($conId);

	    return $folder;
	}
	
	public function buildProjectSyncResults($project)
	{
	    foreach ($project['syncs'] as $sync) {
	        $results[$sync['id']] = $this->buildSyncResult($sync);
	    }
	    
	    return $results;
	}
	
	public function buildSyncResult($sync)
	{
	    try {
	        $result = $this->synchronizer->buildSync($sync);
	    }
	    catch (\Exception $ex) {
	        $result = [ 'error' => $ex->message ];
	    }
	    
	    return $result;
	}
}
