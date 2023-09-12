<?php
session_start();

// Überprüfen und speichern Sie das ausgewählte Jahr, Monat und Tag
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['year'])) {
        $_SESSION['selectedYear'] = $_POST['year'];
    }
    if (isset($_POST['month'])) {
        $_SESSION['selectedMonth'] = $_POST['month'];
    }
    if (isset($_POST['day'])) {
        $_SESSION['selectedDay'] = $_POST['day'];
    }
	if (isset($_POST['reset'])) {
		// Wenn der "Reset"-Button geklickt wurde, löschen Sie die Session-Daten
		unset($_SESSION['selectedYear']);
		unset($_SESSION['selectedMonth']);
		unset($_SESSION['selectedDay']);
	}
}

$currentYear = date("Y");
$selectedYear = isset($_SESSION['selectedYear']) ? $_SESSION['selectedYear'] : $currentYear;
$currentMonth = date("n");
$selectedMonth = isset($_SESSION['selectedMonth']) ? $_SESSION['selectedMonth'] : $currentMonth;
$currentDay = date("j");
$selectedDay = isset($_SESSION['selectedDay']) ? $_SESSION['selectedDay'] : $currentDay;
?>
<!doctype html>
<head>
	<meta charset=utf-8>
	<meta http-equiv=Content-Type content="text/html; charset=utf-8">
	<script src=./js/jquery-3.6.1.min.js type=text/javascript></script>
	<script src=./js/senec_values.js type=text/javascript></script>
	<script src=./js/sort-table.min.js type=text/javascript></script>
	<style>
		body 			{font-family: Verdana; font-size: 0.9em;}
		.toggleCheckboxes:hover { cursor: pointer; background-color:#bee9f7 } 
		.sev-Debug		{background-color:gray;color:#a9a9a9}
		.sev-Info		{background-color:#add8e6;color:#00f}
		.sev-Warning	{background-color:#00f;color:#fff}
		.sev-Error		{background-color:#bb1500;color:#fff}
		.sev-Panic		{background-color:#000;color:#fff}
		.sev-None		{background-color:#d3d3d3;color:#000}
		.msg-booting	{border-top:4pt solid #000}
		.top-bar		{position:fixed;z-index:999;top:0;left:0;width:100%;border-bottom:3px solid #000;background-color:#fff}
		.top-placeholder{top:0;left:0;width:100%;background-color:#fff;visibility:hidden}
		.bottom-scroll	{position:fixed;z-index:999;bottom:0;left:0;width:100%;border-top:3px solid #000;background-color:#fff}
		table,td,th		{border:2px solid #000;border-collapse:collapse}
		//form input, form select	{ background-color: #add8e6; border-radius: 5px; }
	</style>
</head>

<body>
<div class=top-bar>
<div style="padding: 5px; margin: 0 0 5px 0;">
	<img height="20" src=images/Header_Logo.gif>   <span style="font-size: 1.6em; font-weight: bold;">Log Viewer</span>
	&nbsp;&nbsp;&nbsp;&nbsp;[Logifile: <span id="outputSPAN"></span>]&nbsp;&nbsp;&nbsp;&nbsp;
	<form id="dateSelectionForm" action="" method="post" style="display: inline;">
        <label for="year"></label>
        <select id="year" name="year" onchange="this.form.submit()">
            <?php
            for ($year = 2020; $year <= $currentYear; $year++) {
                $selected = ($year == $selectedYear) ? "selected" : "";
                echo "<option value='$year' $selected>$year</option>";
            }
            ?>
        </select>

        <label for="month"></label>
        <select id="month" name="month" onchange="this.form.submit()">
            <?php
            for ($month = 1; $month <= 12; $month++) {
                $monthName = date("F", mktime(0, 0, 0, $month, 1, 2000)); // Vollständiger Monatsname
                $selected = ($month == $selectedMonth) ? "selected" : "";
                echo "<option value='$month' $selected>$monthName</option>";
            }
            ?>
        </select>

        <label for="day"></label>
        <select id="day" name="day" onchange="this.form.submit()">
            <?php
            for ($day = 1; $day <= 31; $day++) {
                $selected = ($day == $selectedDay) ? "selected" : "";
                echo "<option value='$day' $selected>$day</option>";
            }
            ?>
        </select>
		
		- <input type="submit" name="reset" value="show current log">
    </form>
	&nbsp;&nbsp;&nbsp;&nbsp;last update:<span id=textSecLastUpdate>0</span>
</div>

<!--<p>Columns can be sorted by clicking on their headers. | Documentation:<a href=https://senec-ies.atlassian.net/wiki/x/sIDELw target=_blank>LogViewer (Confluence)</a>| Plain text logfile:<a href=log/latest target=_blank>Open Logfile</a>-->
<span class="toggleCheckboxes" style=background-color:#90ee90 onclick=toggleAutoScrollOnBottom()><input type=checkbox id=autoScrollOnBottom checked>Auto-Scroll on Bottom</span>
<span style=background-color:#add8e6>
	<span class="toggleCheckboxes" onclick='toggleSeverity("Debug")'><input type=checkbox id=cbSevDebug checked>Debug</span>
	<span class="toggleCheckboxes" onclick='toggleSeverity("Info")'><input type=checkbox id=cbSevInfo checked>Information</span>
	<span class="toggleCheckboxes" onclick='toggleSeverity("Warning")'><input type=checkbox id=cbSevWarning checked>Warning</span>
	<span class="toggleCheckboxes" onclick='toggleSeverity("Error")'><input type=checkbox id=cbSevError checked>Error</span>
	<span class="toggleCheckboxes" onclick='toggleSeverity("Panic")'><input type=checkbox id=cbSevPanic checked>Panic</span>
	<span class="toggleCheckboxes" onclick='toggleSeverity("None")'><input type=checkbox id=cbSevNone checked>None</span>
</span>
</div>

<div class=top-placeholder>
	<p style=color:#fff>Placeholder<br>Placeholder<br>Placeholder<br>Placeholder<br>Placeholder<br>Placeholder<br>Placeholder<br>Placeholder<br>Placeholder<br>Placeholder<br>
</div>

<table id=logs class=js-sort-table>
<thead>
<tr bgcolor=lightgray>
<th class=js-sort-string>Date & Time
<th class=js-sort-string>Severity
<th class=js-sort-string>Source
<th class=js-sort-string>Caller/Part
<th class=js-sort-string>Message
</thead>
<tbody>
</tbody>
</table>
<br><br>
<hr>
<p>Log End</p>
<hr>
<div id=autoScrollStatus class=bottom-scroll>
	<p>Auto-Scrolling Status: active</p>
</div>

<script type=text/javascript>/* Prevent JSHint warning about supposingly unused functions. */
        /* exported toggleAutoScrollOnBottom, toggleSeverity */

        // map source name to icons, plain HTML is possible (Unicode, Images)
        const sourceToIcon = {
            "BMZ": "&#128267;", // battery
            "Samsung": "&#128267;", // battery
            "SYS": "&#128187;", // computer
            "INV_LV": "&#9889;", // high voltage
            "INV_HV": "&#9889;", // high voltage
        };

        // map severity IDs to names
        const sevToText = {
            "D": "Debug",
            "I": "Info",
            "W": "Warning",
            "E": "Error",
            "P": "Panic"
        };

        // global constants and variables
        const logfile = new XMLHttpRequest();
        //const date = new Date();
        //const year = date.getFullYear();
        //const month = date.getMonth() + 1;
        //const day = date.getDate();
		<?php
	if (isset($_SESSION['selectedYear']) || isset($_SESSION['selectedMonth']) || isset($_SESSION['selectedDay'])){
		echo 'const year = '.$_SESSION['selectedYear'].';
		const month = "'.$_SESSION['selectedMonth'].'";
		const day = "'.$_SESSION['selectedDay'].'";';
	}
	else {
		echo '		const year = new Date().getUTCFullYear();
		const month = new Date().getUTCMonth() + 1; // Monate in JavaScript sind nullbasiert, daher +1
		const day = new Date().getUTCDate();';
	}
?>

        const logname = "log/" + padZero(year, 4) + "/" + padZero(month, 2) + "/" + padZero(day, 2) + ".log";
		//logname = "https://192.168.0.150/log/2023/09/10.log";
		var outputSPAN = document.getElementById("outputSPAN");
		outputSPAN.innerHTML = logname;
        const reDateSevSrc =
            /^(\d{4}-\d{2}-\d{2}) (\d{2}:\d{2}:\d{2}) \[([DIWEF])\|(.*?)\] (.*)/;
        const reDateSevSrcCall =
            /^(\d{4}-\d{2}-\d{2}) (\d{2}:\d{2}:\d{2}) \[([DIWEF])\|(.*?)\|(.*?)\] (.*)/;
        const reDateSrc =
            /^(\d{4}-\d{2}-\d{2}) (\d{2}:\d{2}:\d{2}) (.*?): (.*)/;
        const reDate = /^(\d{4}-\d{2}-\d{2}) (\d{2}:\d{2}:\d{2}) (.*)/;
        var lineHistory = {};
        var autoScrollOnBottom = true;
        var scrolledToBottom = true;
        var prevScrolledToBottom = true;
        var lastScrollTop = 0;
        var scrollDirectionBottom = true;
        var initialDownScroll = true;
        var updateTimestamp = new Date();
        var updateTimeout_ms = 1000;
        var timerPollInterval_ms = 200;
        var timerPoll = setInterval(logfileRequest, timerPollInterval_ms);
        var successfulUpdateTs = new Date();
		
		document.getElementById("year").addEventListener("change", submitForm);
		document.getElementById("month").addEventListener("change", submitForm);
		document.getElementById("day").addEventListener("change", submitForm);

		// Diese Funktion sendet das Formular automatisch
		function submitForm() {
			document.getElementById("dateSelectionForm").submit();
		}
		
		function handleDateSelection(event) {
			event.preventDefault(); // Verhindert das Standardverhalten des Formulars (Seitenneuladen)

			const selectedYear = document.getElementById("year").value;
			const selectedMonth = document.getElementById("month").value;
			const selectedDay = document.getElementById("day").value;

			// Erstellen Sie den Pfad zur ausgewählten Logdatei
			const selectedLogname = `log/${selectedYear}/${selectedMonth}/${selectedDay}.log`;

			// Setzen Sie die Logdatei neu, um die ausgewählte Logdatei abzurufen
			logname = selectedLogname;
			outputSPAN.innerHTML = logname;

			// Rufen Sie die Logdatei ab
			logfileRequest();
		}

        // get severity name from severity id, see dict sevToText
        function getTextFromSeverity(severity)
        {
            if(severity in sevToText)
            {
                return sevToText[severity];
            }

            return severity;
        }

        // get symbol/icon for source name, see dict sourceToIcon
        function getSymbolForSource(source)
        {
            if(source in sourceToIcon)
            {
                return sourceToIcon[source];
            }

            return "";
        }

        // get CSS class by message
        // info: this is used for example to show a divider bar when the system is booting
        function getClassByMessage(message)
        {
            if(message === "System is booting ...")
            {
                return "booting";
            }

            return "none";
        }

        // parse and show a single log line
        function loglineParse(line)
        {
            var match;
            var tableDate = "";
            var tableTime = "";
            var tableSev = "None";
            var tableSource = "";
            var tableCaller = "";
            var tableMessage = line;

            // strip whitespaces
            line = line.trim();

            // skip empty lines
            if(line === "")
            {
                return;
            }

            // skip processed lines
            if(line in lineHistory)
            {
                return;
            }
            lineHistory[line] = true;

            if((match = reDateSevSrcCall.exec(line)) !== null)
            {
                tableDate = match[1];
                tableTime = match[2];
                tableSev = getTextFromSeverity(match[3]);
                tableSource = match[4];
                tableCaller = match[5];
                tableMessage = match[6];
            }
            else if((match = reDateSevSrc.exec(line)) !== null)
            {
                tableDate = match[1];
                tableTime = match[2];
                tableSev = getTextFromSeverity(match[3]);
                tableSource = match[4];
                tableMessage = match[5];
            }
            else if((match = reDateSrc.exec(line)) !== null)
            {
                tableDate = match[1];
                tableTime = match[2];
                tableSource = match[3];
                tableMessage = match[4];
            }
            else if((match = reDate.exec(line)) !== null)
            {
                tableDate = match[1];
                tableTime = match[2];
                tableMessage = match[3];
            }

            $('#logs').find('tbody').append(`
                    <tr class="sev-` + tableSev + ` msg-` + getClassByMessage(
                    tableMessage) + `">
                        <td style="white-space: nowrap">` + tableDate + ` ` +
                tableTime + `</td>
                        <td style="white-space: nowrap">` + tableSev + `</td>
                        <td style="white-space: nowrap">` + getSymbolForSource(
                    tableSource) + ` ` + tableSource + `</td>
                        <td style="white-space: nowrap">` + tableCaller + `</td>
                        <td>` + tableMessage + `</td>
                    </tr>`);

            // message added, scrolling necessary
            scrolledToBottom = false;
        }

        // callback when the logfile was successfully read
        function logfileResponse()
        {
            // check if document is ready for parsing
            if(logfile.readyState !== 4)
            {
                return;
            }

            // check if file was found
            if(logfile.status !== 200)
            {
                return;
            }

            // remember if current entries are scrolled to bottom
            prevScrolledToBottom = scrolledToBottom;

            // parse lines
            allText = logfile.responseText;
            lines = logfile.responseText.split("\n");
            lines.forEach(line => loglineParse(line));

            // scroll to bottom on load
            if(initialDownScroll === true)
            {
                initialDownScroll = false;
                window.scrollTo(0, document.body.scrollHeight);
            }

            // scroll to document bottom (if conditions are met)
            if(prevScrolledToBottom === true)
            {
                scrollToBottom();
            }

            // update successful timestamp
            successfulUpdateTs = new Date();

            // start next request
            updateTimeout_ms = 5000;
        }

        // pad a number to a given places-count of zeroes
        function padZero(number, places)
        {
            return String(number).padStart(places, '0');
        }

        // return time between timestamps in milliseconds
        function timeSince(timestamp)
        {
            return Math.floor(new Date() - timestamp);
        }

        // send an asynchronous logfile request
        function logfileRequest()
        {
            // update text "seconds since last update"
            document.getElementById("textSecLastUpdate").textContent =
                Math.floor(timeSince(successfulUpdateTs) / 1000);

            if(timeSince(updateTimestamp) < updateTimeout_ms)
            {
                return;
            }

            // stop timer
            clearTimeout(timerPoll);
            timerPoll = undefined;

            logfile.open("GET", logname, true);
            logfile.onreadystatechange = logfileResponse;
            logfile.send(null);

            // restart timer
            updateTimestamp = new Date();
            timerPoll = setInterval(logfileRequest, timerPollInterval_ms);
        }

        // callback for the auto-scroll checkbox
        function toggleAutoScrollOnBottom()
        {
            var toggledFlag = !$("#autoScrollOnBottom").prop("checked");

            $("#autoScrollOnBottom").prop("checked", toggledFlag);
            autoScrollOnBottom = toggledFlag;
        }

        // callback for the severity checkboxes
        function toggleSeverity(severityName)
        {
            var cbName = "#cbSev" + severityName;
            var cssName = ".sev-" + severityName;
            var toggledFlag = !$(cbName).prop("checked");
            var cssDisplay = toggledFlag ? "" : "none";

            $(cbName).prop("checked", toggledFlag);
            $(cssName).css(
            {
                "display": cssDisplay
            });
        }

        // callback for the JS scroll event
        function handleScroll()
        {
            const bottomTolerance = 2;

            if(($(window).scrollTop() + $(window).height()) >= ($(document)
                    .height() - bottomTolerance))
            {
                scrolledToBottom = true;
                $("#autoScrollStatus").show();
            }
            else
            {
                scrolledToBottom = false;
                $("#autoScrollStatus").hide();
            }

            // detect scroll direction
            if(lastScrollTop > $(window).scrollTop())
            {
                // scrolling up
                scrollDirectionBottom = false;
            }
            else
            {
                // scrolling down;
                scrollDirectionBottom = true;
            }

            // update last scroll position
            lastScrollTop = $(window).scrollTop();
        }

        // slowly scroll to bottom
        function scrollToBottom()
        {
            if(autoScrollOnBottom !== true)
            {
                return;
            }

            // abort if scroll direction is reversed (by user)
            if(scrollDirectionBottom === false)
            {
                return;
            }

            // scroll by 1 px
            window.scrollBy(0, 1);

            // stop when bottom is reached
            if(scrolledToBottom === true)
            {
                return;
            }

            setTimeout(scrollToBottom, 5);
        }

        // start logfile request when the page loads
        console.log("Logfile: " + logname);
        window.addEventListener("scroll", handleScroll);
        window.addEventListener("load", logfileRequest);
</script>
</body>
</html>
