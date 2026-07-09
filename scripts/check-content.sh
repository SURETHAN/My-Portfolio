#!/usr/bin/env bash
# Validate every content/*.json file. Run after editing, before refreshing.
set -u
DIR="$(cd "$(dirname "$0")/.." && pwd)/content"
fail=0
for f in "$DIR"/*.json; do
  name="$(basename "$f")"
  if err=$(python3 -m json.tool "$f" 2>&1 >/dev/null); then
    echo "  OK    $name"
  else
    echo "  ERROR $name → ${err#*: }"
    fail=1
  fi
done
if [ "$fail" -eq 1 ]; then
  echo
  echo "Fix the file(s) above — the line/column in the error points at the mistake."
  echo "Common causes: a missing comma between items, an extra comma after the"
  echo "last item, or an unescaped \" inside text."
  exit 1
fi
echo
echo "All content files are valid. Refresh the browser to see your changes."
