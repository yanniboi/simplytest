#!/bin/bash

# @todo Refactor ShellScripts #1836036.

# Make sure we run as root.
if [[ $EUID -ne 0 ]]; then
   echo "This script must be run as root!"
   exit 1
fi

# Make sure all arguments are given.
if [[ "$#" -ne 1 ]]; then 
  echo "$0 [path to configuration file]"
  exit 1
fi

S_CONFIGURATION=$1

# Make sure all arguments are given.
if [ ! -f $S_CONFIGURATION ]; then
  echo "Given configuration file could not be found."
  exit 1
fi

# Load submission configuration.
source "$S_CONFIGURATION" || exit 1

# Absolute path to script dir.
DIRECTORY="$( cd -P "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

# Load function library.
source "$DIRECTORY/sources/common"

### PREPARE.

# Set state.
s_ste 102

lg "Prepare.."
s_prepare

### DOWNLOAD AND FETCH DEPENDENCIES.

# Set state.
s_ste 103

lg "Downloading project.."
s_project_download

lg "Fetch dependencies.."
s_project_fetch_dependencies

### INSTALL.
s_ste 104

lg "Installing project.."
s_project_install

### FINALIZE.
s_ste 105
lg "Finalizing.."

# Add infobar script snippet to index.php.
lg "Adding info snippet.."
s_addsnippet

# Make sure all files and directory have the correct group and user.
s_reset_environment_files "$S_ID"

# Set a timeout to destroy the environment.
lg "Set timeout to destroy job.."
s_settimeout "$DIRECTORY/destroy.sh $S_ID >>$DIRECTORY/log/$S_ID.log 2>&1" $S_TIMEOUT

### FINISHED
s_ste 106
