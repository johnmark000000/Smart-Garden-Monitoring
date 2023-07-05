<?php
//-------------------------------------------------------------------------------------------
require 'config.php';

$db;
$sql = "SELECT * FROM history ORDER BY id DESC LIMIT 30";
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
    <title>Plant Monitoring System</title>
    <link rel="icon" href="images/sgmLogo.png" type="image/x-icon" />
    <link rel="stylesheet" href="style.css" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
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
      <div class="hero-container">
        <div class="column-left">
          <h1>Monitor Your Plant Remotely</h1>
          <p>Project on Computer Network and Security</p>
        </div>

        <div class="column-right">
          <div class="sensor-box">
            <h2>Soil Moisture Level</h2>
            <div class="sensor-value">
              <span id="moistureVal"><span id="soilMoistureLevel"></span>
            </div>
          </div>

          <div class="sensor-box">
            <h2>Humidity</h2>
            <div class="sensor-value">
              <span id="humidityVal"><span id="humidity"></span>
              %
            </div>
          </div>

          <div class="sensor-box">
            <h2>Temperature</h2>
            <div class="sensor-value">
              <span id="temperatureVal"><span id="temperature"></span>
              °C
            </div>
          </div>
        </div>
      </div>
    </section>
    <script>
    function updateData() {
      $.ajax({
        url: 'getdata.php',
        dataType: 'json',
        success: function(data) {
          $('#temperature').text(data.temperature);
          $('#humidity').text(data.humidity);
          $('#soilMoistureLevel').text(data.soilMoistureLevel);
        },
        error: function(xhr, status, error) {
          console.log('Error:', error);
        },
        complete: function() {
          setTimeout(updateData, 5000); // Fetch data every 5 seconds
        }
      });
    }

    // Start updating data
    updateData();
  </script>
  </body>
</html>
