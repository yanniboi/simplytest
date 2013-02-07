#!/bin/bash

# Make sure all arguments are given.
if [[ "$#" -lt 1 ]]; then 
  echo "$0 [path to configuration file]"
  exit 1
fi

# Deamonize (start this script again, but dedicated).
if [ "x$1" != "x--" ]; then
  $0 -- "$1" 1> /dev/null 2> /dev/null &
  exit 0
fi

# !!! Absolute path to script dir.
# Change this if this script is somewhere else than in the scripts dir.
DIRECTORY="$( cd -P "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

# Get arguments.
S_CONFIGURATION=$2

# !!! Start build script WITH ROOT PERMISSIONS.
# To do this without beeing root, add these lines to your sudoers file by visudo:
# Cmnd_Alias SIMPLYTESTSPAWN_CMDS = <PATH TO THIS SCRIPT>
# <THIS USERS NAME> ALL=(ALL) NOPASSWD: SIMPLYTESTSPAWN_CMDS
timeout 900 sudo "$DIRECTORY/build.sh" "$S_CONFIGURATION" > "$DIRECTORY/log/$S_ID.log" 2>&1
