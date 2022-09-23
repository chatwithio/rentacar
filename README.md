# Rent a Car (Whatsapp API)

## Installation

The project is build on Symfony 6 and the PHP version of the language being used is PHP 8.1. Install symfony on your
local machine

- ### Run the following command in order to set up the project

```bash
# Install dependencies
composer update
# or
composer install

# Migrate 
php bin/console doctrine:migrations:migrate

# Run Server
symfony server:start -d

# Add and set value in your .env.local for the following environment variables
ROOT_DIR=
```

- ### Using the whatsapp api

The `matricula` against the `phone number` to which the message has to be sent should be present in the XML file under
the directory `xml/resentrega.xml`

- ### Testing the whatsapp api
    - One way to test is sending three messages to the following whatsapp number `+34810101298` and go to the symfony
      profiler and get their request payloads and use them in `POSTMAN` or any other api tool. Following are the
      messages that should be sent
        - `Matricula` (This is the unique id against the mobile number in `xml/resentrega.xml`)
        - Upload an Image or Video <br/><br/>

    - Firstly, we need to send the whatsapp text with the `matricula` assigned to us so that our number can be stored in
      the database. This can be used as test payload against the url using `POSTMAN` or any other api tool.

      https://rentacar.wardcampbell.com/rentacar
      ```bash
      # REQUEST METHOD => POST
      # REQUEST PAYLOAD =>
      {
          "contacts": [
              {
                  "profile": {
                      "name": "NAME"
                  },
                  "wa_id": "NUMBER"
              }
          ],
          "messages": [
              {
                  "from": "NUMBER",
                  "id": "RANDOMLY_GENERATED_ID",
                  "text": {
                      "body": "MATRICULA"
                  },
                  "timestamp": "1663331304",
                  "type": "text"
              }
          ]
      }
      ```
      ```bash
      # RESPONSE
      {
        "message": "OK"
      }
      ```
      You should be receiving a whatsapp message asking to upload images of the car <br/><br/>

    - Now upload the media. This can be used as test payload against the url using postman or any other api tool.<br/>
      https://rentacar.wardcampbell.com/rentacar
      ```bash
      # FOR IMAGE
      # REQUEST METHOD => POST
      # REQUEST PAYLOAD =>
      {
          "contacts": [
              {
                  "profile": {
                      "name": "NAME"
                  },
                  "wa_id": "NUMBER"
              }
          ],
          "messages": [
              {
                  "from": "NUMBER",
                  "id": "RANDOMLY_GENERATED_ID",
                  "image": {
                      "id": "MEDIA_ID",
                      "mime_type": "image\/jpeg",
                      "sha256": "RANDOMLY_GENERATED"
                  },
                  "timestamp": "1663328754",
                  "type": "image"
              }
          ]
      }
      ```

      ```bash
      # FOR VIDEO
      # REQUEST METHOD => POST
      # REQUEST PAYLOAD =>
      {
          "contacts": [
              {
                  "profile": {
                      "name": "NAME"
                  },
                  "wa_id": "NUMBER"
              }
          ],
          "messages": [
              {
                  "from": "NUMBER",
                  "id": "NUMBER",
                  "timestamp": "1663334840",
                  "type": "video",
                  "video": {
                      "id": "MEDIA_ID",
                      "mime_type": "video\/mp4",
                      "sha256": "RANDOMLY_GENERATED"
                  }
              }
          ]
      }
      ```
      ```bash
      # RESPONSE
      {
        "message": "OK"
      }
      ```
    - The language of the messages sent to the user is processed in the WhatsApp Message Service Processor based on the
      `REFERENCIARES11` entity in the `resentrega.xml` file. The default language is `ESPANOL -> es`.
        - FRANCES -> FR
        - INGLES -> EN
        - ALEMAN -> DE
        - ESPANOL -> ES
    - The messages that are being received can be seen under the `/messages` URL, you need to be logged in to see the
      table. In the table, the statuses of the messages that are received sent are also visible.

GOOD LUCK :)