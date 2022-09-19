# Rent a Car (Whatsapp API)
## Installation

The project is build on Symfony 6 and the PHP version of the language being used is PHP 8.1.
Install symfony on your local machine

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
```

- ### Using the whatsapp api
The `matricula` against the `phone number` to which the message has to be sent should be present in the XML file under the directory `xml/resentrega.xml`


- ### Testing the whatsapp api
  - One way to test is sending three messages to the following whatsapp number `+34810101298` and go to the symfony profiler and get their request payloads and use them in `POSTMAN` or any other api tool. Following are the messages that should be sent  
    - `Matricual` (This is the unique id against the mobile number in `xml/resentrega.xml`)
    - Upload an Image or Video <br/><br/>
      
  - Firstly, we need to send the whatsapp text with the `matricula` assigned to us so that our number can be stored in the database. This can be used as test payload against the url using `POSTMAN` or any other api tool.

    https://rentacar.wardcampbell.com/rentacar
    ```bash
    # REQUEST METHOD => POST
    # REQUEST PAYLOAD =>
    {
        "contacts": [
            {
                "profile": {
                    "name": "Hamza Awan"
                },
                "wa_id": "923241494612"
            }
        ],
        "messages": [
            {
                "from": "923241494612",
                "id": "ABEGkjJBSUYSAgo6Ipgzxau9Jez6",
                "text": {
                    "body": "0376 JZH"
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
                    "name": "Hamza Awan"
                },
                "wa_id": "923241494612"
            }
        ],
        "messages": [
            {
                "from": "923241494612",
                "id": "ABEGkjJBSUYSAgo6P7oMl-Kcasdm",
                "timestamp": "1663334840",
                "type": "video",
                "video": {
                    "id": "bcc2e89a-ffb1-4bf2-8dd8-90aa68d707db",
                    "mime_type": "video\/mp4",
                    "sha256": "a7014b886e64805d5d9cffa03647926a2f054a410fb93ecc5008b9df6a678050"
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
    
GOOD LUCK :)