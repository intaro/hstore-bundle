<?php

namespace Intaro\HStoreBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class IntaroHStoreBundle extends Bundle
{
    public function boot()
    {
        $em = $this->container->get('doctrine')->getManager();
        $platform = $em->getConnection()->getDatabasePlatform();
        $platform->registerDoctrineTypeMapping('hstore', 'hstore');
    }
}
