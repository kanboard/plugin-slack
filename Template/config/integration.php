<h3><i class="fa fa-slack fa-fw"></i>Slack</h3>
<div class="panel">
    <?= $this->form->label(t('Webhook URL'), 'slack_webhook_url') ?>


    <p class="form-help"><a https://support.discord.com/hc/en-us/articles/228383668-Intro-to-Webhooks" target="_blank"><?= t('Help communicating with Discord') ?></a></p>

    <div class="form-actions">
        <input type="submit" value="<?= t('Save') ?>" class="btn btn-blue"/>
    </div>
</div>
