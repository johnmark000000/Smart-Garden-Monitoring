#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClient.h>

#include "DHT.h"  // DHT11 library
#define DHTPIN 2  // DHT11 - connected to pin 2
#define DHTTYPE DHT11
DHT myDHT(DHTPIN, DHTTYPE);
float h, t;

// Soil moisture sensor, analog configuration - connected to A0
const int soilPin = A0;
int soilMoisture;
String soilLevel;

// WiFi credentials
const char* ssid = "NON FAMILY";
const char* password = "expecttheunexpected";

//enter domain name and path
//http://www.example.com/sensordata.php
const char* SERVER_NAME = "http://192.168.1.2/smart%20garden%20monitoring/sensordata.php";

//PROJECT_API_KEY is the exact duplicate of, PROJECT_API_KEY in config.php file
//Both values must be same
String PROJECT_API_KEY = "5fsdsf2ev5FF";

//Send an HTTP POST request every 30 seconds
unsigned long lastMillis = 0;
long interval = 1000;

void setup() {
    //-----------------------------------------------------------------
  Serial.begin(115200);
  Serial.println("esp32 serial initialize");
  //-----------------------------------------------------------------
  myDHT.begin();
  Serial.println("initialize DHT11");
  //-----------------------------------------------------------------
  WiFi.begin(ssid, password);
  Serial.println("Connecting");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("");
  Serial.print("Connected to WiFi network with IP Address: ");
  Serial.println(WiFi.localIP());

  Serial.println("Timer set to 5 seconds (timerDelay variable),");
  Serial.println("it will take 5 seconds before publishing the first reading.");
  //-----------------------------------------------------------------
}

void loop() {

  //-----------------------------------------------------------------
  //Check WiFi connection status
  if (WiFi.status() == WL_CONNECTED) {
    if (millis() - lastMillis > interval) {
      //Send an HTTP POST request every interval seconds
      upload_temperature();
      lastMillis = millis();
    }
  }
  //-----------------------------------------------------------------
  else {
    Serial.println("WiFi Disconnected");
  }
  //-----------------------------------------------------------------

  delay(1000);
}

void upload_temperature() {
  //--------------------------------------------------------------------------------
  //Sensor readings may also be up to 2 seconds 'old' (its a very slow sensor)
  //Read temperature as Celsius (the default)
  h = myDHT.readHumidity();
  t = myDHT.readTemperature();
  soilMoisture = analogRead(A0);

  if (isnan(h) || isnan(t)) {
    Serial.println(F("Failed to read from DHT sensor!"));
    return;
  } else if (isnan(soilMoisture)) {
    Serial.println("Failed to read from MQ-2 sensor!");
    return;
  }

  //--------------------------------------------------------------------------------
  //°C
  String humidity = String(h, 2);
  String temperature = String(t, 2);
  if (soilMoisture < 300) {
    soilLevel = "HIGH";
  } else if (soilMoisture >= 300 && soilMoisture < 950) {
    soilLevel = "MID";
  } else if (soilMoisture >= 950) {
    soilLevel = "LOW";
  }

  // Printing values
  Serial.print("Humidity: ");
  Serial.print(humidity);
  Serial.print("%  ");
  Serial.print("Temperature: ");
  Serial.print(temperature);
  Serial.print("℃  ");
  Serial.print("Soil Moisture Level: ");
  Serial.println(soilLevel);
  //--------------------------------------------------------------------------------
  //HTTP POST request data
  String temperature_data;
  temperature_data = "api_key=" + PROJECT_API_KEY;
  temperature_data += "&temperature=" + temperature;
  temperature_data += "&humidity=" + humidity;
  temperature_data += "&soilMoisture=" + soilLevel;

  Serial.print("temperature_data: ");
  Serial.println(temperature_data);
  //--------------------------------------------------------------------------------

  WiFiClient client;
  HTTPClient http;

  http.begin(client, SERVER_NAME);
  // Specify content-type header
  http.addHeader("Content-Type", "application/x-www-form-urlencoded");
  // Send HTTP POST request
  int httpResponseCode = http.POST(temperature_data);

  Serial.print("HTTP Response code: ");
  Serial.println(httpResponseCode);

  // Free resources
  http.end();
}
