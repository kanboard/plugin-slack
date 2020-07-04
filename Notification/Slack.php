<?php

namespace Kanboard\Plugin\Slack\Notification;

use Kanboard\Core\Base;
use Kanboard\Core\Notification\NotificationInterface;
use Kanboard\Model\TaskModel;
use ReflectionClass;
use ReflectionException;

/**
 * Slack Notification
 *
 * @package  notification
 * @author   Frederic Guillot
 */
class Slack extends Base implements NotificationInterface
{

    /**
     * @param $projectId
     * @return array
     */
    private function getProjectEventValues($projectId){
        $constants = array();
        try {
            $reflection = new ReflectionClass(TaskModel::class);
            $constants = $reflection->getConstants();
        } catch(ReflectionException $exception){
            return array();
        } finally {
            $events = array();
        }

        foreach($constants as $key => $value){
            if(strpos($key, 'EVENT') !== false){
                $id = str_replace(".", "_", $value);

                $event_value = $this->projectMetadataModel->get($projectId, $id, $this->configModel->get($id));
                if($event_value == 1) {
                    array_push($events, $value);
                }
            }
        }

        return $events;
    }

    /**
     * @param $userId
     * @return array
     */
    private function getUserEventValues($userId){
        $constants = array();
        try {
            $reflection = new ReflectionClass(TaskModel::class);
            $constants = $reflection->getConstants();
        } catch(ReflectionException $exception){
            return array();
        } finally {
            $events = array();
        }

        foreach($constants as $key => $value){
            if(strpos($key, 'EVENT') !== false){
                $id = str_replace(".", "_", $value);

                $event_value = $this->userMetadataModel->get($userId, $id, $this->configModel->get($id));
                if($event_value == 1) {
                    array_push($events, $value);
                }
            }
        }

        return $events;
    }

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
            $events = $this->getUserEventValues($user['id']);

            foreach($events as $event) {
                if($eventName == $event) {
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
            $events = $this->getProjectEventValues($project['id']);
            foreach($events as $event) {
                if ($eventName == $event) {
                    $this->sendMessage($webhook, $channel, $project, $eventName, $eventData);
                }
            }
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

        $attachment = [];
        if ($this->configModel->get('application_url') !== '') {
            $attachment_link = $this->helper->url->to('TaskViewController', 'show', array('task_id' => $eventData['task']['id'], 'project_id' => $project['id']), '', true);
            $attachment = [
                [
                    'fallback' => 'View task on ' . $attachment_link,
                    'actions' => [
                        [
                            'type' => 'button',
                            'text' => 'View Task',
                            'url' => $attachment_link,
                            'style' => 'primary'

                        ]
                    ]
                ]
            ];
        }

        return array(
            'text' => $message,
            'attachments' => $attachment
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
