# Senec_Log_Viewer
downloading logfiles from Senec Machines an adapted "Senec Log Viewer" for lokal browsing logfiles

requirements:
- Senec Machine with https://
- Lokal Webserver (Webserver must have access to the Senec Machine for log-downloading)

copy:
- images/*
- js/*
- senec_log.php
- senec_log.sh
to your htdocs folder.

Edit senec_log.sh to your settings.
- chmod +x senec_log.sh

Start log-file Downloader with:
- ./senec_log.sh start (it takes a while for the first start, to downloading all your logfiles.)

open your browser http://your-domain-or-ip/senec_log.php
