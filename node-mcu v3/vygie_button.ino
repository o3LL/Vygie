#include <ESP8266WiFi.h>


const int  buttonPin = 2;
const int  buttonPin1 = 0;
const int  buttonPin2 = 4;
const int  buttonPin3 = 5;
const int ledPin = 13;       



const char ssid[] = "";
const char password[] = "";

const char *host = "";
// Variables will change:

int buttonState = 0;         // current state of the button
int lastButtonState = 0;     // previous state of the button

int buttonState1 = 0;         // current state of the button
int lastButtonState1 = 0;     // previous state of the button

int buttonState2 = 0;         // current state of the button
int lastButtonState2 = 0;     // previous state of the button

int buttonState3 = 0;         // current state of the button
int lastButtonState3 = 0;     // previous state of the button

void setup() {
  // initialize the button pin as a input:
  pinMode(buttonPin, INPUT);
  pinMode(buttonPin1, INPUT);
  pinMode(buttonPin2, INPUT);
  pinMode(buttonPin3, INPUT);
  // initialize the LED as an output:
  pinMode(ledPin, OUTPUT);
  // initialize serial communication:
  Serial.begin(9600);
}


void loop() {
 
  buttonState = digitalRead(buttonPin);
  buttonState1 = digitalRead(buttonPin1);
  buttonState2 = digitalRead(buttonPin2);
  buttonState3 = digitalRead(buttonPin3);



    

  
  if (buttonState != lastButtonState) {
   
    if (buttonState == HIGH) {
     
      Serial.println("on");
      Serial.print("encore un enfant avec la gastro. ");

      send_event(1);
    } else {

      Serial.println("off");
    }
   
    delay(50);
  }
   if (buttonState1 != lastButtonState1) {

    if (buttonState1 == HIGH) {
 

      Serial.println("on");
      Serial.print("encore un enfant avec des poux.   ");

      send_event(2);
      }
      else {

      Serial.println("off");
    }

    delay(50);
  }
   if (buttonState2 != lastButtonState2) {

    if (buttonState2 == HIGH) {
 

      Serial.println("on");
      Serial.print("encore un enfant avec la varisselle.   ");

      send_event(3);
      }
      else {

      Serial.println("off");
    }

    delay(50);
  }
   if (buttonState3 != lastButtonState3) {

    if (buttonState3 == HIGH) {
 

      Serial.println("on");
      Serial.print("encore un enfant avec la grippe.   ");

      send_event(4);
      }
      else {

      Serial.println("off");
    }

    delay(50);
  }
  lastButtonState = buttonState;
  lastButtonState1 = buttonState1;
  lastButtonState2 = buttonState2;
  lastButtonState3 = buttonState3;




}

void send_event(int dis)
{

  digitalWrite(ledPin, HIGH);

  Serial.print("Connecting to ");
  Serial.println(host);

  WiFiClient client;
  const int httpPort = 80;
  if (!client.connect(host, httpPort)) 
  {
    Serial.println("Connection failed");
    return;
  }

  String url = "";

  if (dis == 1)
  {
      url ="/api/add/1234/A";
  }
  if (dis == 2)
  {
      url ="/api/add/1234/B";
  }
  
  if (dis == 3)
  {
      url ="/api/add/1234/C";
  }
  if (dis == 4)
  {
      url ="/api/add/1234/D";
  }

  
  Serial.print("Requesting URL: ");
  Serial.println(url);
  

  client.print(String("GET ") + url + " HTTP/1.1\r\n" +
               "Host: " + host + "\r\n" + 
               "Connection: close\r\n\r\n");

 
  while(client.connected())
  {
    if(client.available())
    {
      String line = client.readStringUntil('\r');
      Serial.print(line);
    }
    else 
    {

      delay(50);
    };
  }
  

  Serial.println();
  Serial.println("closing connection");

  client.stop();
  

  digitalWrite(ledPin, HIGH);
}