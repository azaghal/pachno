<?php

namespace pachno\core\modules\main\controllers;

use pachno\core\framework,
    pachno\core\entities,
    pachno\core\entities\tables,
    pachno\core\modules\agile;

/**
 * actions for the main module
 */
class Common extends framework\Action
{

    /**
     * About page
     *
     * @param \pachno\core\framework\Request $request
     */
    public function runAbout(framework\Request $request)
    {
        $this->forward403unless($this->getUser()->hasPageAccess('about'));
    }

    /**
     * 404 not found page
     *
     * @Route(name="notfound", url="/404")
     * @param \pachno\core\framework\Request $request
     */
    public function runNotFound(framework\Request $request)
    {
        $this->getResponse()->setHttpStatus(404);
        $message = null;
    }

    /**
     * 403 forbidden page
     *
     * @param \pachno\core\framework\Request $request
     */
    public function runForbidden(framework\Request $request)
    {
        $this->getResponse()->setHttpStatus(403);
        $this->getResponse()->setTemplate('main/forbidden');
    }

    /**
     * Logs the user out
     *
     * @param \pachno\core\framework\Request $request
     *
     * @return bool
     */
    public function runLogout(framework\Request $request)
    {
        if ($this->getUser() instanceof entities\User)
        {
            framework\Logging::log('Setting user logout state');
            $this->getUser()->setOffline();
            $this->getUser()->save();
        }
        framework\Context::logout();
        if ($request->isAjaxCall())
        {
            return $this->renderJSON(array('status' => 'logout ok', 'url' => framework\Context::getRouting()->generate(framework\Settings::getLogoutReturnRoute())));
        }
        $this->forward(framework\Context::getRouting()->generate(framework\Settings::getLogoutReturnRoute()));
    }

}
