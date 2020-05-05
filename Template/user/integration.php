<?php
use Kanboard\Model\TaskModel;

$reflection = new ReflectionClass(TaskModel::class);
$constants = $reflection->getConstants();

$events = array_filter($constants, function($key)  {
    return strpos($key, "EVENT") !== false;
}, ARRAY_FILTER_USE_KEY);
?>

<h3><i class="fa fa-slack fa-fw"></i>Slack</h3>
<div class="panel">

    <div style="display:flex; flex-direction:row;">
        <div style="display:flex; flex-direction:column; margin:1rem;">
            <h3>General</h3>
            <div style="display:flex; flex-direction:column;">

                <?= $this->form->label(t('Webhook URL'), 'slack_webhook_url') ?>
                <?= $this->form->text('slack_webhook_url', $values) ?>

                <?= $this->form->label(t('Channel/Group/User (Optional)'), 'slack_webhook_channel') ?>
                <?= $this->form->text('slack_webhook_channel', $values, array(), array('placeholder="@username"')) ?>

                <p class="form-help">
                    <a href="https://github.com/kanboard/plugin-slack#configuration" target="_blank"><?= t('Help on Slack integration') ?></a>
                </p>

            </div>
        </div>

        <div style="display:flex; flex-direction:column; margin:1rem;">
            <h3>Trigger Events</h3>
            <div style="display:flex; flex-direction:row; flex-wrap:wrap; justify-content: start;">

                <?php foreach($events as $key => $name)
                {
                    $id = str_replace(".", "_", $name);
                    $value = str_replace("event_", "", strtolower($key));
                    $checked = isset($values[$id]) && $values[$id] == 1;
                    ?>
                    <div style="display:flex; flex-direction:column; margin: 0.5rem 1rem 0.5rem 0; text-align:center;">
                        <?= $this->form->hidden($id, array($id => 0)) ?>
                        <?= $this->form->checkbox($id, $value, 1, $checked) ?>
                    </div>
                    <?php
                }
                ?>

            </div>
        </div>
    </div>

    <div class="form-actions">
        <input type="submit" value="<?= t('Save') ?>" class="btn btn-blue"/>
    </div>
</div>
