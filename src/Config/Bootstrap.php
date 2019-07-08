<?php

namespace App\Config;

use Plasticode\Config\Bootstrap as BootstrapBase;

class Bootstrap extends BootstrapBase
{
    public function getMappings()
    {
        $mappings = parent::getMappings();
        
        return array_merge(
            $mappings,
            [
                'dbClass' => function ($container) {
                    return \App\Data\Db::class;
                },

                'builder' => function ($container) {
                	return new \App\Core\Builder($container);
                },

                'cases' => function ($container) {
                	$cases = new \Plasticode\Util\Cases;

                	$cases->addCases([
            			'word' => 'синхронизация',
            			'base' => 'синхронизаци',
            			'index' => 'копия',
            		]);
                
                	return $cases;
                },

                'localization' => function ($container) {
                    return new \App\Config\Localization;
                },
                
                // own

                'transportConnector' => function ($container) {
                    return new \App\Sync\FtpConnector;
                },
                
                'syncConnectorFactory' => function ($container) {
                    return new \App\Sync\SyncConnectorFactory($container->transportConnector);
                },
                
                'synchronizer' => function ($container) {
                    $loader = new \App\Sync\FtpLoader;
                    $packageLoader = new \App\Sync\PackageLoader($loader);
                    
                    return new \App\Sync\Synchronizer($container->syncConnectorFactory, $packageLoader);
                },
                
                // handlers
                
                'notFoundHandler' => function ($container) {
                	return new \App\Handlers\NotFoundHandler($container);
                },
            ]
        );
    }
}
