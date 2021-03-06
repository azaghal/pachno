<?php

    use pachno\core\entities\AgileBoard;


// shows only issues with permissions, useful when if we're including subprojects
if (isset($issues) && !empty($issues) && !current($issues)->hasAccess())
    return;
?>
<div class="row <?php if (!count($issues)) echo 'collapsed'; ?>" data-swimlane-identifier="<?php echo $swimlane->getIdentifier(); ?>"<?php if ($swimlane->getBoard()->usesSwimlanes() && $swimlane->hasIdentifiables() && $swimlane->getBoard()->getSwimlaneType() == AgileBoard::SWIMLANES_ISSUES): ?> id="whiteboard_issue_<?php echo $swimlane->getIdentifierIssue()->getID(); ?>" data-issue-id="<?php echo $swimlane->getIdentifierIssue()->getID(); ?>" data-last-updated="<?php echo $swimlane->getIdentifierIssue()->getLastUpdatedTime(); ?>"<?php endif; ?>>
    <?php if ($swimlane->getBoard()->usesSwimlanes() && $swimlane->hasIdentifiables()): ?>
        <div class="swimlane-header">
            <div class="header">
                <?php echo image_tag('icon-mono-expand.png', array('class' => 'expander', 'onclick' => "$(this).up('.tbody').toggleClassName('collapsed');")); ?>
                <?php echo image_tag('icon-mono-collapse.png', array('class' => 'collapser', 'onclick' => "$(this).up('.tbody').toggleClassName('collapsed');")); ?>
                <?php if ($swimlane->getBoard()->getSwimlaneType() == AgileBoard::SWIMLANES_ISSUES): ?>
                    <span class="issue_header <?php if ($swimlane->getIdentifierIssue()->isClosed()) echo 'closed'; ?>">
                        <?php echo link_tag(make_url('viewissue', array('issue_no' => $swimlane->getIdentifierIssue()->getFormattedIssueNo(), 'project_key' => $swimlane->getIdentifierIssue()->getProject()->getKey())), $swimlane->getIdentifierIssue()->getFormattedIssueNo(true, false), array('title' => $swimlane->getIdentifierIssue()->getFormattedTitle(), 'target' => '_blank', 'class' => 'issue_header')); ?>
                        <?php echo $swimlane->getIdentifierIssue()->getTitle(); ?>
                    </span>
                <?php else: ?>
                    <?php echo $swimlane->getName(); ?>
                <?php endif; ?>
                (<span class="swimlane_count"><?php echo count($issues); ?></span>)
            </div>
        </div>
        <?php if ($swimlane->getBoard()->getSwimlaneType() == AgileBoard::SWIMLANES_ISSUES): ?>
            <div class="planning_indicator" id="issue_<?php echo $swimlane->getIdentifierIssue()->getID(); ?>_indicator" style="display: none;"><?php echo image_tag('spinning_16.gif'); ?></div>
        <?php endif; ?>
    <?php endif; ?>
</div>
<div class="row">
    <?php foreach ($swimlane->getBoard()->getColumns() as $column): ?>
        <div class="column" id="swimlane_<?php echo $swimlane->getIdentifier(); ?>_column_<?php echo $column->getID(); ?>" data-column-id="<?php echo $column->getID(); ?>" data-swimlane-identifier="<?php echo $swimlane->getIdentifier(); ?>" data-status-ids="<?php echo join(',', $column->getStatusIds()); ?>">
            <?php foreach ($issues as $issue): ?>
                <?php if ($column->hasIssue($issue)) include_component('agile/whiteboardissue', compact('issue', 'column', 'swimlane')); ?>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</div>
