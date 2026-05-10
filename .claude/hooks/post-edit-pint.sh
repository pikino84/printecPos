#!/usr/bin/env bash
# PostToolUse hook — auto-format PHP files with Laravel Pint after Edit/Write.
# Silent on success, silent on failure (must never break the edit).
set +e

# Read full stdin once (small JSON payload from harness).
input=$(cat)

# Parse "file_path" from JSON via sed (no python/jq dependency — both are unreliable
# on Windows + Git Bash: python3 is hijacked by Microsoft Store, jq not installed).
file=$(printf '%s' "$input" | sed -n 's/.*"file_path"[[:space:]]*:[[:space:]]*"\([^"]*\)".*/\1/p' | head -1)

# Bail conditions
[ -z "$file" ] && exit 0
[[ "$file" != *.php ]] && exit 0
[[ "$file" == *vendor/* ]] && exit 0
[[ "$file" == *node_modules/* ]] && exit 0
[[ "$file" == *.claude/* ]] && exit 0

# Move to project root
cd "${CLAUDE_PROJECT_DIR:-$(pwd)}" || exit 0
[ -f vendor/bin/pint ] || exit 0

# Locate PHP. Project is validated on 8.3 (Laravel 10 + PHPUnit 10).
# Prefer 8.3, fall back to 8.2 only — newer 8.4/8.5 are unverified.
PHP_BIN=""
for major in 8.3 8.2; do
    candidate=$(ls -d /c/laragon/bin/php/php-${major}.*-Win32-*/ 2>/dev/null | sort -V | tail -1)
    if [ -n "$candidate" ] && [ -f "${candidate}php.exe" ]; then
        PHP_BIN="${candidate}php.exe"
        break
    fi
done

[ -z "$PHP_BIN" ] && exit 0

# Run Pint silently
"$PHP_BIN" vendor/bin/pint "$file" > /dev/null 2>&1
exit 0
