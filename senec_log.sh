#!/bin/bash

remote_log_url_base="https://192.168.0.150/Log/" //hier die IP auf eure eigene der Senec anpassen
local_log_directory="log/" //hier das log-Verzeichnis angeben. Liegt die .sh in einem anderen Verezcihnis als htdocs, so muss dies angepasst werden
log_output_file="/tmp/senec_log.log" //kann unverändert bleiben oder auch nach belieben angepasst werden

# Funktion zum Starten des Skripts
start() {
    if [ -e "$log_output_file" ]; then
        rm "$log_output_file"
    fi

    nohup /bin/bash "$0" run > "$log_output_file" 2>&1 &
    echo "Skript gestartet. Ausgabe in $log_output_file"
}

# Funktion zum Stoppen des Skripts
stop() {
    pids=$(pgrep -f "$0 run")
    if [ -n "$pids" ]; then
        kill $pids
        echo "Skript gestoppt."
    else
        echo "Skript läuft nicht."
    fi
}

# Funktion zum Anzeigen des Status
status() {
    pids=$(pgrep -f "$0 run")
    if [ -n "$pids" ]; then
        echo "Skript läuft (PID: $pids)."
		tail -f -n 200 $log_output_file
    else
        echo "Skript läuft nicht."
    fi
}

# Hauptausführung des Skripts
if [ "$1" == "run" ]; then
	while true; do
		current_year=$(date -u +"%Y")
		current_month=$(date -u +"%m")
		current_date=$(date -u +"%Y/%m/%d")
	
		remote_log_url="${remote_log_url_base}${current_date}.log"
	
		local_year_directory="${local_log_directory}${current_year}/"
		local_month_directory="${local_year_directory}${current_month}/"
		local_log_file="${local_log_directory}${current_date}.log"

		# Verzeichnisse erstellen, wenn sie nicht vorhanden sind
		mkdir -p "$local_year_directory"
		mkdir -p "$local_month_directory"

		# Logdatei von der Remote-URL herunterladen
		wget --no-check-certificate -O "$local_log_file" "$remote_log_url"

		if [ $? -eq 0 ]; then
			echo "Logdatei aktualisiert: $local_log_file"
		else
			echo "Fehler beim Herunterladen der Logdatei. Exit-Code: $?"
		fi

		# Kurze Pause, bevor erneuter Versuch
		sleep 5  # Hier können Sie das Intervall anpassen, wie oft die Logdatei überprüft wird
	done
	
else
    # Verarbeiten Sie Befehle "start", "stop" und "status"
    case "$1" in
        start)
            start
            ;;
        stop)
            stop
            ;;
        status)
            status
            ;;
        *)
            echo "Verwendung: $0 {start|stop|status}"
            exit 1
            ;;
    esac
fi
