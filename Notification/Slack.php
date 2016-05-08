<?php

namespace Kanboard\Plugin\Slack\Notification;

use Kanboard\Core\Base;
use Kanboard\Notification\NotificationInterface;

/**
 * Slack Notification
 *
 * @package  notification
 * @author   Frederic Guillot
 */
class Slack extends Base implements NotificationInterface
{
    /**
     * Send notification to a user
     *
     * @access public
     * @param  array     $user
     * @param  string    $event_name
     * @param  array     $event_data
     */
    public function notifyUser(array $user, $event_name, array $event_data)
    {
        $webhook = $this->userMetadata->get($user['id'], 'slack_webhook_url', $this->config->get('slack_webhook_url'));
        $channel = $this->userMetadata->get($user['id'], 'slack_webhook_channel');

        if (! empty($webhook)) {
            $project = $this->project->getById($event_data['task']['project_id']);
            $this->sendMessage($webhook, $channel, $project, $event_name, $event_data);
        }
    }

    /**
     * Send notification to a project
     *
     * @access public
     * @param  array     $project
     * @param  string    $event_name
     * @param  array     $event_data
     */
    public function notifyProject(array $project, $event_name, array $event_data)
    {
        $webhook = $this->projectMetadata->get($project['id'], 'slack_webhook_url', $this->config->get('slack_webhook_url'));
        $channel = $this->projectMetadata->get($project['id'], 'slack_webhook_channel');

        if (! empty($webhook)) {
            $this->sendMessage($webhook, $channel, $project, $event_name, $event_data);
        }
    }

    /**
     * Get message to send
     *
     * @access public
     * @param  array     $project
     * @param  string    $event_name
     * @param  array     $event_data
     * @return array
     */
    public function getMessage(array $project, $event_name, array $event_data)
    {
        if ($this->userSession->isLogged()) {
            $author = $this->helper->user->getFullname();
            $title = $this->notification->getTitleWithAuthor($author, $event_name, $event_data);
        } else {
            $title = $this->notification->getTitleWithoutAuthor($event_name, $event_data);
        }

        $message = '*['.$project['name'].']* ';
        $message .= $title;
        $message .= ' ('.$event_data['task']['title'].')';

        if ($this->config->get('application_url') !== '') {
            $message .= ' - <';
            $message .= $this->helper->url->to('task', 'show', array('task_id' => $event_data['task']['id'], 'project_id' => $project['id']), '', true);
            $message .= '|'.t('view the task on Kanboard').'>';
        }

        return array(
            'text' => $message,
            'username' => 'Kanboard',
            'icon_url' => 'http://kanboard.net/assets/img/favicon.png',
        );
    }

    /**
     * Send message to Slack
     *
     * @access private
     * @param  string    $webhook
     * @param  string    $channel
     * @param  array     $project
     * @param  string    $event_name
     * @param  array     $event_data
     */
    private function sendMessage($webhook, $channel, array $project, $event_name, array $event_data)
    {
        $payload = $this->getMessage($project, $event_name, $event_data);

        if (! empty($channel)) {
            $payload['channel'] = $channel;
        }

        $this->httpClient->postJson($webhook, $payload);
    }
}
