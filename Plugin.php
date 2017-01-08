<?php

namespace Kanboard\Plugin\Slack;

use Kanboard\Core\Translator;
use Kanboard\Core\Plugin\Base;

/**
 * Slack Plugin
 *
 * @package  slack
 * @author   Frederic Guillot
 */
class Plugin extends Base
{
    public function initialize()
    {
        $this->template->hook->attach('template:config:integrations', 'slack:config/integration');
        $this->template->hook->attach('template:project:integrations', 'slack:project/integration');
        $this->template->hook->attach('template:user:integrations', 'slack:user/integration');

        $this->userNotificationTypeModel->setType('slack', t('Slack'), '\Kanboard\Plugin\Slack\Notification\Slack');
        $this->projectNotificationTypeModel->setType('slack', t('Slack'), '\Kanboard\Plugin\Slack\Notification\Slack');
    }

    public function onStartup()
    {
        Translator::load($this->languageModel->getCurrentLanguage(), __DIR__.'/Locale');
    }

    public function getPluginDescription()
    {
        return 'Receive notifications on Slack';
    }

    public function getPluginAuthor()
    {
        return 'Frédéric Guillot';
    }

    public function getPluginVersion()
    {
        return '1.0.6';
    }

    public function getPluginHomepage()
    {
        return 'https://github.com/kanboard/plugin-slack';
    }

    public function getCompatibleVersion()
    {
        return '>=1.0.37';
    }
}
