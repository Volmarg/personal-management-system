#!/bin/bash

LATEST_TAG=$(git tag --sort=-committerdate | head -1)
CURRENT_STATE="HEAD";
CHANGES=$(git log --pretty="- %h %s" $CURRENT_STATE...$LATEST_TAG)
printf "## Changes\n$CHANGES\n\n## Metadata\n\\nPrevious version ---- $LATEST_TAG\nTotal commits ------- $(echo "$CHANGES" | wc -l)\n\n"
