@echo off
echoLite Publisher ftp uploader
echo Please edit "ftp-install.txt" file to upload Lite Publisher
echo 1. Change "open example.com" to your server name
echo 2. Change "user login password" to your ftp login and passwword
echo 3. Change "cd public_html" to domain folder on your server.
echo ---------
echo To cancel press Ctrl+C
pause
ftp -n -s:ftp-upload.txt