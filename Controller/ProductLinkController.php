<?php
/*************************************************************************************/
/*      This file is part of the Thelia package.                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : dev@thelia.net                                                       */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/
/*************************************************************************************/

namespace Dealer\Controller;

use Dealer\Controller\Base\BaseController;
use Dealer\Dealer;
use Dealer\Model\DealerProduct;
use Propel\Runtime\Propel;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Tools\URL;

/**
 * Class ProductLinkController
 * @package Dealer\Controller
 */
class ProductLinkController extends BaseController
{
    const CONTROLLER_ENTITY_NAME = "dealer_product_link";
    const CONTROLLER_CHECK_RESOURCE = Dealer::RESOURCES_ASSOCIATED;
    /**
     * @inheritDoc
     */
    protected function getListRenderTemplate()
    {
        $id = $this->getRequest()->query->get("dealer_id");

        return new RedirectResponse(URL::getInstance()->absoluteUrl("/admin/module/Dealer/dealer/edit#associated",
            ["dealer_id" => $id, ]));
    }

    /**
     * @inheritDoc
     */
    protected function redirectToListTemplate()
    {
        $id = $this->getRequest()->request->get("dealer_id");

        return new RedirectResponse(URL::getInstance()->absoluteUrl("/admin/module/Dealer/dealer/edit#associated",
            ["dealer_id" => $id, ]));
    }

    /**
     * @inheritDoc
     */
    protected function getEditRenderTemplate()
    {
        // TODO: Implement getEditRenderTemplate() method.
    }

    /**
     * @inheritDoc
     */
    protected function getCreateRenderTemplate()
    {
        // TODO: Implement getCreateRenderTemplate() method.
    }

    /**
     * @inheritDoc
     */
    protected function getObjectId($object)
    {
        /** @var DealerProduct $object */
        return $object->getId();
    }

    /**
     * @inheritDoc
     */
    protected function getExistingObject()
    {
        // TODO: Implement getExistingObject() method.
    }

    /**
     * @inheritDoc
     */
    protected function hydrateObjectForm($object)
    {
        // TODO: Implement hydrateObjectForm() method.
    }

    /**
     * Method to get current controller associated service
     * @return object
     */
    protected function getService()
    {
        if (!$this->service) {
            $this->service = $this->getContainer()->get("dealer_product_link_service");
        }

        return $this->service;
    }

    /**
     * Method to get Base Creation Form
     * @return \Thelia\Form\BaseForm
     */
    protected function getCreationForm()
    {
        return $this->createForm(static::CONTROLLER_ENTITY_NAME . "_create");
    }

    /**
     * Delete an object
     *
     * @return \Thelia\Core\HttpFoundation\Response the response
     */
    public function deleteAction()
    {
        // Check current user authorization
        if (null !== $response = $this->checkAuth(AdminResources::MODULE, Dealer::getModuleCode(),
                AccessManager::DELETE)
        ) {
            return $response;
        }

        $con = Propel::getConnection();
        $con->beginTransaction();
        try {
            // Check token
            $this->getTokenProvider()->checkToken(
                $this->getRequest()->query->get("_token")
            );
            $data = [
                "product_id" => $this->getRequest()->request->get(static::CONTROLLER_ENTITY_NAME . "_id"),
                "dealer_id" => $this->getRequest()->request->get("dealer_id"),
            ];
            $this->getService()->deleteFromArray($data);
            $con->commit();

            if ($this->getRequest()->request->get("success_url") == null) {
                return $this->redirectToListTemplate();
            } else {
                return new RedirectResponse(URL::getInstance()->absoluteUrl($this->getRequest()->request->get("success_url")));
            }
        } catch (\Exception $e) {
            $con->rollBack();

            return $this->renderAfterDeleteError($e);
        }
    }
}
