Slack plugin for Kanboard
=========================

Receive Kanboard notifications on Slack.

Author
------

- Frederic Guillot
- License MIT

Requirements
------------

- Kanboard >= 1.0.37

Installation
------------

You have the choice between 3 methods:

1. Install the plugin from the Kanboard plugin manager in one click
2. Download the zip file and decompress everything under the directory `plugins/Slack`
3. Clone this repository into the folder `plugins/Slack`

Note: Plugin folder is case-sensitive.

Configuration
-------------

Firstly, you have to generate a new webhook url in Slack (**Configured Integrations > Incoming Webhook**) [from here](https://slack.com/apps/A0F7XDUAZ-incoming-webhooks).

You can define only one webhook url (**Settings > Integrations > Slack**) and override the channel for each project and user.

### Receive individual user notifications

- Go to your user profile then choose **Integrations > Slack**
- Copy and paste the webhook url from Slack or leave it blank if you want to use the global webhook url
- Use `@username` to receive direct message to your user
- Enable Slack in your user notifications **Notifications > Slack**

### Receive project notifications to a room

- Go to the project settings then choose **Integrations > Slack**
- Copy and paste the webhook url from Slack or leave it blank if you want to use the global webhook url
- Use `#channel` to receive messages in a specific channel
- Enable Slack in your project notifications **Notifications > Slack**

## Troubleshooting

- Enable the debug mode
- All connection errors with the Slack API are recorded in the log files `data/debug.log` or syslog
