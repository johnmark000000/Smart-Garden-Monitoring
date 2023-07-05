<?php
//-------------------------------------------------------------------------------------------
require 'config.php';

$db;
$sql = "SELECT * FROM history ORDER BY id DESC LIMIT 15";
$result = $db->query($sql);
if (!$result) { {
        echo "Error: " . $sql . "<br>" . $db->error;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="style.css" />
    <link rel="icon" href="images/sgmLogo.png" type="image/x-icon" />
    <title>History</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://www.gstatic.com/charts/loader.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
  </head>

  <body>
    <nav>
      <div class="logo">Plant Monitoring System</div>
      <div class="nav-item">
        <a href="/smart garden monitoring/index.php">Home</a>
        <a href="/smart garden monitoring/about.php">History</a>
      </div>
    </nav>

    <section class="hero">
      <div class="heroH-container">
        <div class="top-section">
        <div id="chartContainer" class="chart-container">
            <canvas id="lineGraph"></canvas>
        </div>
        </div>
        <div class="bottom-section">
          <table class="table">
            <thead>
              <tr>
                <th scope="col">ID</th>
                <th scope="col">Time and Date</th>
                <th scope="col">Soil Moisture Level</th>
                <th scope="col">Humidity</th>
                <th scope="col">Temperature</th>
              </tr>
            </thead>
            <tbody id="table-body"></tbody>
          </table>
        </div>
      </div>
    </section>
    <script>
    function updateTable(data) {
      var tableBody = document.getElementById("table-body");
        var newRow = tableBody.insertRow(0);
        newRow.innerHTML = `
        <td>${data.id}</td>
        <td>${data.timeAndDate}</td>
        <td>${data.soilMoistureLevel}</td>
        <td>${data.humidity}%</td>
        <td>${data.temperature}°C</td>
      `;
    }

    function fetchData() {
      $.ajax({
        url: 'table.php', // Replace 'fetch_data.php' with the URL of your PHP script
        type: 'GET',
        dataType: 'json',
        success: function(data) {
          updateTable(data);
        },
        error: function(xhr, status, error) {
          console.error(error);
        }
      });
    }

    setInterval(fetchData, 5000); // Update the table every 5 seconds
  </script>
  <script>
        const ctx = document.getElementById('lineGraph').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                        label: 'Temperature',
                        borderColor: 'red',
                        fill: true,
                        data: []
                    },
                    {
                        label: 'Humidity',
                        borderColor: 'blue',
                        fill: true,
                        data: []
                    },
                    
                ]
            },
            options: {
                fontSize: 12,
                animation: {
                    duration: 1 // Disable animation for real-time updates
                },
                elements: {
                    line: {
                        tension: 0.4 // Adjust the tension for the curves (0.0 to 1.0)
                    }
                },
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Time'
                        }
                    },
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Value'
                        }
                    }
                }
            }
        });

        function refreshData() {
            var container = document.getElementById('chartContainer');
            var isMobile = window.matchMedia('(max-width: 700px)').matches;
            var height = isMobile ? chart.data.labels.length * 40 : 500;
            container.style.height = height + 'px';

            var maxDataPoints = 30;

            if (window.matchMedia('(max-width: 700px)').matches) {
                maxDataPoints = 5; // Adjust the maximum data points for mobile devices
            }

            // Limit the number of data points to display
            if (chart.data.labels.length > maxDataPoints) {
                var numToRemove = chart.data.labels.length - maxDataPoints;

                for (var i = 0; i < numToRemove; i++) {
                    chart.data.labels.shift();
                    chart.data.datasets.forEach((dataset) => {
                        dataset.data.shift();
                    });
                }
            }

            $.ajax({
                url: 'getdata.php',
                data: 'q=' + $("#users").val(),
                dataType: 'json',
                success: function(responseText) {
                    var created_date = responseText.created_date;
                    var formattedDate = moment(created_date).format('h:mm A');
                    var temperature = parseFloat(responseText.temperature).toFixed(2);
                    var humidity = parseFloat(responseText.humidity).toFixed(2);
                    //var gas = parseFloat(responseText.gas).toFixed(2);

                    // Add new data to the chart
                    chart.data.labels.push(formattedDate);
                    chart.data.datasets[0].data.push(temperature);
                    chart.data.datasets[1].data.push(humidity);
                    //chart.data.datasets[2].data.push(gas);

                    // Limit the number of data points to display
                    const maxDataPoints = 30;
                    if (chart.data.labels.length > maxDataPoints) {
                        chart.data.labels.shift();
                        chart.data.datasets.forEach((dataset) => {
                            dataset.data.shift();
                        });
                    }

                    // Update the chart
                    chart.update();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log(errorThrown + ': ' + textStatus);
                }
            });
        }

        // Call refreshData function periodically to update the chart
        setInterval(refreshData, 1000);
    </script>
  </body>
</html>
