<?php

    namespace pachno\core\entities\common;

    use pachno\core\framework;

    /**
     * An identifiable class
     *
     * @author Daniel Andre Eikeland <zegenie@zegeniestudios.net>
     * @version 3.1
     * @license http://opensource.org/licenses/MPL-2.0 Mozilla Public License 2.0 (MPL 2.0)
     * @package pachno
     * @subpackage core
     */

    /**
     * An identifiable class
     *
     * @package pachno
     * @subpackage core
     */
    abstract class IdentifiableScoped extends Identifiable
    {

        /**
         * The related scope
         *
         * @var integer
         * @Column(type="integer", length=10)
         * @Relates(class="\pachno\core\entities\Scope")
         */
        protected $_scope;

        /**
         * Set the scope this item is in
         *
         * @param \pachno\core\entities\Scope $scope
         */
        public function setScope($scope)
        {
            $this->_scope = $scope;
        }

        /**
         * Retrieve the scope this item is in
         *
         * @return \pachno\core\entities\Scope
         */
        public function getScope()
        {
            if (!$this->_scope instanceof \pachno\core\entities\Scope)
                $this->_b2dbLazyLoad('_scope');

            return $this->_scope;
        }

        protected function getCurrentScope()
        {
            return framework\Context::getScope();
        }

        protected function getCurrentScopeID()
        {
            return framework\Context::getScope()->getID();
        }

        protected function _preSave($is_new)
        {
            if ($is_new && $this->_scope === null)
                $this->_scope = $this->getCurrentScope();
        }

    }
