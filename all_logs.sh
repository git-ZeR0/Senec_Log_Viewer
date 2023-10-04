#!/bin/bash
installationdate="2020-12-24"
senecip=192.168.0.150

days=$(echo $((($(date +%s)-$(date +%s --date $installationdate))/(3600*24))))

# Herunterladen und speichern der Log-Dateien
for ((i = 0; i < days; i++)); do
  log_date=$(date -d "$i days ago" '+%Y-%m-%d')
  log_folder="log/$(date -d "$i days ago" '+%Y/%m')"
  mkdir -p "$log_folder"
  log_path="$log_folder/$(date -d "$i days ago" '+%d').log"
  curl -o "$log_path" -k -O "https://$senecip//log/$(date -d "$i days ago" '+%Y/%m/%d').log"
done
