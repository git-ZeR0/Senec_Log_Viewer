# Senec_Log_Viewer
downloading logfiles from Senec Machines an adapted "Senec Log Viewer" for lokal browsing logfiles
![Screenshot](Logfile_Viewer.png)

requirements:
- Senec Machine with https://
- Lokal Webserver (Webserver must have access to the Senec Machine for log-downloading)

copy:
- images/*
- js/*
- senec_log.php
- senec_log.sh
- all_logs.sh
to your htdocs folder.

Edit senec_log.sh and all_logs.sh to your settings.
- chmod +x senec_log.sh
- chmod +x all_logs.sh

Start all-log-downloader one time:
- ./all_logs.sh start
(it takes a while for to downloading all your logfiles)

Start log-file Updater with:
- ./senec_log.sh start
(it runs in the background and updated the current logfile)

open your browser http://your-domain-or-ip/senec_log.php
- you can change the Date for a specific logfile
- or click View Current Log to view the current log file that has been continuously updated using senec_log.sh
