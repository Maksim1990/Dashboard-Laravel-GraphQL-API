#!/bin/bash

### For php-nginx image usage
#supervisord -c /opt/docker/etc/supervisor.conf

# Start Sopervisor & worker tasks
supervisord -c /etc/supervisor/supervisord.conf
supervisorctl start horizon:*
#supervisorctl start laravel-echo:*
supervisorctl start websockets:*

# In order to restart specific process
#supervisorctl restart horizon:
