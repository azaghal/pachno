<?php

    namespace pachno\core\entities;

    use b2db\Core;
    use b2db\Criterion;
    use b2db\Query;
    use pachno\core\entities\common\IdentifiableScoped;
    use pachno\core\entities\SavedSearch;
    use pachno\core\framework;
    use b2db\Criteria;

    /**
     * Search filter class
     *
     * @author Daniel Andre Eikeland <zegenie@zegeniestudios.net>
     * @version 3.0
     * @license http://opensource.org/licenses/MPL-2.0 Mozilla Public License 2.0 (MPL 2.0)
     * @package pachno
     * @subpackage core
     */

    /**
     * Search filter class
     *
     * @package pachno
     * @subpackage core
     *
     * @Table(name="\pachno\core\entities\tables\SavedSearchFilters")
     */
    class SearchFilter extends IdentifiableScoped implements \ArrayAccess
    {

        const VALUE = 'savedsearchfilters.value';
        const OPERATOR = 'savedsearchfilters.operator';
        const SEARCH_ID = 'savedsearchfilters.search_id';
        const FILTER_KEY = 'savedsearchfilters.filter_key';

        const FILTER_RELATION_NEITHER_CHILD_NOR_PARENT = 0;
        const FILTER_RELATION_WITHOUT_PARENT = 1;
        const FILTER_RELATION_ONLY_PARENT = 2;
        const FILTER_RELATION_WITHOUT_CHILD = 3;
        const FILTER_RELATION_ONLY_CHILD = 4;

        /**
         * The value of the filter
         *
         * @var string
         * @Column(type="string", length=200)
         */
        protected $_value;

        /**
         * The operator for the filter
         *
         * @var string
         * @Column(type="string", length=40)
         */
        protected $_operator = '=';

        /**
         * The filter key
         *
         * @var string
         * @Column(type="string", length=100)
         */
        protected $_filter_key;

        /**
         * The saved search this filter applies to
         *
         * @var SavedSearch
         * @Column(type="integer", length=10)
         * @Relates(class="\pachno\core\entities\SavedSearch")
         */
        protected $_search_id;

        /**
         * The related custom data type
         *
         * @var \pachno\core\entities\CustomDatatype
         */
        protected $_customtype;

        public static function createFilter($key, $options = [], SavedSearch $search = null)
        {
            if (isset($options['o'])) $options['operator'] = $options['o'];
            if (isset($options['v'])) $options['value'] = (is_array($options['v'])) ? implode(',', $options['v']) : $options['v'];

            $options = array_merge(['operator' => '=', 'value' => ''], $options);
            $filter = new \pachno\core\entities\SearchFilter();
            $filter->setFilterKey($key);
            $filter->setOperator($options['operator']);
            $filter->setValue($options['value']);
            $filter->setSearchId($search);

            return $filter;
        }

        public static function getValidSearchFilters()
        {
            return ['id', 'project_id', 'subprojects', 'text', 'state', 'issuetype', 'status', 'resolution', 'reproducability', 'category', 'severity', 'priority', 'posted_by', 'assignee_user', 'assignee_team', 'owner_user', 'owner_team', 'component', 'build', 'edition', 'posted', 'last_updated', 'milestone', 'blocking', 'votes_total', 'relation', 'time_spent'];
        }

        public static function getPredefinedFilters($type, SavedSearch $search)
        {
            $filters = [];
            switch ($type)
            {
                case SavedSearch::PREDEFINED_SEARCH_PROJECT_OPEN_ISSUES:
                case SavedSearch::PREDEFINED_SEARCH_PROJECT_OPEN_ISSUES_INCLUDING_SUBPROJECTS:
                    $filters['status'] = self::createFilter('status', array('operator' => '=', 'value' => 'open'), $search);
                    $filters['project_id'] = self::createFilter('project_id', array('operator' => '=', 'value' => framework\Context::getCurrentProject()->getID()), $search);
                    $types = array();
                    foreach (framework\Context::getCurrentProject()->getIssuetypeScheme()->getIssuetypes() as $issuetype)
                    {
                        if (in_array($issuetype->getType(), [Issuetype::TYPE_BUG, Issuetype::TYPE_TASK])) {
                            $types[] = $issuetype->getID();
                        }
                    }
                    if (count($types))
                    {
                        $filters['issuetype'] = self::createFilter('issuetype', array('operator' => '=', 'value' => join(',', $types)));
                    }
                    if ($type == SavedSearch::PREDEFINED_SEARCH_PROJECT_OPEN_ISSUES_INCLUDING_SUBPROJECTS) {
                        $filters['subprojects'] = self::createFilter('subprojects', array('operator' => '=', 'value' => 'all'), $search);
                    }
                    break;
                case SavedSearch::PREDEFINED_SEARCH_PROJECT_CLOSED_ISSUES:
                case SavedSearch::PREDEFINED_SEARCH_PROJECT_CLOSED_ISSUES_INCLUDING_SUBPROJECTS:
                    $filters['status'] = self::createFilter('status', array('operator' => '=', 'value' => 'closed'), $search);
                    $filters['project_id'] = self::createFilter('project_id', array('operator' => '=', 'value' => framework\Context::getCurrentProject()->getID()), $search);
                    if ($type == SavedSearch::PREDEFINED_SEARCH_PROJECT_CLOSED_ISSUES_INCLUDING_SUBPROJECTS) {
                        $filters['subprojects'] = self::createFilter('subprojects', array('operator' => '=', 'value' => 'all'), $search);
                    }
                    break;
                case SavedSearch::PREDEFINED_SEARCH_PROJECT_WISHLIST:
                    $filters['status'] = self::createFilter('status', array('operator' => '=', 'value' => 'open'), $search);
                    $filters['project_id'] = self::createFilter('project_id', array('operator' => '=', 'value' => framework\Context::getCurrentProject()->getID()), $search);
                    $types = array();
                    foreach (framework\Context::getCurrentProject()->getIssuetypeScheme()->getIssuetypes() as $issuetype)
                    {
                        if (in_array($issuetype->getType(), [Issuetype::TYPE_FEATURE, Issuetype::TYPE_ENHANCEMENT]))
                            $types[] = $issuetype->getID();
                    }
                    if (count($types))
                    {
                        $filters['issuetype'] = self::createFilter('issuetype', array('operator' => '=', 'value' => implode(',', $types)));
                    }
                    break;
                case SavedSearch::PREDEFINED_SEARCH_PROJECT_REPORTED_LAST_NUMBEROF_TIMEUNITS:
                    $filters['project_id'] = self::createFilter('project_id', array('operator' => '=', 'value' => framework\Context::getCurrentProject()->getID()), $search);
                    $units = framework\Context::getRequest()->getParameter('units');
                    switch (framework\Context::getRequest()->getParameter('time_unit'))
                    {
                        case 'seconds':
                            $time_unit = NOW - $units;
                            break;
                        case 'minutes':
                            $time_unit = NOW - (60 * $units);
                            break;
                        case 'hours':
                            $time_unit = NOW - (60 * 60 * $units);
                            break;
                        case 'days':
                            $time_unit = NOW - (86400 * $units);
                            break;
                        case 'weeks':
                            $time_unit = NOW - (86400 * 7 * $units);
                            break;
                        case 'months':
                            $time_unit = NOW - (86400 * 30 * $units);
                            break;
                        case 'years':
                            $time_unit = NOW - (86400 * 365 * $units);
                            break;
                        default:
                            $time_unit = NOW - (86400 * 30);
                    }
                    $filters['posted'] = self::createFilter('posted', array('operator' => '>=', 'value' => $time_unit), $search);
                    break;
                case SavedSearch::PREDEFINED_SEARCH_PROJECT_REPORTED_THIS_MONTH:
                    $filters['project_id'] = self::createFilter('project_id', array('operator' => '=', 'value' => framework\Context::getCurrentProject()->getID()), $search);
                    $filters['posted'] = self::createFilter('posted', array('operator' => '>=', 'value' => mktime(date('H'), date('i'), date('s'), date('n'), 1)), $search);
                    break;
                case SavedSearch::PREDEFINED_SEARCH_PROJECT_MILESTONE_TODO:
                    $filters['status'] = self::createFilter('status', array('operator' => '=', 'value' => 'open'), $search);
                    $filters['project_id'] = self::createFilter('project_id', array('operator' => '=', 'value' => framework\Context::getCurrentProject()->getID()), $search);
                    $filters['milestone'] = self::createFilter('milestone', array('operator' => '!=', 'value' => 0), $search);
                    break;
                case SavedSearch::PREDEFINED_SEARCH_PROJECT_MOST_VOTED:
                    $filters['project_id'] = self::createFilter('project_id', array('operator' => '=', 'value' => framework\Context::getCurrentProject()->getID()), $search);
                    $filters['status'] = self::createFilter('status', array('operator' => '=', 'value' => 'open'), $search);
                    $filters['votes_total'] = self::createFilter('votes_total', array('operator' => '>=', 'value' => '1'), $search);
                    break;
                case SavedSearch::PREDEFINED_SEARCH_MY_REPORTED_ISSUES:
                    $filters['posted_by'] = self::createFilter('posted_by', array('operator' => '=', 'value' => framework\Context::getUser()->getID()), $search);
                    break;
                case SavedSearch::PREDEFINED_SEARCH_MY_ASSIGNED_OPEN_ISSUES:
                    $filters['status'] = self::createFilter('status', array('operator' => '=', 'value' => 'open'), $search);
                    $filters['assignee_user'] = self::createFilter('assignee_user', array('operator' => '=', 'value' => framework\Context::getUser()->getID()), $search);
                    break;
                case SavedSearch::PREDEFINED_SEARCH_TEAM_ASSIGNED_OPEN_ISSUES:
                    $filters['status'] = self::createFilter('status', array('operator' => '=', 'value' => 'open'), $search);
                    $teams = array();
                    foreach (framework\Context::getUser()->getTeams() as $team_id => $team)
                    {
                        $teams[] = $team_id;
                    }
                    $filters['assignee_team'] = self::createFilter('assignee_team', array('operator' => '=', 'value' => join(',', $teams)), $search);
                    break;
                case SavedSearch::PREDEFINED_SEARCH_MY_OWNED_OPEN_ISSUES:
                    $filters['status'] = self::createFilter('status', array('operator' => '=', 'value' => 'open'), $search);
                    $filters['owner_user'] = self::createFilter('owner_user', array('operator' => '=', 'value' => framework\Context::getUser()->getID()), $search);
                    break;
            }

            return $filters;
        }

        public static function getFromRequest(\pachno\core\framework\Request $request, SavedSearch $search)
        {
            $filters = $request->getRawParameter('fs', array());
            if ($request['quicksearch'])
            {
                $filters['text']['o'] = '=';
            }
            if (framework\Context::isProjectContext())
            {
                $filters['project_id'] = array('o' => '=', 'v' => framework\Context::getCurrentProject()->getID());
            }

            $return_filters = array();
            foreach ($filters as $key => $details)
            {
                if (!isset($details['o']))
                {
                    foreach ($details as $i => $subdetails)
                    {
                        if (count($subdetails) == 1)
                        {
                            if (isset($subdetails['o']))
                            {
                                $v = (is_array($details[$i+1]['v'])) ? implode(',', $details[$i+1]['v']) : $details[$i+1]['v'];
                                $return_filters[$key][] = self::createFilter($key, array('o' => $subdetails['o'], 'v' => $v), $search);
                            }

                            continue;
                        }

                        $return_filters[$key][] = self::createFilter($key, $subdetails, $search);
                    }
                }
                else
                {
                    $return_filters[$key] = self::createFilter($key, $details, $search);
                }
            }

            return $return_filters;
        }

        /**
         * @param string $filter_key
         */
        public function setFilterKey($filter_key)
        {
            $this->_filter_key = $filter_key;
        }

        /**
         * @return string
         */
        public function getFilterKey()
        {
            return $this->_filter_key;
        }

        protected function _populateCustomtype()
        {
            if ($this->_customtype === null)
            {
                $this->_customtype = CustomDatatype::getByKey($this->getFilterKey());
            }
        }

        public function isCustomFilter()
        {
            return (!in_array($this->getFilterKey(), self::getValidSearchFilters()));
        }

        public function getFilterType()
        {
            $this->_populateCustomtype();
            return $this->_customtype->getType();
        }

        public function getFilterTitle()
        {
            $this->_populateCustomtype();
            return $this->_customtype->getDescription();
        }

        /**
         * @param string $operator
         */
        public function setOperator($operator)
        {
            $this->_operator = $operator;
        }

        /**
         * @return string
         */
        public function getOperator()
        {
            return $this->_operator;
        }

        /**
         * @param SavedSearch $search_id
         */
        public function setSearchId($search_id)
        {
            $this->_search_id = $search_id;
        }

        /**
         * @param SavedSearch $search
         */
        public function setSearch(SavedSearch $search)
        {
            $this->_search_id = $search;
        }

        /**
         * @return SavedSearch
         */
        public function getSearch()
        {
            return $this->_b2dbLazyLoad('_search_id');
        }

        /**
         * @param string $value
         */
        public function setValue($value)
        {
            $this->_value = $value;
        }

        /**
         * @return string
         */
        public function getValue()
        {
            return $this->_value;
        }

        public function hasExclusiveValues()
        {
            $this->_populateCustomtype();
            return in_array($this->_customtype->getType(), array(CustomDatatype::RADIO_CHOICE, CustomDatatype::COMPONENTS_CHOICE, CustomDatatype::EDITIONS_CHOICE, CustomDatatype::RELEASES_CHOICE, CustomDatatype::STATUS_CHOICE));
        }

        public function getValues()
        {
            if (!$this->hasValue()) return array();

            $values = (!is_array($this->_value)) ? explode(',', $this->_value) : $this->_value;
            return $values;
        }

        public function hasValue($value = null)
        {
            if ($value === null)
            {
                return $this->_value !== '';
            }
            else
            {
                if (!$this->hasValue()) return false;

                $values = explode(',', $this->_value);
                return in_array($value, $values);
            }
        }

        protected function _getAvailableComponentChoices()
        {
            if (framework\Context::isProjectContext()) return framework\Context::getCurrentProject()->getComponents();

            $components = array();
            foreach (\pachno\core\entities\Project::getAll() as $project)
            {
                foreach ($project->getComponents() as $component)
                    $components[$component->getID()] = $component;
            }

            return $components;
        }

        protected function _getAvailableMilestoneChoices()
        {
            if (framework\Context::isProjectContext()) return framework\Context::getCurrentProject()->getMilestones();

            $milestones = array();
            foreach (\pachno\core\entities\Project::getAll() as $project)
            {
                foreach ($project->getMilestones() as $milestone)
                    $milestones[$milestone->getID()] = $milestone;
            }

            return $milestones;
        }

        protected function _getAvailableBuildChoices()
        {
            if (framework\Context::isProjectContext()) return framework\Context::getCurrentProject()->getBuilds();

            $builds = array();
            foreach (\pachno\core\entities\Project::getAll() as $project)
            {
                foreach ($project->getBuilds() as $build)
                    $builds[$build->getID()] = $build;
            }

            return $builds;
        }

        protected function _getAvailableEditionChoices()
        {
            if (framework\Context::isProjectContext()) return framework\Context::getCurrentProject()->getEditions();

            $editions = array();
            foreach (\pachno\core\entities\Project::getAll() as $project)
            {
                foreach ($project->getEditions() as $edition)
                    $editions[$edition->getID()] = $edition;
            }

            return $editions;
        }

        protected function _getAvailableTeamChoices()
        {
            $teams = framework\Context::getUser()->getTeams();
            if (framework\Context::isProjectContext())
            {
                foreach (framework\Context::getCurrentProject()->getAssignedTeams() as $team)
                {
                    $teams[$team->getID()] = $team;
                }
            }
            return $teams;
        }

        protected function _getAvailableClientChoices()
        {
            $clients = tables\Clients::getTable()->getAll(10);
            foreach ($clients as $i => $client)
            {
                if (!$client->hasAccess()) unset($clients[$i]);
            }

            return $clients;
        }

        protected function _getAvailableUserChoices()
        {
            $me = framework\Context::getUser();
            $filters = array($me->getID() => $me);
            foreach ($me->getFriends() as $user)
            {
                $filters[$user->getID()] = $user;
            }
            if (count($this->getValues()))
            {
                $users = tables\Users::getTable()->getByUserIDs($this->getValues());
                foreach ($users as $user)
                {
                    $filters[$user->getID()] = $user;
                }
            }
            return $filters;
        }

        public function getAvailableValues()
        {
            switch ($this->getFilterKey())
            {
                case 'issuetype':
                    return (framework\Context::isProjectContext()) ? framework\Context::getCurrentProject()->getIssuetypeScheme()->getIssuetypes() : Issuetype::getAll();
                case 'status':
                    return Status::getAll();
                case 'category':
                    return Category::getAll();
                case 'priority':
                    return Priority::getAll();
                case 'severity':
                    return Severity::getAll();
                case 'reproducability':
                    return Reproducability::getAll();
                case 'resolution':
                    return Resolution::getAll();
                case 'project_id':
                    return \pachno\core\entities\Project::getAll();
                case 'build':
                    return $this->_getAvailableBuildChoices();
                case 'component':
                    return $this->_getAvailableComponentChoices();
                case 'edition':
                    return $this->_getAvailableEditionChoices();
                case 'milestone':
                    return $this->_getAvailableMilestoneChoices();
                case 'subprojects':
                    $filters = array();
                    $projects = Project::getIncludingAllSubprojectsAsArray(framework\Context::getCurrentProject());
                    foreach ($projects as $project)
                    {
                        if ($project->getID() == framework\Context::getCurrentProject()->getID()) continue;

                        $filters[$project->getID()] = $project;
                    }
                    return $filters;
                case 'owner_user':
                case 'assignee_user':
                case 'posted_by':
                    return $this->_getAvailableUserChoices();
                case 'owner_team':
                case 'assignee_team':
                    return $this->_getAvailableTeamChoices();
                default:
                    $customdatatype = CustomDatatype::getByKey($this->getFilterKey());
                    if ($customdatatype instanceof \pachno\core\entities\CustomDatatype && $customdatatype->hasCustomOptions())
                    {
                        return $customdatatype->getOptions();
                    }
                    else
                    {
                        switch ($this->getFilterType())
                        {
                            case CustomDatatype::COMPONENTS_CHOICE:
                                return $this->_getAvailableComponentChoices();
                            case CustomDatatype::RELEASES_CHOICE:
                                return $this->_getAvailableBuildChoices();
                            case CustomDatatype::EDITIONS_CHOICE:
                                return $this->_getAvailableEditionChoices();
                            case CustomDatatype::MILESTONE_CHOICE:
                                return $this->_getAvailableMilestoneChoices();
                            case CustomDatatype::USER_CHOICE:
                                return $this->_getAvailableUserChoices();
                            case CustomDatatype::TEAM_CHOICE:
                                return $this->_getAvailableTeamChoices();
                            case CustomDatatype::CLIENT_CHOICE:
                                return $this->_getAvailableClientChoices();
                            case CustomDatatype::STATUS_CHOICE:
                                return Status::getAll();
                            default:
                                return array();
                        }
                    }
            }
        }

        /**
         * (PHP 5 &gt;= 5.0.0)<br/>
         * Whether a offset exists
         * @link http://php.net/manual/en/arrayaccess.offsetexists.php
         * @param mixed $offset <p>
         * An offset to check for.
         * </p>
         * @return boolean true on success or false on failure.
         * </p>
         * <p>
         * The return value will be casted to boolean if non-boolean was returned.
         */
        public function offsetExists($offset)
        {
            return in_array($offset, array('operator', 'o', 'value', 'v', 'key', 'filter', 'filter_key'));
        }

        /**
         * (PHP 5 &gt;= 5.0.0)<br/>
         * Offset to retrieve
         * @link http://php.net/manual/en/arrayaccess.offsetget.php
         * @param mixed $offset <p>
         * The offset to retrieve.
         * </p>
         * @return mixed Can return all value types.
         */
        public function offsetGet($offset)
        {
            switch ($offset)
            {
                case 'operator':
                case 'o':
                    return $this->_operator;
                case 'value':
                case 'v':
                    return $this->_value;
                case 'key':
                case 'filter':
                case 'filter_key':
                    return $this->_filter_key;
            }
        }

        /**
         * (PHP 5 &gt;= 5.0.0)<br/>
         * Offset to set
         * @link http://php.net/manual/en/arrayaccess.offsetset.php
         * @param mixed $offset <p>
         * The offset to assign the value to.
         * </p>
         * @param mixed $value <p>
         * The value to set.
         * </p>
         * @return void
         */
        public function offsetSet($offset, $value)
        {
            switch ($offset)
            {
                case 'operator':
                    $this->_operator = $value;
                case 'value':
                    $this->_value = $value;
                case 'key':
                case 'filter':
                case 'filter_key':
                    $this->_filter_key = $value;
            }
        }

        /**
         * (PHP 5 &gt;= 5.0.0)<br/>
         * Offset to unset
         * @link http://php.net/manual/en/arrayaccess.offsetunset.php
         * @param mixed $offset <p>
         * The offset to unset.
         * </p>
         * @return void
         */
        public function offsetUnset($offset)
        {
            // TODO: Implement offsetUnset() method.
        }

        /**
         *
         * @param \b2db\Query $query
         * @param \pachno\core\entities\SearchFilter[] $filters
         * @param \b2db\Criteria $criteria
         * @return null
         */
        public function addToCriteria(Query $query, $filters, $criteria = null)
        {
            $filter_key = $this->getFilterKey();

            if (in_array($this['operator'], array(Criterion::EQUALS, Criterion::NOT_EQUALS, Criterion::GREATER_THAN, Criterion::GREATER_THAN_EQUAL, Criterion::LESS_THAN, Criterion::LESS_THAN_EQUAL)))
            {
                if ($filter_key == 'text')
                {
                    if ($this['value'] != '')
                    {
                        $searchterm = (mb_strpos($this['value'], '%') !== false) ? $this['value'] : "%{$this['value']}%";
                        $issue_no = Issue::extractIssueNoFromNumber($this['value']);
                        if ($this['operator'] == '=')
                        {
                            $comparison = (Core::getDriver() == 'pgsql') ? \b2db\Criterion::ILIKE : \b2db\Criterion::LIKE;
                            if ($criteria === null) {
                                $criteria = new Criteria();
                                $criteria->where(tables\Issues::TITLE, $searchterm, $comparison);
                            }
                            $criteria->or(tables\Issues::DESCRIPTION, $searchterm, $comparison);
                            $criteria->or(tables\Issues::REPRODUCTION_STEPS, $searchterm, $comparison);
                            $criteria->or(tables\IssueCustomFields::OPTION_VALUE, $searchterm, $comparison);
                            if (is_numeric($issue_no)) $criteria->or(tables\Issues::ISSUE_NO, $issue_no, \b2db\Criterion::EQUALS);
                        }
                        else
                        {
                            $comparison = (Core::getDriver() == 'pgsql') ? \b2db\Criterion::NOT_ILIKE : \b2db\Criterion::NOT_LIKE;
                            if ($criteria === null) {
                                $criteria = new Criteria();
                                $criteria->where(tables\Issues::TITLE, $searchterm, $comparison);
                            }
                            $criteria->or(tables\Issues::DESCRIPTION, $searchterm, $comparison);
                            $criteria->or(tables\Issues::REPRODUCTION_STEPS, $searchterm, $comparison);
                            $criteria->or(tables\IssueCustomFields::OPTION_VALUE, $searchterm, $comparison);
                            if (is_numeric($issue_no)) {
                                $criteria->or(tables\Issues::ISSUE_NO, $issue_no, \b2db\Criterion::EQUALS);
                            }
                        }
                        return $criteria;
                    }
                }
                elseif (in_array($filter_key, self::getValidSearchFilters()))
                {
                    if ($filter_key == 'subprojects')
                    {
                        if (framework\Context::isProjectContext())
                        {
                            if ($criteria === null) {
                                $criteria = new Criteria();
                                $criteria->where(tables\Issues::PROJECT_ID, framework\Context::getCurrentProject()->getID());
                            }
                            if ($this->hasValue())
                            {
                                foreach ($this->getValues() as $value)
                                {
                                    switch ($value)
                                    {
                                        case 'all':
                                            $subprojects = Project::getIncludingAllSubprojectsAsArray(framework\Context::getCurrentProject());
                                            foreach ($subprojects as $subproject)
                                            {
                                                if ($subproject->getID() == framework\Context::getCurrentProject()->getID()) continue;
                                                $criteria->or(tables\Issues::PROJECT_ID, $subproject->getID());
                                            }
                                            break;
                                        case 'none':
                                        case '':
                                            break;
                                        default:
                                            $criteria->or(tables\Issues::PROJECT_ID, (int) $value);
                                            break;
                                    }
                                }
                            }
                            return $criteria;
                        }
                    }
                    elseif (in_array($filter_key, array('build', 'edition', 'component', 'relation', 'time_spent')))
                    {
                        switch ($filter_key)
                        {
                            case 'component':
                                $table = tables\IssueAffectsComponent::getTable();
                                $foreign_key  = tables\IssueAffectsComponent::ISSUE;
                                break;
                            case 'edition':
                                $table = tables\IssueAffectsEdition::getTable();
                                $foreign_key  = tables\IssueAffectsEdition::ISSUE;
                                break;
                            case 'build':
                                $table = tables\IssueAffectsBuild::getTable();
                                $foreign_key  = tables\IssueAffectsBuild::ISSUE;
                                break;
                            case 'relation':
                                if ($this->hasValue(self::FILTER_RELATION_ONLY_CHILD))
                                {
                                    $query->join(tables\IssueRelations::getTable(), tables\IssueRelations::CHILD_ID, tables\Issues::ID, [], \b2db\Join::INNER);
                                }
                                else if ($this->hasValue(self::FILTER_RELATION_WITHOUT_CHILD))
                                {
                                    $query->join(tables\IssueRelations::getTable(), tables\IssueRelations::CHILD_ID, tables\Issues::ID);
                                    $criteria = new Criteria();
                                    $criteria->where(tables\IssueRelations::CHILD_ID, '', \b2db\Criterion::IS_NULL);
                                    return $criteria;
                                }
                                else if ($this->hasValue(self::FILTER_RELATION_ONLY_PARENT))
                                {
                                    $query->join(tables\IssueRelations::getTable(), tables\IssueRelations::PARENT_ID, tables\Issues::ID, array(), \b2db\Join::INNER);
                                }
                                else if ($this->hasValue(self::FILTER_RELATION_WITHOUT_PARENT))
                                {
                                    $query->join(tables\IssueRelations::getTable(), tables\IssueRelations::PARENT_ID, tables\Issues::ID);
                                    $criteria = new Criteria();
                                    $criteria->where(tables\IssueRelations::PARENT_ID, '', \b2db\Criterion::IS_NULL);
                                    return $criteria;
                                }
                                else if ($this->hasValue(self::FILTER_RELATION_NEITHER_CHILD_NOR_PARENT))
                                {
                                    $query->join(tables\IssueRelations::getTable(), tables\IssueRelations::CHILD_ID, tables\Issues::ID);
                                    $query->join(tables\IssueRelations::getTable(), tables\IssueRelations::PARENT_ID, tables\Issues::ID);
                                    $query->where(tables\IssueRelations::CHILD_ID, '', \b2db\Criterion::IS_NULL);
                                    $criteria = new Criteria();
                                    $criteria->where(tables\IssueRelations::PARENT_ID, '', \b2db\Criterion::IS_NULL);
                                    return $criteria;
                                }
                                return null;
                                break;
                            case 'time_spent':
                                $query->join(tables\IssueSpentTimes::getTable(), tables\IssueSpentTimes::ISSUE_ID, tables\Issues::ID);
                                $query->addSelectionColumn(tables\IssueSpentTimes::SPENT_MINUTES, 'spent_minutes_sum', \b2db\Query::DB_SUM);
                                $query->addSelectionColumn(tables\IssueSpentTimes::SPENT_HOURS, 'spent_hours_sum', \b2db\Query::DB_SUM);
                                $query->addSelectionColumn(tables\IssueSpentTimes::SPENT_DAYS, 'spent_days_sum', \b2db\Query::DB_SUM);
                                $query->addSelectionColumn(tables\IssueSpentTimes::SPENT_WEEKS, 'spent_weeks_sum', \b2db\Query::DB_SUM);
                                $query->addSelectionColumn(tables\IssueSpentTimes::SPENT_MONTHS, 'spent_months_sum', \b2db\Query::DB_SUM);
                                $query->addGroupBy(tables\Issues::ID);
                                $criteria = new Criteria();
                                $criteria->where(tables\IssueSpentTimes::EDITED_AT, $this->_value, $this->_operator);
                                return $criteria;
                                break;
                        }
                        $query->join($table, $foreign_key, tables\Issues::ID, array(array($table->getB2DBAlias().'.'.$filter_key, $this->getValues())), \b2db\Join::INNER);
                        return null;
                    }
                    else
                    {
                        if ($filter_key == 'project_id' && in_array('subprojects', $filters, true)) return null;

                        $values = $this->getValues();
                        $num_values = 0;

                        if ($filter_key == 'status')
                        {
                            if ($this->hasValue('open'))
                            {
                                $c = new Criteria();
                                $c->where(tables\Issues::STATE, Issue::STATE_OPEN);
                                $num_values++;
                            }
                            if ($this->hasValue('closed'))
                            {
                                $num_values++;
                                if (isset($c)) {
                                    $c->where(tables\Issues::STATE, Issue::STATE_CLOSED);
                                } else {
                                    $c = new Criteria();
                                    $c->where(tables\Issues::STATE, Issue::STATE_CLOSED);
                                }
                            }

                            if (isset($c))
                            {
                                if (count($values) == $num_values) {
                                    return $c;
                                } else {
                                    $query->where($c);
                                }
                            }
                        }

                        $dbname = tables\Issues::getTable()->getB2DBName();

                        foreach ($values as $value)
                        {
                            $operator = $this['operator'];
                            $or = true;
                            if ($filter_key == 'status' && in_array($value, array('open', 'closed')))
                            {
                                continue;
                            }
                            else
                            {
                                $field = $dbname.'.'.$filter_key;
                                if ($operator == '!=' || in_array($filter_key, array('posted', 'last_updated')))
                                {
                                    $or = false;
                                }
                            }
                            if ($criteria === null)
                            {
                                $criteria = new Criteria();
                                $criteria->where($field, $value, urldecode($operator));
                            }
                            elseif ($or)
                            {
                                $criteria->or($field, $value, urldecode($operator));
                            }
                            else
                            {
                                $criteria->where($field, $value, urldecode($operator));
                            }
                        }

                        return $criteria;
                    }
                }
                elseif (CustomDatatype::doesKeyExist($filter_key))
                {
                    $customdatatype = CustomDatatype::getByKey($filter_key);
                    if (in_array($this->getFilterType(), CustomDatatype::getInternalChoiceFieldsAsArray()))
                    {
                        $table = clone tables\IssueCustomFields::getTable();
                        $query->join($table, tables\IssueCustomFields::ISSUE_ID, tables\Issues::ID, array(array($table->getB2DBAlias().'.customfields_id', $customdatatype->getID()), array($table->getB2DBAlias().'.customfieldoption_id', $this->getValues())), \b2db\Join::INNER);
                        return null;
                    }
                    else
                    {
                        foreach ($this->getValues() as $value)
                        {
                            if ($customdatatype->hasCustomOptions())
                            {
                                if ($criteria === null)
                                {
                                    $criteria = new Criteria();
                                    $criteria->where(tables\IssueCustomFields::CUSTOMFIELDS_ID, $customdatatype->getID());
                                    $criteria->where(tables\IssueCustomFields::CUSTOMFIELDOPTION_ID, $value, $this['operator']);
                                }
                                else
                                {
                                    $criteria->or(tables\IssueCustomFields::CUSTOMFIELDOPTION_ID, $value, $this['operator']);
                                }
                            }
                            else
                            {
                                if ($criteria === null)
                                {
                                    $criteria = new Criteria();
                                    $criteria->where(tables\IssueCustomFields::CUSTOMFIELDS_ID, $customdatatype->getID());
                                    $criteria->where(tables\IssueCustomFields::OPTION_VALUE, $value, $this['operator']);
                                }
                                else
                                {
                                    $criteria->or(tables\IssueCustomFields::OPTION_VALUE, $value, $this['operator']);
                                }
                            }
                        }
                        return $criteria;
                    }
                }
            }
        }

        public function getName()
        {
            return $this->_filter_key;
        }

    }
