#!/usr/bin/env python
import sys
import yaml

filepath = sys.argv[1]
value_key = sys.argv[2]

# Open the specified yaml file.
yaml_file = open(filepath, 'r')
yaml_contents = yaml.load(yaml_file)

# Check whether the requested value key exists.
if value_key in yaml_contents:
    # Get the specified value.
    value = yaml_contents[value_key]

    # If the value is a string, print it out.
    if isinstance(value, str):
        print value
    # If the value is an array of strings, print each out.
    elif isinstance(value, (list, tuple)):
        for sub_values in value:
            print sub_values

yaml_file.close()
