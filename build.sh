#!/bin/bash

# === CONFIG ===
DB_NAME="1800-ucc-explorer"
OUTPUT_FILE="${DB_NAME}_$(date +%Y-%m-%d).sql"
CSS_FILE="./dist/css/App.css"
ARCHIVE_FILE="backup_$(date +%Y-%m-%d).tar.gz"

# === STEP 1: Export Database ===
echo "Exporting database: $DB_NAME"
mysqldump -u root "$DB_NAME" > "$OUTPUT_FILE"

# === STEP 2: Fix Collation in SQL Dump ===
if [ -f "$OUTPUT_FILE" ]; then
  echo "Renaming collation in $OUTPUT_FILE"
  sed -i 's|utf8mb4_0900_ai_ci|utf8mb4_unicode_ci|g' "$OUTPUT_FILE"
  echo "✅ Collation updated in SQL dump!"
else
  echo "⚠️ Warning: $OUTPUT_FILE not found. Skipping collation fix."
fi

if [ $? -eq 0 ]; then
  echo "✅ Database export successful! File saved as: $OUTPUT_FILE"
else
  echo "❌ Database export failed! Aborting build."
  exit 1
fi

# === STEP 3: Run Vite Build ===
echo "Running Vite build..."
vite build

if [ $? -eq 0 ]; then
  echo "✅ Vite build completed successfully!"
else
  echo "❌ Vite build failed!"
  exit 1
fi

# === STEP 4: Fix Font Paths in CSS ===
if [ -f "$CSS_FILE" ]; then
  echo "Fixing font paths in $CSS_FILE"
  sed -i 's|/eot|/dist/eot|g; s|/woff2|/dist/woff2|g; s|/woff|/dist/woff|g; s|/ttf|/dist/ttf|g' "$CSS_FILE"
  echo "✅ Font paths updated!"
else
  echo "⚠️ Warning: $CSS_FILE not found. Skipping font path fix."
fi

# === STEP 5: Create Compressed Backup ===
echo "Creating backup archive: $ARCHIVE_FILE"
tar -czvf "$ARCHIVE_FILE" src dist assets kernel app

if [ $? -eq 0 ]; then
  echo "✅ Backup archive created successfully!"
else
  echo "❌ Failed to create backup archive!"
fi

echo "🎉 All done!"
