<ul class="simple-list">
<?php if (count($role->getPermissions())): ?>
    <?php foreach ($role->getPermissions() as $permission): ?>
        <?php $permission_details = ($permission->getModule() == 'core') ? \pachno\core\framework\Context::getPermissionDetails($permission->getPermission()) : \pachno\core\framework\Context::getModule($permission->getModule())->getPermissionDetails($permission->getPermission()); ?>
        <?php if ($permission_details === null) continue; ?>
        <li>
            <?php echo image_tag('action_ok.png', array('style' => 'margin: 2px 5px -2px 0;')); ?><?php echo (array_key_exists('description', $permission_details)) ? $permission_details['description'] : $permission; ?>
        </li>
    <?php endforeach; ?>
<?php else: ?>
    <li class="faded_out"><?php echo __('This role does not have any associated permissions'); ?></li>
<?php endif; ?>
</ul>
