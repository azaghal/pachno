<li class="plugin theme <?php echo ($enabled) ? ' enabled' : ' disabled'; ?>" id="theme_<?php echo $theme['key']; ?>" data-theme-key="<?php echo $theme['key']; ?>" data-id="<?php echo $theme['key']; ?>" data-version="<?php echo $theme['version']; ?>">
    <?php echo __('%theme_name version %version by %author', array(
        '%theme_name' => '<h1>'.$theme['name'].'</h1>',
        '%version' => '<span class="version">'.$theme['version'].'</span>',
        '%author' => '<a href="https://pachno.com/themes/'.$theme['key'].'" class="author-link">'.$theme['author'].'</a>'
    )); ?>
    <p class="description"><?php echo $theme['description']; ?></p>
    <div class="status-badge theme_status plugin_status<?php echo ($enabled) ? ' enabled' : ' disabled'; ?>">
        <?php echo ($enabled) ? __('Current theme') : __('Disabled'); ?>
    </div>
    <div class="status-badge theme_status plugin_status outofdate">
        <?php echo __('Update available'); ?>
    </div>
    <?php if ($is_default_scope): ?>
        <div id="update_theme_help_<?php echo $theme['key']; ?>" class="fullpage_backdrop" style="display: none;">
            <div class="backdrop_box medium">
                <div class="backdrop_detail_header">
                    <span><?php echo __('Install downloaded theme update file'); ?></span>
                    <a href="javascript:void(0);" class="closer" onclick="$('update_theme_help_<?php echo $theme['key']; ?>').hide();"><?php echo fa_image_tag('times'); ?></a>
                </div>
                <div class="backdrop_detail_content">
                    <?php echo __('Please click the download link below and download the update file. Unpack the downloaded archive in the theme folder (%theme_folder), overwriting the current theme (%current_theme_folder) on this server, replacing the old contents. When you are done, refresh this page.',
                        array(
                            '%theme_folder' => '<span class="command_box">'.PACHNO_PATH.'themes</span>',
                            '%current_theme_folder' => '<span class="command_box">'.PACHNO_PATH.'themes/'.$theme['key'].'</span>'
                        ));
                    ?>
                </div>
                <div class="backdrop_details_submit">
                    <a id="theme_<?php echo $theme['key']; ?>_download_location" class="button" href="#" target="_blank"><?php echo __('Download update file'); ?></a>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <div class="theme-actions plugin-actions">
        <?php if ($is_default_scope): ?>
            <button class="button button-lightblue update-button dropper" id="theme_<?php echo $theme['key']; ?>_update" data-key="<?php echo $theme['key']; ?>"><?php echo __('Update'); ?></button>
            <ul id="theme_<?php echo $theme['key']; ?>_update_dropdown" style="font-size: 1.1em;" class="popup_box more_actions_dropdown" onclick="$(this).previous().toggleClassName('button-pressed');$(this).toggle();">
                <li>
                    <?php echo link_tag(make_url('configuration_download_theme_update', array('theme_key' => $theme['key'])), __('Install latest version')); ?>
                </li>
                <li><a href="javascript:void(0);" class="update-theme-menu-item"><?php echo __('Manual update'); ?></a></li>
            </ul>
        <?php endif; ?>
        <a href="<?php echo make_url('configuration_enable_theme', array('theme_key' => $theme['key'])); ?>" class="button enable-button"><?php echo __('Enable theme'); ?></a>
    </div>
</li>
