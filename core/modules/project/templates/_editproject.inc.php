<?php

/**
 * @var \pachno\core\entities\Project $project
 * @var \pachno\core\entities\Role[] $roles
 */

?>
<div class="backdrop_box large" id="project_config_popup_main_container">
    <div class="backdrop_detail_header">
        <span><?= ($project->getId()) ? __('Edit project') : __('Create a project'); ?></span>
        <a class="closer" href="javascript:void(0);" onclick="Pachno.Main.Helpers.Backdrop.reset();"><?= fa_image_tag('times'); ?></a>
    </div>
    <div id="backdrop_detail_content" class="backdrop_detail_content">
        <?php if ($project->getId()): ?>
            <h5 style="font-size: 13px; text-align: left;">
                <div class="button button-blue" style="float: right; margin: -5px 5px 5px 0;"><?= link_tag(make_url('project_settings', ['project_key' => $project->getKey()]), '<span>'.__('More settings').'</span>'); ?></a></div>
                <?= __('Only showing basic project details. More settings available in the main project configuration.'); ?>
            </h5>
        <?php endif; ?>
        <?php \pachno\core\framework\Event::createNew('core', 'project/editproject::above_content')->trigger(compact('project')); ?>
        <div class="form-container">
            <?php if ($access_level == \pachno\core\framework\Settings::ACCESS_FULL): ?>
                <form accept-charset="<?= \pachno\core\framework\Context::getI18n()->getCharset(); ?>" action="<?= make_url('configure_project_settings', ['project_id' => $project->getID()]); ?>" method="post" onsubmit="Pachno.Project.submitInfo('<?= make_url('configure_project_settings', ['project_id' => $project->getID()]); ?>', <?= $project->getID(); ?>); return false;" id="project_info">
            <?php endif; ?>
            <?php \pachno\core\framework\Event::createNew('core', 'project/editproject::additional_form_elements')->trigger(compact('project')); ?>
                <div class="form-row">
                    <?php if ($access_level == \pachno\core\framework\Settings::ACCESS_FULL): ?>
                        <input type="text" class="name-input-enhance" name="project_name" id="project_name_input" onblur="Pachno.Project.updatePrefix('<?= make_url('configure_project_get_updated_key', ['project_id' => $project->getID()]); ?>', <?= $project->getID(); ?>);" value="<?php print $project->getName(); ?>" placeholder="<?= __('A great project name'); ?>">
                    <?php else: ?>
                        <span class="value"><?= $project->getName(); ?></span>
                    <?php endif; ?>
                    <label for="project_name_input"><?= __('Project name'); ?></label>
                </div>
                <?php if ($project->getId()): ?>
                    <div class="form-row">
                        <?php if ($access_level == \pachno\core\framework\Settings::ACCESS_FULL): ?>
                            <div id="project_key_indicator" class="semi_transparent" style="position: absolute; height: 23px; background-color: #FFF; width: 210px; text-align: center; display: none;"><?= image_tag('spinning_16.gif'); ?></div>
                            <input type="text" name="project_key" id="project_key_input" value="<?php print $project->getKey(); ?>" style="width: 150px;">
                        <?php else: ?>
                            <?= $project->getKey(); ?>
                        <?php endif; ?>
                        <label for="project_key_input"><?= __('Project key'); ?></label>
                        <div class="helper-text"><?= __('This is a part of all urls referring to this project'); ?></div>
                    </div>
                    <div class="form-row">
                        <label for="project_description_input"><?= __('Project description'); ?></label>
                        <?php if ($access_level == \pachno\core\framework\Settings::ACCESS_FULL): ?>
                            <?php include_component('main/textarea', ['area_name' => 'description', 'target_type' => 'project', 'target_id' => $project->getID(), 'area_id' => 'project_description_input', 'height' => '200px', 'width' => '100%', 'value' => $project->getDescription(), 'hide_hint' => true]); ?>
                        <?php else: ?>
                            <span class="value"><?= ($project->hasDescription()) ? $project->getDescription() : __('No description set'); ?></span>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="form-row">
                        <div class="fancydropdown-container">
                            <div class="fancydropdown">
                                <label><?= __('Type of project'); ?></label>
                                <span class="value"><?= __('Classic software project'); ?></span>
                                <?= fa_image_tag('angle-down', ['class' => 'expander']); ?>
                                <div class="dropdown-container list-mode">
                                    <input type="radio" name="project_type" value="classic" id="edit_project_type_classic" class="fancycheckbox" checked>
                                    <label for="edit_project_type_classic" class="list-item multiline">
                                        <span class="icon"><?php echo fa_image_tag('code'); ?></span>
                                        <span class="name">
                                            <span class="title value"><?= __('Classic software project'); ?></span>
                                            <span class="description"><?php echo __('Classic project template without specific settings'); ?></span>
                                        </span>
                                    </label>
                                    <input type="radio" name="project_type" value="team" id="edit_project_type_team" class="fancycheckbox">
                                    <label for="edit_project_type_team" class="list-item multiline">
                                        <span class="icon"><?php echo fa_image_tag('users'); ?></span>
                                        <span class="name">
                                            <span class="title value"><?= __('Distributed teams project'); ?></span>
                                            <span class="description"><?php echo __('For projects with multiple teams, often distributed across locations'); ?></span>
                                        </span>
                                    </label>
                                    <input type="radio" name="project_type" value="open-source" id="edit_project_type_open_source" class="fancycheckbox">
                                    <label for="edit_project_type_open_source" class="list-item multiline">
                                        <span class="icon"><?php echo fa_image_tag('code-branch'); ?></span>
                                        <span class="name">
                                            <span class="title value"><?= __('Classic open source'); ?></span>
                                            <span class="description"><?php echo __('For small/medium open source projects without multiple teams'); ?></span>
                                        </span>
                                    </label>
                                    <input type="radio" name="project_type" value="agile" id="edit_project_type_agile" class="fancycheckbox">
                                    <label for="edit_project_type_agile" class="list-item multiline">
                                        <span class="icon"><?php echo fa_image_tag('redo', ['style' => 'transform: rotate(90deg)']); ?></span>
                                        <span class="name">
                                            <span class="title value"><?= __('Agile software project'); ?></span>
                                            <span class="description"><?php echo __('For projects with an agile methodology like e.g. scrum or kanban'); ?></span>
                                        </span>
                                    </label>
                                    <input type="radio" name="project_type" value="service-desk" id="edit_project_type_service_desk" class="fancycheckbox">
                                    <label for="edit_project_type_service_desk" class="list-item multiline">
                                        <span class="icon"><?php echo fa_image_tag('phone'); ?></span>
                                        <span class="name">
                                            <span class="title value"><?= __('Helpdesk / support'); ?></span>
                                            <span class="description"><?php echo __('For helpdesk or support projects without a traditional software development cycle'); ?></span>
                                        </span>
                                    </label>
                                    <input type="radio" name="project_type" value="personal" id="edit_project_type_personal" class="fancycheckbox">
                                    <label for="edit_project_type_personal" class="list-item multiline">
                                        <span class="icon"><?php echo fa_image_tag('th-list'); ?></span>
                                        <span class="name">
                                            <span class="title value"><?= __('Personal todo-list'); ?></span>
                                            <span class="description"><?php echo __('A project acting like a personal todo-list. No fuzz, no headache.'); ?></span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="helper-text">
                            <?= __('Select the type of project you are creating. The type of project decides initial workflows, issue types, settings and more. You can always configure this later.'); ?>
                        </div>
                    </div>
                    <div class="form-row">
                        <input type="hidden" name="mark_as_owner" value="1">
                        <input type="hidden" name="assignee_type" value="<?= $assignee_type; ?>">
                        <input type="hidden" name="assignee_id" value="<?= $assignee_id; ?>">
                        <div class="fancydropdown-container">
                            <div class="fancydropdown">
                                <label><?= __('My role in this project'); ?></label>
                                <span class="value"><?= __('I have no role in this project'); ?></span>
                                <?= fa_image_tag('angle-down', ['class' => 'expander']); ?>
                                <div class="dropdown-container list-mode">
                                    <input type="radio" class="fancycheckbox" id="project_role_checkbox_0" name="role_id" value="0" checked>
                                    <label for="project_role_checkbox_0" class="list-item">
                                        <?= fa_image_tag('check-circle', ['class' => 'checked'], 'far') . fa_image_tag('circle', ['class' => 'unchecked'], 'far'); ?>
                                        <span class="name value"><?= __('I have no role in this project'); ?></span>
                                    </label>
                                    <?php foreach ($roles as $role): ?>
                                        <input type="radio" class="fancycheckbox" id="project_role_checkbox_<?= $role->getID(); ?>" name="role_id" value="<?= $role->getID(); ?>">
                                        <label for="project_role_checkbox_<?= $role->getID(); ?>" class="list-item">
                                            <?= fa_image_tag('check-circle', ['class' => 'checked'], 'far') . fa_image_tag('circle', ['class' => 'unchecked'], 'far'); ?>
                                            <span class="name value"><?= $role->getName(); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <div class="helper-text">
                            <?= __('Choose a role in this project if you want to automatically set up permissions for your role'); ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php if ($access_level == \pachno\core\framework\Settings::ACCESS_FULL): ?>
                <div class="form-row submit-container">
                    <button class="button primary" type="submit" id="project_submit_settings_button" onclick="Pachno.Project.submitInfo('<?= make_url('configure_project_settings', ['project_id' => $project->getID()]); ?>', <?= $project->getID(); ?>);">
                        <?php if ($project->getId()): ?>
                            <?= fa_image_tag('save'); ?><span><?= __('Save project'); ?></span>
                        <?php else: ?>
                            <?= fa_image_tag('plus-square'); ?><span><?= __('Create project'); ?></span>
                        <?php endif; ?>
                        <span class="indicator"><?= fa_image_tag('spinner', ['class' => 'fa-spin']); ?></span>
                    </button>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>
