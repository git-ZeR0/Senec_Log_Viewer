#!/bin/bash

remote_log_url_base="https://192.168.0.150//log/" //hier die IP auf eure eigene der Senec anpassen
local_log_directory="log/" //hier das log-Verzeichnis angeben. Liegt die .sh in einem anderen Verezcihnis als htdocs, so muss dies angepasst werden
log_output_file="/tmp/senec_log.log" //kann unverändert bleiben oder auch nach belieben angepasst werden

# Timeout in Millisekunden
timeout_ms=5000

# Mindestwartezeit in Sekunden bis zum erneuten Abruf des Logfiles
min_wait=30

# Addiere Sekunden bei jedem fehgeschlagenen Abruf
add_wait=10

# Maximale Wartezeit in Sekunden (5 Minuten)
max_wait=300

# Aktuelle Wartezeit Start
current_wait=$min_wait

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

        # Temporäre Datei für das Herunterladen erstellen
        temp_log_file="/tmp/temp_log.log"
    
        # Logdatei von der Remote-URL herunterladen
        wget --timeout=${timeout_ms} --no-check-certificate -O "$temp_log_file" "$remote_log_url"
    
        if [ $? -eq 0 ]; then
            # Erfolgreich heruntergeladen, kopiere temp Logfile nach local_log_file
            mv "$temp_log_file" "$local_log_file"
            echo "Logdatei aktualisiert: $local_log_file"
			current_wait=$min_wait  # Zurücksetzen auf Mindestwartezeit
        else
            echo "Fehler beim Herunterladen der Logdatei. Exit-Code: $?"
            rm -f "$temp_log_file"  # Lösche temp Logfile
			current_wait=$((current_wait + add_wait))  # Wartezeit erhöhen
            if [ $current_wait -gt $max_wait ]; then
                current_wait=$max_wait  # Begrenzen auf maximale Wartezeit
            fi
        fi

		# Wartezeit vor erneutem Abruf
		sleep $current_wait
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
