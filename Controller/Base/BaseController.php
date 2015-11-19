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

namespace Dealer\Controller\Base;

use Dealer\Dealer;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Form\Exception\FormValidationException;

/**
 * Class BaseController
 * @package Dealer\Controller\Base
 */
abstract class BaseController extends BaseAdminController
{
    protected $useFallbackTemplate = true;
    /**
     * Name of entity associated with controller
     */
    const CONTROLLER_ENTITY_NAME = null;

    /**
     * Current Service Associated to controller
     */
    protected $service;


    // ABSTRACT FUNCTIONS

    /**
     * Use to get render of list
     * @return mixed
     */
    abstract protected function getListRenderTemplate();

    /**
     * Use to get Edit render
     * @return mixed
     */
    abstract protected function getEditRenderTemplate();

    /**
     * Use to get Create render
     * @return mixed
     */
    abstract protected function getCreateRenderTemplate();

    /**
     * @return mixed
     */
    abstract protected function getObjectId($object);


    // PUBLIC METHODS

    /**
     * The default action is displaying the list.
     *
     * @return \Thelia\Core\HttpFoundation\Response the response
     */
    public function defaultAction()
    {
        // Check current user authorization
        if (null !== $response = $this->checkAuth(AdminResources::MODULE, Dealer::getModuleCode(),
                AccessManager::VIEW)
        ) {
            return $response;
        }

        return $this->getListRenderTemplate();
    }

    /**
     * Create an object
     * @return mixed|\Symfony\Component\HttpFoundation\Response
     */
    public function createAction()
    {
        // Check current user authorization
        if (null !== $response = $this->checkAuth(AdminResources::MODULE, Dealer::getModuleCode(),
                AccessManager::VIEW)
        ) {
            return $response;
        }

        // Create the Creation Form
        $creationForm = $this->getCreationForm($this->getRequest());

        try {
            // Check the form against constraints violations
            $form = $this->validateForm($creationForm, "POST");
            // Get the form field values
            $data = $form->getData();

            $createdObject = $this->getService()->createFromArray($data, $this->getCurrentEditionLocale());


            // Substitute _ID_ in the URL with the ID of the created object
            $successUrl = str_replace('_ID_', $this->getObjectId($createdObject), $creationForm->getSuccessUrl());

            // Redirect to the success URL
            return $this->generateRedirect($successUrl);

        } catch (FormValidationException $ex) {
            // Form cannot be validated
            $error_msg = $this->createStandardFormValidationErrorMessage($ex);
        } catch (\Exception $ex) {
            // Any other error
            $error_msg = $ex->getMessage();
        }
        if (false !== $error_msg) {
            $this->setupFormErrorContext(
                $this->getTranslator()->trans("%obj creation", ['%obj' => static::CONTROLLER_ENTITY_NAME]),
                $error_msg,
                $creationForm,
                $ex
            );

            // At this point, the form has error, and should be redisplayed.
            return $this->getListRenderTemplate();
        }

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
                AccessManager::VIEW)
        ) {
            return $response;
        }

        try {
            // Check token
            $this->getTokenProvider()->checkToken(
                $this->getRequest()->query->get("_token")
            );

            $this->getService()->deleteFromId($this->getRequest()->request->get("dealer_id"));

            if ($response == null) {
                return $this->redirectToListTemplate();
            } else {
                return $response;
            }
        } catch (\Exception $e) {
            return $this->renderAfterDeleteError($e);
        }
    }

    // HELPERS
    /**
     * Method to get current controller associated service
     * @return object
     */
    protected function getService()
    {
        if (!$this->service) {
            $this->service = $this->getContainer()->get(static::CONTROLLER_ENTITY_NAME . "_service");
        }

        return $this->service;
    }

    /**
     * Method to get Base Creation Form
     * @return \Thelia\Form\BaseForm
     */
    protected function getCreationForm()
    {
        return $this->createForm(static::CONTROLLER_ENTITY_NAME . ".create");
    }

    /**
     * Method to get Base Update Form
     * @param array $data
     * @return \Thelia\Form\BaseForm
     */
    protected function getUpdateForm($data = [])
    {
        if (!is_array($data)) {
            $data = [];
        }

        return $this->createForm(static::CONTROLLER_ENTITY_NAME . ".update", $data);
    }

    /**
     * @param \Exception $e
     * @return \Thelia\Core\HttpFoundation\Response
     */
    protected function renderAfterDeleteError(\Exception $e)
    {
        $errorMessage = sprintf(
            "Unable to delete '%s'. Error message: %s",
            static::CONTROLLER_ENTITY_NAME,
            $e->getMessage()
        );

        $this->getParserContext()
            ->setGeneralError($errorMessage)
        ;

        return $this->defaultAction();
    }


}