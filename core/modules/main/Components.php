<?php

    namespace pachno\core\modules\main;

    use pachno\core\entities\Comment;
    use pachno\core\entities\LogItem;
    use pachno\core\framework,
        pachno\core\entities,
        pachno\core\entities\tables;
    use pachno\core\framework\interfaces\AuthenticationProvider;
    use PragmaRX\Google2FA\Google2FA;

    /**
     * Main action components
     *
     * @property entities\User $user
     * @property entities\Issue $issue
     * @property entities\Client $client
     * @property entities\Team $team
     * @property entities\Issue[] $issues
     * @property entities\Project $project
     * @property entities\WorkflowTransition $transition
     * @property entities\LogItem $item
     *
     */
    class Components extends framework\ActionComponent
    {

        public function componentIssueLogItem()
        {
            $this->showtrace = (date('YmdHis', $this->previous_time) != date('YmdHis', $this->item->getTime()));
        }

        public function componentIssueMoreActions()
        {
            if (!isset($this->show_workflow_transitions)) {
                $this->show_workflow_transitions = true;
            }
            if (!isset($this->multi)) {
                $this->multi = false;
            }
        }

        public function componentUserdropdown()
        {
            framework\Logging::log('user dropdown component');
            $this->rnd_no = rand();
            try
            {
                if (!$this->user instanceof entities\User)
                {
                    framework\Logging::log('loading user object in dropdown');
                    if (is_numeric($this->user))
                    {
                        $this->user = tables\Users::getTable()->getByUserId($this->user);
                    }
                    else
                    {
                        $this->user = tables\Users::getTable()->getByUsername($this->user);
                    }
                    framework\Logging::log('done (loading user object in dropdown)');
                }
            }
            catch (\Exception $e)
            {

            }
            $this->show_avatar = (isset($this->show_avatar)) ? $this->show_avatar : true;
            framework\Logging::log('done (user dropdown component)');
        }

        public function componentUserdropdown_Inline()
        {
            $this->componentUserdropdown();
        }

        public function componentClientusers()
        {
            try
            {
                if (!$this->client instanceof entities\Client)
                {
                    framework\Logging::log('loading user object in dropdown');
                    $this->client = entities\Client::getB2DBTable()->selectById($this->client);
                    framework\Logging::log('done (loading user object in dropdown)');
                }
                $this->clientusers = $this->client->getMembers();
            }
            catch (\Exception $e)
            {

            }
        }

        public function componentTeamdropdown()
        {
            framework\Logging::log('team dropdown component');
            $this->rnd_no = rand();
            try
            {
                $this->team = (isset($this->team)) ? $this->team : null;
                if (!$this->team instanceof entities\Team)
                {
                    framework\Logging::log('loading team object in dropdown');
                    $this->team = entities\Team::getB2DBTable()->selectById($this->team);
                    framework\Logging::log('done (loading team object in dropdown)');
                }
            }
            catch (\Exception $e)
            {

            }
            framework\Logging::log('done (team dropdown component)');
        }

        public function componentIdentifiableselector()
        {
            $this->include_teams = (isset($this->include_teams)) ? $this->include_teams : false;
            $this->include_clients = (isset($this->include_clients)) ? $this->include_clients : false;
            $this->include_users = (isset($this->include_users)) ? $this->include_users : true;
            $this->callback = (isset($this->callback)) ? $this->callback : null;
            $this->allow_clear = (isset($this->allow_clear)) ? $this->allow_clear : true;
            $this->use_form = (isset($this->use_form)) ? $this->use_form : true;
        }

        public function componentIdentifiableselectorresults()
        {
            $this->include_teams = (framework\Context::getRequest()->hasParameter('include_teams')) ? framework\Context::getRequest()->getParameter('include_teams') : false;
            $this->include_clients = (framework\Context::getRequest()->hasParameter('include_clients')) ? framework\Context::getRequest()->getParameter('include_clients') : false;
        }

        public function componentMyfriends()
        {
            $this->friends = framework\Context::getUser()->getFriends();
        }

        protected function setupVariables()
        {
            $i18n = framework\Context::getI18n();
            if ($this->issue instanceof entities\Issue)
            {
                $this->project = $this->issue->getProject();
                $this->statuses = ($this->project->useStrictWorkflowMode()) ? $this->project->getAvailableStatuses() : $this->issue->getAvailableStatuses();
                $this->issuetypes = $this->project->getIssuetypeScheme()->getIssuetypes();
                $fields_list = [];
                $fields_list['category'] = ['title' => $i18n->__('Category'), 'fa_icon' => 'chart-pie', 'fa_icon_style' => 'fas', 'choices' => [], 'visible' => $this->issue->isCategoryVisible(), 'value' => (($this->issue->getCategory() instanceof entities\Category) ? $this->issue->getCategory()->getId() : 0), 'icon' => false, 'change_tip' => $i18n->__('Click to change category'), 'change_header' => $i18n->__('Change category'), 'clear' => $i18n->__('Clear the category'), 'select' => $i18n->__('%clear_the_category or click to select a new category', ['%clear_the_category' => ''])];

                if ($this->issue->isUpdateable() && $this->issue->canEditCategory()) {
                    $fields_list['category']['choices'] = entities\Category::getAll();
                }

                $fields_list['resolution'] = ['title' => $i18n->__('Resolution'), 'choices' => [], 'visible' => $this->issue->isResolutionVisible(), 'value' => (($this->issue->getResolution() instanceof entities\Resolution) ? $this->issue->getResolution()->getId() : 0), 'icon' => false, 'change_tip' => $i18n->__('Click to change resolution'), 'change_header' => $i18n->__('Change resolution'), 'clear' => $i18n->__('Clear the resolution'), 'select' => $i18n->__('%clear_the_resolution or click to select a new resolution', ['%clear_the_resolution' => ''])];

                if ($this->issue->isUpdateable() && $this->issue->canEditResolution()) {
                    $fields_list['resolution']['choices'] = entities\Resolution::getAll();
                }

                $has_priority = $this->issue->getPriority() instanceof entities\Priority;
                $fields_list['priority'] = ['title' => $i18n->__('Priority'), 'choices' => [], 'visible' => $this->issue->isPriorityVisible(), 'extra_classes' => (($has_priority) ? 'priority_' . $this->issue->getPriority()->getItemdata() : ''), 'value' => (($has_priority) ? $this->issue->getPriority()->getId() : 0), 'fa_icon' => (($has_priority) ? $this->issue->getPriority()->getFontAwesomeIcon() : ''), 'fa_icon_style' => (($has_priority) ? $this->issue->getPriority()->getFontAwesomeIconStyle() : ''), 'icon' => false, 'change_tip' => $i18n->__('Click to change priority'), 'change_header' => $i18n->__('Change priority'), 'clear' => $i18n->__('Clear the priority'), 'select' => $i18n->__('%clear_the_priority or click to select a new priority', ['%clear_the_priority' => ''])];

                if ($this->issue->isUpdateable() && $this->issue->canEditPriority()) {
                    $fields_list['priority']['choices'] = entities\Priority::getAll();
                }

                $fields_list['reproducability'] = ['title' => $i18n->__('Reproducability'), 'choices' => [], 'visible' => $this->issue->isReproducabilityVisible(), 'value' => (($this->issue->getReproducability() instanceof entities\Reproducability) ? $this->issue->getReproducability()->getId() : 0), 'icon' => false, 'change_tip' => $i18n->__('Click to change reproducability'), 'change_header' => $i18n->__('Change reproducability'), 'clear' => $i18n->__('Clear the reproducability'), 'select' => $i18n->__('%clear_the_reproducability or click to select a new reproducability', ['%clear_the_reproducability' => ''])];

                if ($this->issue->isUpdateable() && $this->issue->canEditReproducability()) {
                    $fields_list['reproducability']['choices'] = entities\Reproducability::getAll();
                }

                $fields_list['severity'] = ['title' => $i18n->__('Severity'), 'choices' => [], 'visible' => $this->issue->isSeverityVisible(), 'value' => (($this->issue->getSeverity() instanceof entities\Severity) ? $this->issue->getSeverity()->getId() : 0), 'icon' => false, 'change_tip' => $i18n->__('Click to change severity'), 'change_header' => $i18n->__('Change severity'), 'clear' => $i18n->__('Clear the severity'), 'select' => $i18n->__('%clear_the_severity or click to select a new severity', ['%clear_the_severity' => ''])];

                if ($this->issue->isUpdateable() && $this->issue->canEditSeverity()) {
                    $fields_list['severity']['choices'] = entities\Severity::getAll();
                }

                $fields_list['milestone'] = ['title' => $i18n->__('Targetted for'), 'fa_icon' => 'list-alt', 'fa_style' => 'far', 'choices' => [], 'visible' => $this->issue->isMilestoneVisible(), 'value' => (($this->issue->getMilestone() instanceof entities\Milestone) ? $this->issue->getMilestone()->getId() : 0), 'icon' => true, 'icon_name' => 'icon_milestones.png', 'change_tip' => $i18n->__('Click to change which milestone this issue is targetted for'), 'change_header' => $i18n->__('Set issue target / milestone'), 'clear' => $i18n->__('Set as not targetted'), 'select' => $i18n->__('%set_as_not_targetted or click to set a new target milestone', ['%set_as_not_targetted' => '']), 'url' => true, 'current_url' => (($this->issue->getMilestone() instanceof entities\Milestone) ? $this->getRouting()->generate('project_roadmap', ['project_key' => $this->issue->getProject()->getKey()]).'#roadmap_milestone_'.$this->issue->getMilestone()->getID() : '')];

                if ($this->issue->isUpdateable() && $this->issue->canEditMilestone()) {
                    $fields_list['milestone']['choices'] = $this->project->getMilestonesForIssues();
                }

                $customfields_list = [];
                foreach (entities\CustomDatatype::getAll() as $key => $customdatatype)
                {
                    $customvalue = $this->issue->getCustomField($key);
                    $customfields_list[$key] = ['type' => $customdatatype->getType(),
                        'title' => $i18n->__($customdatatype->getDescription()),
                        'visible' => $this->issue->isFieldVisible($key),
                        'editable' => $customdatatype->isEditable(),
                        'change_tip' => $i18n->__($customdatatype->getInstructions()),
                        'change_header' => $i18n->__($customdatatype->getDescription()),
                        'clear' => $i18n->__('Clear this field'),
                        'select' => $i18n->__('%clear_this_field or click to set a new value', ['%clear_this_field' => ''])];

                    if ($customdatatype->getType() == entities\CustomDatatype::CALCULATED_FIELD) {
                        $result = $this->issue->getCustomField($key);
                        $customfields_list[$key]['value'] = $result;
                    } elseif ($customdatatype->hasCustomOptions()) {
                        $customfields_list[$key]['value'] = ($customvalue instanceof entities\CustomDatatypeOption) ? $customvalue->getId() : 0;
                        $customfields_list[$key]['choices'] = $customdatatype->getOptions();
                    } elseif ($customdatatype->hasPredefinedOptions()) {
                        $customfields_list[$key]['value'] = ($customvalue instanceof entities\common\Identifiable) ? $customvalue->getId() : '';
                        $customfields_list[$key]['identifiable'] = ($customvalue instanceof entities\common\Identifiable) ? $customvalue : null;
                        $customfields_list[$key]['choices'] = $customdatatype->getOptions();
                    } else {
                        $customfields_list[$key]['value'] = $customvalue;
                    }
                }
                $this->customfields_list = $customfields_list;
                $this->editions = ($this->issue->getProject()->isEditionsEnabled()) ? $this->issue->getEditions() : array();
                $this->components = ($this->issue->getProject()->isComponentsEnabled()) ? $this->issue->getComponents() : array();
                $this->builds = ($this->issue->getProject()->isBuildsEnabled()) ? $this->issue->getBuilds() : array();
                $this->affected_count = count($this->editions) + count($this->components) + count($this->builds);
            }
            else
            {
                $fields_list = [];
                $fields_list['category'] = ['choices' => entities\Category::getAll()];
                $fields_list['resolution'] = ['choices' => entities\Resolution::getAll()];
                $fields_list['priority'] = ['choices' => entities\Priority::getAll()];
                $fields_list['reproducability'] = ['choices' => entities\Reproducability::getAll()];
                $fields_list['severity'] = ['choices' => entities\Severity::getAll()];
                $fields_list['milestone'] = ['choices' => $this->project->getMilestonesForIssues()];

                if (isset($this->issues)) {
                    $all_statuses = [];
                    $project_statuses = $this->project->getAvailableStatuses();
                    foreach ($this->issues as $issue) {
                        $statuses = ($this->project->useStrictWorkflowMode()) ? $project_statuses : $issue->getAvailableStatuses();
                        foreach ($statuses as $status_id => $status) {
                            $all_statuses[$status_id] = $status;
                        }
                    }
                    $this->statuses = $all_statuses;
                }

            }

            $this->fields_list = $fields_list;
            if (isset($this->transition) && $this->transition->hasAction(entities\WorkflowTransitionAction::ACTION_ASSIGN_ISSUE))
            {
                $available_assignees = array();
                foreach (framework\Context::getUser()->getTeams() as $team)
                {
                    foreach ($team->getMembers() as $user)
                    {
                        $available_assignees[$user->getID()] = $user->getNameWithUsername();
                    }
                }
                foreach (framework\Context::getUser()->getFriends() as $user)
                {
                    $available_assignees[$user->getID()] = $user->getNameWithUsername();
                }
                $this->available_assignees = $available_assignees;
            }
        }

        public function componentViewIssueFields()
        {
            $this->setupVariables();
        }

        public function componentIssuemaincustomfields()
        {
            $this->setupVariables();
        }

        public function componentHideableInfoBox()
        {
            $this->show_box = framework\Settings::isInfoBoxVisible($this->key);
        }

        public function componentHideableInfoBoxModal()
        {
            if (!isset($this->options))
                $this->options = array();
            if (!isset($this->button_label))
                $this->button_label = $this->getI18n()->__('Hide');
            $this->show_box = framework\Settings::isInfoBoxVisible($this->key);
        }

        public function componentUploader()
        {
            switch ($this->mode)
            {
                case 'issue':
                    $this->issue = entities\Issue::getB2DBTable()->selectById($this->issue_id);
                    break;
                case 'article':
                    $this->article = entities\Article::getByName($this->article_name);
                    break;
                default:
                    // @todo: dispatch a framework\Event that allows us to retrieve the
                    // necessary variables from anyone catching it
                    break;
            }
        }

        public function componentDynamicUploader()
        {
            switch (true)
            {
                case isset($this->issue):
                    $this->target = $this->issue;
                    $this->existing_files = array_reverse($this->issue->getFiles());
                    break;
                case isset($this->article):
                    $this->target = $this->article;
                    $this->existing_files = array_reverse($this->article->getFiles());
                    break;
                default:
                    // @todo: dispatch a framework\Event that allows us to retrieve the
                    // necessary variables from anyone catching it
                    break;
            }
        }

        public function componentStandarduploader()
        {
            switch ($this->mode)
            {
                case 'issue':
                    $this->form_action = make_url('issue_upload', array('issue_id' => $this->issue->getID()));
                    $this->poller_url = make_url('issue_upload_status', array('issue_id' => $this->issue->getID()));
                    $this->existing_files = array_reverse($this->issue->getFiles());
                    break;
                case 'article':
                    $this->form_action = make_url('article_upload', array('article_name' => $this->article->getName()));
                    $this->poller_url = make_url('article_upload_status', array('article_name' => $this->article->getName()));
                    $this->existing_files = array_reverse($this->article->getFiles());
                    break;
                default:
                    // @todo: dispatch a framework\Event that allows us to retrieve the
                    // necessary variables from anyone catching it
                    break;
            }
        }

        public function componentAttachedfile()
        {
            if ($this->mode == 'issue' && !isset($this->issue))
            {
                $this->issue = entities\Issue::getB2DBTable()->selectById($this->issue_id);
            }
            elseif ($this->mode == 'article' && !isset($this->article))
            {
                $this->article = entities\Article::getByName($this->article_name);
            }
            $this->file_id = $this->file->getID();
        }

        public function componentUpdateissueproperties()
        {
            $this->issue = $this->issue ? : null;
            $this->setupVariables();
        }

        public function componentRelateissue()
        {

        }

        public function componentNotifications()
        {
            $this->filter_first_notification = ! is_null($this->first_notification_id) && is_numeric($this->first_notification_id);
            $notifications = $this->getUser()->getNotifications($this->first_notification_id, $this->last_notification_id);
            if ($this->filter_first_notification)
            {
                $this->notifications = $notifications;
            }
            else
            {
                $this->notifications = count($notifications) ? array_slice($notifications, 0, 25) : array();
            }
            $this->num_unread = $this->getUser()->getNumberOfUnreadNotifications();
            $this->num_read = $this->getUser()->getNumberOfReadNotifications();
            $this->desktop_notifications_new_tab = $this->getUser()->isDesktopNotificationsNewTabEnabled();
        }

        public function componentNotification_text()
        {
            $this->return_notification = true;

            if ($this->notification->isShown())
            {
                $this->return_notification = false;
            }
            else
            {
                $this->notification->showOnce();
                $this->notification->save();
            }
        }

        public function componentFindduplicateissues()
        {
            $this->setupVariables();
        }

        public function componentFindrelatedissues()
        {

        }

        public function componentLogitem()
        {
            if (!isset($this->include_issue_title)) {
                $this->include_issue_title = true;
            }
            if (!isset($this->include_time)) {
                $this->include_time = $this->include_issue_title;
            }
            if (!isset($this->include_project)) {
                $this->include_project = false;
            }
        }

        public function componentComments()
        {
            $this->comment_count = Comment::countComments($this->target_id, $this->target_type);
        }

        public function componentCommentitem()
        {
            if ($this->comment->getTargetType() == Comment::TYPE_ISSUE) {
                try {
                    $this->issue = entities\Issue::getB2DBTable()->selectById($this->comment->getTargetID());
                } catch (\Exception $e) { }
            }
        }

        public function componentUsercard()
        {
            $this->rnd_no = rand();
            $this->issues = $this->user->getIssues();
        }

        public function componentIssueaffected()
        {
            $this->editions = ($this->issue->getProject()->isEditionsEnabled()) ? $this->issue->getEditions() : array();
            $this->components = ($this->issue->getProject()->isComponentsEnabled()) ? $this->issue->getComponents() : array();
            $this->builds = ($this->issue->getProject()->isBuildsEnabled()) ? $this->issue->getBuilds() : array();
            $this->statuses = entities\Status::getAll();
            $this->count = count($this->editions) + count($this->components) + count($this->builds);
        }

        public function componentRelatedissues()
        {
            $this->child_issues = $this->issue->getChildIssues();
        }

        public function componentDuplicateissues()
        {
            $this->duplicate_issues = $this->issue->getDuplicateIssues();
        }

        public function componentLoginpopup()
        {
            if (framework\Context::getRequest()->getParameter('redirect') == true)
                $this->mandatory = true;
        }

        public function componentLogin()
        {
            $this->selected_tab = isset($this->section) ? $this->section : 'login';
            $this->options = $this->getParameterHolder();

            if (framework\Context::hasMessage('login_referer')):
                $this->referer = htmlentities(framework\Context::getMessage('login_referer'), ENT_COMPAT, framework\Context::getI18n()->getCharset());
            elseif (array_key_exists('HTTP_REFERER', $_SERVER)):
                $this->referer = htmlentities($_SERVER['HTTP_REFERER'], ENT_COMPAT, framework\Context::getI18n()->getCharset());
            else:
                $this->referer = framework\Context::getRouting()->generate('dashboard');
            endif;

            try
            {
                $this->loginintro = null;
                $this->registrationintro = null;
                $this->loginintro = tables\Articles::getTable()->getArticleByName('LoginIntro');
                $this->registrationintro = tables\Articles::getTable()->getArticleByName('RegistrationIntro');
            }
            catch (\Exception $e)
            {

            }

            if (framework\Settings::isLoginRequired())
            {
                $authentication_backend = framework\Settings::getAuthenticationBackend();
                if ($authentication_backend->getAuthenticationMethod() == AuthenticationProvider::AUTHENTICATION_TYPE_TOKEN)
                {
                    framework\Context::getResponse()->deleteCookie('username');
                    framework\Context::getResponse()->deleteCookie('session_token');
                }
                else
                {
                    framework\Context::getResponse()->deleteCookie('username');
                    framework\Context::getResponse()->deleteCookie('password');
                }
                $this->error = framework\Context::geti18n()->__('You need to log in to access this site');
            }

            if (framework\Context::hasMessage('login_error')) {
                $this->error = framework\Context::getMessageAndClear('login_error');
            }
        }

        public function componentLoginRegister()
        {

        }

        public function componentCaptcha()
        {
            if (!isset($_SESSION['activation_number'])) {
                $_SESSION['activation_number'] = pachno_get_activation_number();
            }
        }

        public function componentIssueadditem()
        {
            $project = $this->issue->getProject();
            $this->editions = $project->getEditions();
            $this->components = $project->getComponents();
            $this->builds = $project->getActiveBuilds();
        }

        public function componentDashboardview()
        {
            if ($this->view->hasJS())
            {
                foreach ($this->view->getJS() as $js)
                    $this->getResponse()->addJavascript($js);
            }
        }

        public function componentDashboardConfig()
        {
            $this->views = entities\DashboardView::getAvailableViews($this->target_type);
            $this->dashboardViews = entities\DashboardView::getViews($this->tid, $this->target_type);
        }

        protected function _setupReportIssueProperties()
        {
            $this->locked_issuetype = $this->locked_issuetype ? : null;
            $this->selected_issuetype = $this->selected_issuetype ? : null;
            $this->selected_edition = $this->selected_edition ? : null;
            $this->selected_build = $this->selected_build ? : null;
            $this->selected_milestone = $this->selected_milestone ? : null;
            $this->parent_issue = $this->parent_issue ? : null;
            $this->selected_component = $this->selected_component ? : null;
            $this->selected_category = $this->selected_category ? : null;
            $this->selected_status = $this->selected_status ? : null;
            $this->selected_resolution = $this->selected_resolution ? : null;
            $this->selected_priority = $this->selected_priority ? : null;
            $this->selected_reproducability = $this->selected_reproducability ? : null;
            $this->selected_severity = $this->selected_severity ? : null;
            $this->selected_estimated_time = $this->selected_estimated_time ? : null;
            $this->selected_spent_time = $this->selected_spent_time ? : null;
            $this->selected_percent_complete = $this->selected_percent_complete ? : null;
            $this->selected_pain_bug_type = $this->selected_pain_bug_type ? : null;
            $this->selected_pain_likelihood = $this->selected_pain_likelihood ? : null;
            $this->selected_pain_effect = $this->selected_pain_effect ? : null;
            $selected_customdatatype = $this->selected_customdatatype ? : array();
            foreach (entities\CustomDatatype::getAll() as $customdatatype)
            {
                $selected_customdatatype[$customdatatype->getKey()] = isset($selected_customdatatype[$customdatatype->getKey()]) ? $selected_customdatatype[$customdatatype->getKey()] : null;
            }
            $this->selected_customdatatype = $selected_customdatatype;
            $this->issuetype_id = $this->issuetype_id ? : null;
            $this->issue = $this->issue ? : null;
            $this->categories = entities\Category::getAll();
            $this->severities = entities\Severity::getAll();
            $this->priorities = entities\Priority::getAll();
            $this->reproducabilities = entities\Reproducability::getAll();
            $this->resolutions = entities\Resolution::getAll();
            $this->statuses = entities\Status::getAll();
            $this->milestones = framework\Context::getCurrentProject()->getMilestonesForIssues();
            $this->al_items = array();
        }

        public function componentReportIssue()
        {
            $introarticle = tables\Articles::getTable()->getArticleByName(ucfirst(framework\Context::getCurrentProject()->getKey()) . ':ReportIssueIntro');
            $this->introarticle = ($introarticle instanceof entities\Article) ? $introarticle : tables\Articles::getTable()->getArticleByName('ReportIssueIntro');
            $reporthelparticle = tables\Articles::getTable()->getArticleByName(ucfirst(framework\Context::getCurrentProject()->getKey()) . ':ReportIssueHelp');
            $this->reporthelparticle = ($reporthelparticle instanceof entities\Article) ? $reporthelparticle : tables\Articles::getTable()->getArticleByName('ReportIssueHelp');
            $this->uniqid = framework\Context::getRequest()->getParameter('uniqid', uniqid());
            $this->_setupReportIssueProperties();
            $dummyissue = new entities\Issue();
            $dummyissue->setProject(framework\Context::getCurrentProject());
            $this->canupload = (framework\Settings::isUploadsEnabled() && $dummyissue->canAttachFiles());
        }

        public function componentReportIssueContainer()
        {

        }

        public function componentConfirmUsername()
        {

        }

        public function componentMoveIssue()
        {

        }

        public function componentIssuePermissions()
        {
            $al_items = $this->issue->getAccessList();

            foreach ($al_items as $k => $item)
            {
                if ($item['target'] instanceof entities\User && $item['target']->getID() == $this->getUser()->getID())
                {
                    unset($al_items[$k]);
                }
            }

            $this->al_items = $al_items;
        }

        public function componentIssueSubscribers()
        {
            $this->users = $this->issue->getSubscribers();
        }

        public function componentIssueSpenttimes()
        {

        }

        public function componentIssueSpenttime()
        {
            $this->entry = tables\IssueSpentTimes::getTable()->selectById($this->entry_id);
        }

        public function componentDashboardLayoutStandard()
        {

        }

        public function componentDashboardViewRecentComments()
        {
            $this->comments = entities\Comment::getRecentCommentsByAuthor($this->getUser()->getID());
        }

        public function componentDashboardViewLoggedActions()
        {
            $this->log_items = tables\LogItems::getTable()->getByUserID($this->getUser()->getID(), 35);
            $this->prev_date = null;
            $this->prev_timestamp = null;
            $this->prev_issue = null;
        }

        public function componentDashboardViewUserProjects()
        {
            $routing = $this->getRouting();
            $i18n = $this->getI18n();
            framework\Context::loadLibrary('ui');
            $links = array(
                array('url' => $routing->generate('project_open_issues', array('project_key' => '%project_key%')), 'text' => $i18n->__('Issues')),
                array('url' => $routing->generate('project_roadmap', array('project_key' => '%project_key%')), 'text' => $i18n->__('Roadmap')),
            );
            $event = \pachno\core\framework\Event::createNew('core', 'main\Components::DashboardViewUserProjects::links', null, array(), $links);
            $event->trigger();
            $this->links = $event->getReturnList();
        }

        public function componentDashboardViewUserMilestones()
        {

        }

        public function componentIssueEstimator()
        {
            $times = array();
            switch ($this->field)
            {
                case 'estimated_time':
                    $times['months'] = $this->issue->getEstimatedMonths();
                    $times['weeks'] = $this->issue->getEstimatedWeeks();
                    $times['days'] = $this->issue->getEstimatedDays();
                    $times['hours'] = $this->issue->getEstimatedHours();
                    $times['minutes'] = $this->issue->getEstimatedMinutes();
                    $this->points = $this->issue->getEstimatedPoints();
                    break;
                case 'spent_time';
                    $times['months'] = 0;
                    $times['weeks'] = 0;
                    $times['days'] = 0;
                    $times['hours'] = 0;
                    $times['minutes'] = 0;
                    $this->points = 0;
                    break;
            }
            $this->times = $times;
            $this->project_key = $this->issue->getProject()->getKey();
            $this->issue_id = $this->issue->getID();
        }

        public function componentAddDashboardView()
        {
            $request = framework\Context::getRequest();
            $this->dashboard = entities\Dashboard::getB2DBTable()->selectById($request['dashboard_id']);
            $this->column = $request['column'];
            $this->views = entities\DashboardView::getAvailableViews($this->dashboard->getType());
            $this->savedsearches = tables\SavedSearches::getTable()->getAllSavedSearchesByUserIDAndPossiblyProjectID(framework\Context::getUser()->getID(), ($this->dashboard->getProject() instanceof entities\Project) ? $this->dashboard->getProject()->getID() : 0);
        }


        public function componentProjectList()
        {
            $url_options = ['project_state' => 'active', 'list_mode' => $this->list_mode];
            $partial_options = ['key' => 'project_config'];

            if ($this->list_mode == 'team') {
                $url_options['team_id'] = $this->team_id;
                $partial_options['assignee_type'] = 'team';
                $partial_options['assignee_id'] = $this->team_id;
            } elseif ($this->list_mode == 'client') {
                $url_options['client_id'] = $this->client_id;
            }

            $this->active_url = $this->getRouting()->generate('project_list', $url_options);
            $url_options['project_state'] = 'archived';
            $this->partial_options = $partial_options;
            $this->archived_url = $this->getRouting()->generate('project_list', $url_options);
            $this->show_project_config_link = $this->getUser()->canAccessConfigurationPage(framework\Settings::CONFIGURATION_SECTION_PROJECTS) && framework\Context::getScope()->hasProjectsAvailable();
        }

        public function componentMenuLink()
        {
            $this->link_id = $this->link->getId();
        }

        public function componentEnable2FA()
        {
            $secret = $this->getUser()->get2faToken();
            if (!$secret) {
                $google2fa = new Google2FA();
                $secret = $google2fa->generateSecretKey();
                $this->getUser()->set2faToken($secret);
                $this->getUser()->save();
            }

            $google2fa_qr_code = new \PragmaRX\Google2FAQRCode\Google2FA();
            $this->qr_code_inline = $google2fa_qr_code->getQRCodeInline('Pachno', $this->getUser()->getEmail(), $secret);
            $this->session_token = framework\Context::getRequest()->getCookie('session_token');
        }

    }
