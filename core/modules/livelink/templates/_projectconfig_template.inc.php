<?php

/**
 * @var \pachno\core\modules\livelink\Livelink $module
 * @var \pachno\core\modules\livelink\ConnectorProvider $connector_module
 */

?>
<div class="livelink-banner">
    <h2 class="livelink-header">
        <?= fa_image_tag('leaf') . __('Pachno Live Link'); ?>
    </h2>
    <p class="livelink-intro">
        <?= __('Import and/or link an existing project from %github, %gitlab, %bitbucket and more with %pachno_live_link.', ['%github' => fa_image_tag('github', [], 'fab') . ' GitHub', '%gitlab' => fa_image_tag('gitlab', [], 'fab') . ' GitLab', '%bitbucket' => fa_image_tag('bitbucket', [], 'fab') . ' BitBucket', '%pachno_live_link' => link_tag('https://pachno.com/features/livelink', fa_image_tag('leaf') . 'Pachno Live Link', ['target' => '_blank'])]); ?>
    </p>
    <?php if (isset($connector)): ?>
        <ul class="livelink-import-list">
            <li>
                <?php if ($project->getID()): ?>
                    <?php if ($module->isProjectImportInProgress($project)): ?>
                        <span class="description">
                            <?= fa_image_tag('spinner', ['class' => 'fa-spin']) . __('Importing from %project_name', ['%project_name' => '<span class="project_name">' . fa_image_tag($connector->getConnector()->getLogo(), ['class' => 'connector_logo'], $connector->getConnector()->getLogoStyle()) . $display_name . '</span>']); ?>
                        </span>
                    <?php else: ?>
                        <span class="description">
                            <?= __('Linked to %project_name', ['%project_name' => '<span class="project_name">' . fa_image_tag($connector->getConnector()->getLogo(), ['class' => 'connector_logo'], $connector->getConnector()->getLogoStyle()) . $display_name . '</span>']); ?>
                        </span>
                        <button class="button" id="project_remove_livelink_button"><?php echo __('Remove'); ?></button>
                    <?php endif; ?>
                <?php else: ?>
                    <span class="description">
                        <?= __('Linking / importing %project_name', ['%project_name' => '<span class="project_name">' . fa_image_tag($connector->getConnector()->getLogo(), ['class' => 'connector_logo'], $connector->getConnector()->getLogoStyle()) . $display_name . '</span>']); ?>
                        <span class="live_import_type">
                            <input type="checkbox" class="fancycheckbox" checked>
                            <?php if ($input['live_link'] == 'import'): ?>
                                <label><?= __("Only import the project"); ?></label>
                            <?php elseif ($input['live_link'] == 'simple'): ?>
                                <label><?= __("Import project from GitHub and update issues when commits are pushed"); ?></label>
                            <?php endif; ?>
                        </span>
                    </span>
                    <button class="button" id="project_remove_livelink_button" onclick="Pachno.Main.Helpers.Backdrop.show('<?= make_url('get_partial_for_backdrop', $partial_options); ?>');"><?php echo __('Cancel'); ?></button>
                <?php endif; ?>
            </li>
        </ul>
    <?php elseif ($module->hasConnectors()): ?>
        <ul class="livelink-import-list">
            <?php foreach ($module->getConnectorModules() as $connector_key => $connector_module): ?>
                <li>
                    <span class="description"><?= fa_image_tag($connector_module->getConnector()->getLogo(), [], $connector_module->getConnector()->getLogoStyle()) . $connector_module->getConnector()->getProjectTemplateDescription(); ?></span>
                    <button class="button" onclick="Pachno.Main.Helpers.Backdrop.show('<?= make_url('get_partial_for_backdrop', ['key' => 'livelink-import_project', 'connector' => $connector_key, 'project_id' => $project->getID()]); ?>');"><?php echo __('Import / link'); ?></button>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="livelink-intro">
            <?= __('%pachno_live_link requires integration plugins. Download the integration plugins from %configure_modules or visit %pachno_com to get started.', ['%pachno_live_link' => link_tag('https://pachno.com/features/livelink', fa_image_tag('leaf') . 'Pachno Live Link', ['target' => '_blank']), '%pachno_com' => link_tag('https://pachno.com/register/self-hosted', fa_image_tag('globe') . ' pachno.com'), '%configure_modules' => link_tag(make_url('configure_modules'), __('Configuration center') . '&nbsp;&raquo;&nbsp;' . __('Modules'))]); ?>
        </p>
    <?php endif; ?>
</div>
<?php if (!$project->getID()): ?>
    <fieldset class="livelink-separator">
        <?php if (isset($connector)): ?>
            <legend><?= __('Enter project details'); ?></legend>
        <?php else: ?>
            <legend><?= __('%import_with_livelink or create a project manually', array('%import_with_livelink' => '')); ?></legend>
        <?php endif; ?>
    </fieldset>
<?php else: ?>
    <script>
        require(['domReady', 'pachno/index', 'jquery'], function (domReady, pachno_index_js, $) {
            domReady(function () {
                var removeProjectLivelink = function () {
                    ['#dialog_yes', '#dialog_no'].each(function (elm) {
                        $(elm).addClass('disabled');
                    });

                    var submitRemoveLivelink = function () {
                        return new Promise(function (resolve, reject) {
                            $.ajax({
                                type: 'POST',
                                url: '<?= make_url('livelink_remove_project_connector', ['project_id' => $project->getID()]); ?>',
                                success: resolve,
                                error: function (details) {
                                    reject(details);
                                }
                            });
                        });
                    };

                    var loadLivelinkPartial = function () {
                        return new Promise(function (resolve, reject) {
                            $.ajax({
                                type: 'GET',
                                url: '<?= make_url('get_project_connector_template', ['project_id' => $project->getID()]); ?>',
                                success: resolve,
                                error: function (details) {
                                    reject(details);
                                }
                            });
                        });
                    };

                    submitRemoveLivelink()
                        .then(loadLivelinkPartial)
                        .then(function (content) {
                            $('#tab_livelink_pane').html(content);
                            ['#dialog_yes', '#dialog_no'].each(function (elm) {
                                $(elm).removeClass('disabled');
                            });
                            pachno_index_js.Main.Helpers.Dialog.dismiss();
                        })
                        .catch(function (error) {
                            pachno_index_js.Main.Helpers.Dialog.dismiss();
                            pachno_index_js.Main.Helpers.Message.error(error);
                            ['#dialog_yes', '#dialog_no'].each(function (elm) {
                                $(elm).removeClass('disabled');
                            });
                        })
                };

                $('#project_remove_livelink_button').off();
                $('#project_remove_livelink_button').on('click', function (e) {
                    e.preventDefault();
                    pachno_index_js.Main.Helpers.Dialog.show('<?php echo __('Remove Pachno LiveLink?'); ?>', '<?php echo __('Are you sure you want to remove the LiveLink integration from this project? No issues or project details will be removed or affected by this, but you will no longer receive updates from the external repository.'); ?>', {yes: {click: removeProjectLivelink }, no: {click: pachno_index_js.Main.Helpers.Dialog.dismiss}});
                });

            });
        });
    </script>
<?php endif; ?>
