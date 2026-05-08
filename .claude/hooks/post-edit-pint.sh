#!/usr/bin/env bash
# PostToolUse hook — auto-format PHP files with Laravel Pint after Edit/Write.
# Silent on success, silent on failure (must never break the edit).
set +e

# Extract file_path from stdin JSON. Python is on PATH (used by other tooling here).
file=$(python3 -c "import sys,json; d=json.load(sys.stdin); print(d.get('tool_input',{}).get('file_path',''))" 2>/dev/null)

# Bail conditions
[ -z "$file" ] && exit 0
[[ "$file" != *.php ]] && exit 0
[[ "$file" == *vendor/* ]] && exit 0
[[ "$file" == *node_modules/* ]] && exit 0
[[ "$file" == *.claude/* ]] && exit 0

# Move to project root
cd "${CLAUDE_PROJECT_DIR:-$(pwd)}" || exit 0
[ -f vendor/bin/pint ] || exit 0

# PHP isn't on Git Bash PATH on Windows — locate the latest 8.x in Laragon.
PHP_DIR=""
for major in 8.4 8.3 8.2 8.1; do
    candidate=$(ls -d /c/laragon/bin/php/php-${major}.*/ 2>/dev/null | sort -V | tail -1)
    if [ -n "$candidate" ]; then
        PHP_DIR="$candidate"
        break
    fi
done

PHP_BIN="${PHP_DIR}php.exe"
[ ! -f "$PHP_BIN" ] && exit 0

# Run Pint silently
"$PHP_BIN" vendor/bin/pint "$file" > /dev/null 2>&1
exit 0
