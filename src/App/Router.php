<?php

namespace App;

use App\Entity\AbstractDbEntity;
use App\Exchange\Request;
use App\Exchange\Response;

class Router
{
    public function executeAction()
    {
        $request = new Request();

        $requestEntity = $request->getRequestEntity();
        $elementId = $request->getRequestElementId();
        $requestData = $request->getRequestData();
        $requestAction = $request->getRequestAction();

        /**
         * @var AbstractDbEntity $entity
         */
        $entity = new $requestEntity($elementId, $requestData);
        $entity->$requestAction();
    }
}
