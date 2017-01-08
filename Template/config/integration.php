<h3><i class="fa fa-slack fa-fw"></i>Slack</h3>
<div class="panel">
    <?= $this->form->label(t('Webhook URL'), 'slack_webhook_url') ?>
    <?= $this->form->text('slack_webhook_url', $values) ?>

    <p class="form-help"><a href="https://kanboard.net/plugin/slack" target="_blank"><?= t('Help on Slack integration') ?></a></p>

    <div class="form-actions">
        <input type="submit" value="<?= t('Save') ?>" class="btn btn-blue"/>
    </div>
</div>
