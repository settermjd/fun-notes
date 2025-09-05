<?php

declare(strict_types=1);

namespace App\Service;

use PhpDb\Adapter\AdapterInterface;
use PhpDb\TableGateway\AbstractTableGateway;
use PhpDb\TableGateway\Feature\FeatureSet;
use PhpDb\TableGateway\Feature\RowGatewayFeature;

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

    public function noteExists(int $noteId): bool
    {
        return $this->select(
                [
                    'id' => $noteId
                ]
            )->current() !== null;
    }

}
