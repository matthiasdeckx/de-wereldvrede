#!/usr/bin/env bash
# Restore content: copy every page .txt into .en.txt then remove .txt.
# Run this AFTER restoring the deleted .txt files from backup.
# Usage: from project root: bash scripts/restore-content-to-en.sh

set -e
CONTENT_DIR="${1:-content}"

if [[ ! -d "$CONTENT_DIR" ]]; then
  echo "Usage: $0 [content-dir]" && echo "Default: content/" && exit 1
fi

count=0
while IFS= read -r -d '' f; do
  dir=$(dirname "$f")
  base=$(basename "$f" .txt)
  # Skip media metadata (e.g. image.jpg.txt)
  if [[ "$base" == *.* ]]; then
    continue
  fi
  en_file="$dir/$base.en.txt"
  if [[ -f "$en_file" ]]; then
    echo "Overwrite $en_file with content from $f"
    cp "$f" "$en_file"
    rm "$f"
    ((count++)) || true
  else
    echo "Rename $f -> $en_file"
    mv "$f" "$en_file"
    ((count++)) || true
  fi
done < <(find "$CONTENT_DIR" -type f -name "*.txt" ! -name "*.en.txt" -print0 2>/dev/null)

echo "Done. Processed $count file(s)."
