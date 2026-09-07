@echo off

echo ========================================
echo Uploading CSV files...
echo ========================================

"E:\programs\WinSCP\WinSCP.com" /script="E:\programs\WinSCP\Scripts\upload_csv.txt"

echo.
echo ========================================
echo Upload completed.
echo ========================================
pause
