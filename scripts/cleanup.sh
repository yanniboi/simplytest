#!/bin/bash

# Destroying all sandboxes older than 10 days.
OLD_SANDBOXES=$(find /home -maxdepth 1 -type d -ctime +10 -exec basename {} \;)
for SANDBOX in $OLD_SANDBOXES
do
  if [ "$SANDBOX" != "spawner" ]; then
    ./destroy.sh $SANDBOX
  fi
done