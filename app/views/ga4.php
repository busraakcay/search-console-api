<div>
    <div class="userCountInfosContainer">
        <div class="userCountContainer">
            <p>
                Aktif kullanıcılar
            </p>
            <p>Son 5 dakika</p>
            <div class="activeUserBoxContainer">
                <p id="activeUserCount" class="activeUserBox">
                    <?php echo number_format($userCountLast5Min) ?>
                </p>
            </div>
        </div>
        <div class="userCountContainer">
            <p><b>Geçen hafta</b> aktif kullanıcılar</p>
            <p>
                <?php echo formatDateString($anotherLastWeek) . " - " . formatDateString($lastWeek) ?>
            </p>
            <div class="activeUserBoxContainer">
                <p class="activeUserBox">
                    <?php echo number_format($getTotalLastWeekActiveUsers) ?>
                </p>
            </div>
        </div>
        <div class="userCountContainer">
            <p><b>Bu hafta</b> aktif kullanıcılar</p>
            <p>
                <?php echo formatDateString($lastWeek) . " - " . formatDateString($initialDate) ?>
            </p>
            <div class="activeUserBoxContainer">
                <p class="activeUserBox">
                    <?php echo number_format($getTotalThisWeekActiveUsers) ?>
                </p>
            </div>
        </div>
        <div class="userCountContainer">
            <p>
                Aktif kullanıcı sayısında
            </p>
            <p><b><?php echo $getChangeRateAndValue[0] ?></b></p>
            <div class="activeUserBoxContainer">
                <p class="activeUserBox">
                    <?php echo $getChangeRateAndValue[1] ?>
                </p>
            </div>
        </div>
    </div>
    <div id="chartContainer" class="chartContainer"></div>
</div>

<script>
    function updateUserCount() {
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                var responseText = this.responseText;
                var userCount = extractNumberFromBody(responseText);
                if (!isNaN(userCount)) {
                    document.getElementById('activeUserCount').textContent = userCount;
                }
            }
        };
        xhttp.open("GET", "getActiveUserCount", true);
        xhttp.send();
    }

    function extractNumberFromBody(responseText) {
        var tempDiv = document.createElement("div");
        tempDiv.innerHTML = responseText;
        var bodyText = tempDiv.querySelector("p").innerHTML;
        var matches = bodyText.match(/\d+/);

        if (matches && matches.length > 0) {
            return parseInt(matches[0]);
        }

        return NaN;
    }

    updateUserCount();
    setInterval(updateUserCount, 10000);
</script>

<script>
    window.onload = function() {

        var chart = new CanvasJS.Chart("chartContainer", {
            animationEnabled: true,

            title: {
                text: "Günlere Göre Aktif Kullanıcılar"
            },
            axisX: {
                title: "Günler"
            },
            axisY: {
                title: "Geçen Hafta Aktif Kullanıcılar",
                titleFontColor: "#4F81BC",
                lineColor: "#4F81BC",
                labelFontColor: "#4F81BC",
                tickColor: "#4F81BC"
            },
            axisY2: {
                title: "Bu Hafta Aktif Kullanıcılar",
                titleFontColor: "#C0504E",
                lineColor: "#C0504E",
                labelFontColor: "#C0504E",
                tickColor: "#C0504E"
            },
            legend: {
                cursor: "pointer",
                dockInsidePlotArea: true,
                itemclick: toggleDataSeries
            },
            data: [{
                type: "line",
                name: "Geçen Hafta",
                markerSize: 0,
                indexLabel: "{y}",
                indexLabelFontColor: "#4F81BC", // Change color here
                indexLabelFontSize: 11, // Change font size here
                toolTipContent: "Tarih: {label} <br> {name}: {y} kullanıcı",
                showInLegend: true,
                dataPoints: <?php echo json_encode($userDateCountLastWeek, JSON_NUMERIC_CHECK); ?>
            }, {
                type: "line",
                axisYType: "secondary",
                name: "Bu Hafta",
                markerSize: 0,
                indexLabel: "{y}",
                indexLabelFontColor: "#C0504E", // Change color here
                indexLabelFontSize: 11, // Change font size here
                toolTipContent: "Tarih: {label} <br> {name}: {y} kullanıcı",
                showInLegend: true,
                dataPoints: <?php echo json_encode($userDateCountThisWeek, JSON_NUMERIC_CHECK); ?>
            }]
        });
        chart.render();

        function toggleDataSeries(e) {
            if (typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
                e.dataSeries.visible = false;
            } else {
                e.dataSeries.visible = true;
            }
            chart.render();
        }
    }
</script>