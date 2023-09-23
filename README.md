# Senec Log Viewer
downloading logfiles from Senec Machines and adapted "Senec Log Viewer" for lokal browsing logfiles
![Screenshot](Logfile_Viewer.png)

### Requirements
- Senec Machine with https://
- Local Webserver (Webserver must have access to the Senec Machine for log-downloading)

### how to use it
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
- ./all_logs.sh

(it takes a while for to downloading all your logfiles)


Start log-file Updater with:
- ./senec_log.sh start

(it runs in the background and updated the current logfile)


open your browser http://your-domain-or-ip/senec_log.php
- you can change the Date for a specific logfile
- or click "show current log" to view the current log file, which is continuously updated with senec_log.sh


### Changelog
- 2023-09-19 Added more colors
  - Warning is now orange
  - Error is now red
  - NPU shows now in yellow
  - NET shows now in cyan


