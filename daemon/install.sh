#!/bin/bash
SOURCE=`dirname $BASH_SOURCE`
template_file=$SOURCE/template.sh
temporal_file=$SOURCE/temporal.sh

if [ -f /etc/init.d/webos-master ]; then
	echo "A SERVICE ALREADY EXISTS" >&2
	exit 1
fi

echo "cp $template_file $temporal_file"
cp $template_file $temporal_file

echo "Define your service name: "
read SERVICE_NAME

echo "Define your service description :"
read SERVICE_DESCRIPTION

echo "Path your /impementation/path/service/master.php: "
read SERVICE_COMMAND

echo "Define your service user (root recommended): "
read SERVICE_USERNAME

echo service name is $SERVICE_NAME 

sed -i "s/<NAME>/$SERVICE_NAME/g" $temporal_file
sed -i "s/<DESCRIPTION>/$SERVICE_DESCRIPTION/g" $temporal_file
sed -i "s/<COMMAND>/$SERVICE_COMMAND/g" $temporal_file
sed -i "s/<USERNAME>/$SERVICE_USERNAME/g" $temporal_file

mv $temporal_file /etc/init.d/webos-master

sudo chmod +x /etc/init.d/webos-master
sudo update-rc.d webos-master defaults