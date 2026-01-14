#!/bin/bash

# === CONFIG ===
CSS_FILE="./dist/css/App.css"


# === STEP 1: Run Vite Build ===
echo "Running Vite build..."
vite build

if [ $? -eq 0 ]; then
  echo "✅ Vite build completed successfully!"
else
  echo "❌ Vite build failed!"
  exit 1
fi

# === STEP 2: Fix Font Paths in CSS ===
if [ -f "$CSS_FILE" ]; then
  echo "Fixing font paths in $CSS_FILE"
  sed -i 's|/eot|/dist/eot|g; s|/woff2|/dist/woff2|g; s|/woff|/dist/woff|g; s|/ttf|/dist/ttf|g' "$CSS_FILE"
  echo "✅ Font paths updated!"
else
  echo "⚠️ Warning: $CSS_FILE not found. Skipping font path fix."
fi


echo "🎉 All done!"
