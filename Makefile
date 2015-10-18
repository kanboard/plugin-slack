PLUGIN_NAME = Slack

all:
	@ git archive HEAD --prefix=${PLUGIN_NAME}/ --format=zip -o ${PLUGIN_NAME}.zip
