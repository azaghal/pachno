<?php

    namespace pachno\core\entities\tables;

    use pachno\core\framework;

    /**
     * Workflow transition actions table
     *
     * @author Daniel Andre Eikeland <zegenie@zegeniestudios.net>
     * @version 3.1
     * @license http://opensource.org/licenses/MPL-2.0 Mozilla Public License 2.0 (MPL 2.0)
     * @package pachno
     * @subpackage tables
     */

    /**
     * Workflow transition actions table
     *
     * @package pachno
     * @subpackage tables
     *
     * @Table(name="workflow_transition_actions")
     * @Entity(class="\pachno\core\entities\WorkflowTransitionAction")
     */
    class WorkflowTransitionActions extends ScopedTable
    {

        const B2DB_TABLE_VERSION = 1;
        const B2DBNAME = 'workflow_transition_actions';
        const ID = 'workflow_transition_actions.id';
        const SCOPE = 'workflow_transition_actions.scope';
        const ACTION_TYPE = 'workflow_transition_actions.action_type';
        const TRANSITION_ID = 'workflow_transition_actions.transition_id';
        const WORKFLOW_ID = 'workflow_transition_actions.workflow_id';
        const TARGET_VALUE = 'workflow_transition_actions.target_value';

        public function getByTransitionID($transition_id)
        {
            $query = $this->getQuery();
            $query->where(self::SCOPE, framework\Context::getScope()->getID());
            $query->where(self::TRANSITION_ID, $transition_id);
            return $this->select($query);
        }

        protected function setupIndexes()
        {
            $this->addIndex('scope_transitionid', array(self::SCOPE, self::TRANSITION_ID));
        }

    }