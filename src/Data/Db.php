<?php

namespace App\Data;

use Plasticode\Data\Db as DbBase;

class Db extends DbBase
{
	protected function getProtected($table, $id, $where = null)
	{
		$user = $this->auth->getUser();
		
		if (!$user) {
		    return null;
		}
	    
		$editor = $this->can($table, 'edit');
		
		$where = $where ?? function($q) use ($id) {
			return $q->where('id', $id);
		};

		return $this->getBy($table, function($q) use ($where, $user, $editor) {
			$q = $where($q);

			if (!$editor) {
				$q = $q->where("created_by", $user->id);
			}
			
			return $q;
		});
	}

	public function getConnection($id) {
		return $this->get(Tables::CONNECTIONS, $id);
	}

	public function getFolder($id) {
		return $this->get(Tables::FOLDERS, $id);
	}

    public function getProjects()
    {
		$user = $this->auth->getUser();

		if (!$user) {
		    return null;
		}
		
    	return $this->getMany(Tables::PROJECTS, function($q) use ($user) {
    		return $q
    			->where('created_by', $user->id)
    			->orderByAsc('name');
    	});
    }

	public function getProject($id) {
		return $this->getProtected(Tables::PROJECTS, $id);
	}
    
    public function getSyncs($projectId = null)
    {
    	return $this->getMany(Tables::SYNCS, function($q) use ($projectId) {
    	    if ($projectId > 0) {
    	        $q = $q->where('project_id', $projectId);
    	    }
    	    
    		return $q->orderByAsc('name');
    	});
    }

	public function getSync($id) {
		return $this->get(Tables::SYNCS, $id);
	}
}
