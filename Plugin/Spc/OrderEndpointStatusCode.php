<?php

namespace Amazon\Pay\Plugin\Spc;

use Magento\Webapi\Controller\Rest;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\HttpInterface;

class OrderEndpointStatusCode
{
    /**
     * Set code HTTP 201 if successful creation
     *
     * @param FrontControllerInterface $controller
     * @param HttpInterface $response
     * @return HttpInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDispatch(
        Rest $controller,
        HttpInterface $response,
        RequestInterface $request
    )
    {
        // skipping this for now until the endpoint actually creates an order
//        if ($response->getStatusCode() == \Magento\Framework\Webapi\Response::HTTP_OK
//            && strpos($request->getPathInfo(), 'amazon-spc') !== false
//            && strpos($request->getPathInfo(), 'order') !== false) {
//            $response->setStatusCode(201);
//        }

        return $response;
    }
}
