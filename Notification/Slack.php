<?php

namespace Kanboard\Plugin\Slack\Notification;

use Kanboard\Core\Base;
use Kanboard\Core\Notification\NotificationInterface;
use Kanboard\Model\TaskModel;

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
     * @param  string    $eventName
     * @param  array     $eventData
     */
    public function notifyUser(array $user, $eventName, array $eventData)
    {
        $webhook = $this->userMetadataModel->get($user['id'], 'slack_webhook_url', $this->configModel->get('slack_webhook_url'));
        $channel = $this->userMetadataModel->get($user['id'], 'slack_webhook_channel');

        if (! empty($webhook)) {
            if ($eventName === TaskModel::EVENT_OVERDUE) {
                foreach ($eventData['tasks'] as $task) {
                    $project = $this->projectModel->getById($task['project_id']);
                    $eventData['task'] = $task;
                    $this->sendMessage($webhook, $channel, $project, $eventName, $eventData);
                }
            } else {
                $project = $this->projectModel->getById($eventData['task']['project_id']);
                $this->sendMessage($webhook, $channel, $project, $eventName, $eventData);
            }
        }
    }

    /**
     * Send notification to a project
     *
     * @access public
     * @param  array     $project
     * @param  string    $eventName
     * @param  array     $eventData
     */
    public function notifyProject(array $project, $eventName, array $eventData)
    {
        $webhook = $this->projectMetadataModel->get($project['id'], 'slack_webhook_url', $this->configModel->get('slack_webhook_url'));
        $channel = $this->projectMetadataModel->get($project['id'], 'slack_webhook_channel');

        if (! empty($webhook)) {
            $this->sendMessage($webhook, $channel, $project, $eventName, $eventData);
        }
    }

    /**
     * Get message to send
     *
     * @access public
     * @param  array     $project
     * @param  string    $eventName
     * @param  array     $eventData
     * @return array
     */
    public function getMessage(array $project, $eventName, array $eventData)
    {
        if ($this->userSession->isLogged()) {
            $author = $this->helper->user->getFullname();
            $title = $this->notificationModel->getTitleWithAuthor($author, $eventName, $eventData);
        } else {
            $title = $this->notificationModel->getTitleWithoutAuthor($eventName, $eventData);
        }

        $message = '*['.$project['name'].']* ';
        $message .= $title;
        $message .= ' ('.$eventData['task']['title'].')';

        if ($this->configModel->get('application_url') !== '') {
            $message .= ' - <';
            $message .= $this->helper->url->to('TaskViewController', 'show', array('task_id' => $eventData['task']['id'], 'project_id' => $project['id']), '', true);
            $message .= '|'.t('view the task on Kanboard').'>';
        }

        return array(
            'text' => $message,
            'username' => 'Kanboard',
            'icon_url' => 'https://raw.githubusercontent.com/kanboard/kanboard/master/assets/img/favicon.png',
        );
    }

    /**
     * Send message to Slack
     *
     * @access protected
     * @param  string    $webhook
     * @param  string    $channel
     * @param  array     $project
     * @param  string    $eventName
     * @param  array     $eventData
     */
    protected function sendMessage($webhook, $channel, array $project, $eventName, array $eventData)
    {
        $payload = $this->getMessage($project, $eventName, $eventData);

        if (! empty($channel)) {
            $payload['channel'] = $channel;
        }

        $this->httpClient->postJsonAsync($webhook, $payload);
    }
}
