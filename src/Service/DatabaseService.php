<?php

declare(strict_types=1);

namespace App\Service;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\TableGateway\Feature\FeatureSet;
use Laminas\Db\TableGateway\Feature\GlobalAdapterFeature;
use Laminas\Db\TableGateway\Feature\RowGatewayFeature;

class DatabaseService extends AbstractTableGateway
{
    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
        $this->table = 'notes';
        $this->featureSet = new FeatureSet();
        $this->featureSet->addFeature(new RowGatewayFeature('id'));

        $this->initialize();
    }

}
