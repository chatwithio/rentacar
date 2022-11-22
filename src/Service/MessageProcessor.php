<?php

namespace App\Service;

use App\Entity\Car;
use App\Entity\Message;
use Doctrine\Persistence\ManagerRegistry;

use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\NoReturn;
use JetBrains\PhpStorm\Pure;

class MessageProcessor
{
    private ManagerRegistry $doctrine;
    private EntityManagerInterface $em;
    private MessageService $messageService;
    private array $data = [];

    public function __construct(ManagerRegistry $doctrine, EntityManagerInterface $em, MessageService $messageService)
    {
        $this->doctrine = $doctrine;
        $this->em = $em;
        $this->messageService = $messageService;
    }

    public function process($content): void
    {
        $messageRepository = $this->doctrine->getRepository(Message::class);

        if (isset($content['messages'])) {
            foreach ($content['messages'] as $k => $message) {
                // save message
                $messageObj = new Message();
                $messageObj->setSent(false);
                $messageObj->setDelivered(false);
                $messageObj->setRead(false);
                $messageObj->setMessageFrom($content['contacts'][$k]['wa_id']);
                $messageObj->setMessageTo($content['contacts'][$k]['wa_id']);
                $name = $content['contacts'][$k]['profile']['name'];
                $type = $message['type'];
                $this->processXml();

                if ($type == 'text') {
                    $matricula = $message['text']['body'];
                    $matricula = strtoupper($matricula);
                    $matricula = str_replace(" ", '', $matricula);

                    if (isset($this->data[$matricula])) {
                        $messageObj->setMessageType('text');
                        $messageObj->setMessageContent($matricula);

                        //save the data
                        $car = new Car();
                        $car->setMatricula($matricula);
                        $car->setWaId($content['contacts'][$k]['wa_id']);
                        $car->setTelFrom($this->data[$matricula]['tel']);
                        $car->setCreated(new \DateTime('now'));
                        $this->em->persist($car);
                        $this->em->flush();

                        // send WhatsApp message into different language
                        $this->sendTextToWhatsappByLanguage(
                            $content['contacts'][$k]['wa_id'],
                            $this->data[$matricula]['lang'],
                            "Salut $name, vous pouvez commencer à nous envoyer des photos/vidéos!",
                            "Hallo $name, Sie können anfangen, uns fotos/videos zu schicken!",
                            "Hola $name, puedes empezar a enviarnos photos/videos!",
                            "Hello $name, you can start sending us photos/videos!"
                        );

                        $messageRepository->add($messageObj, true);
                    } else {
                        // error whatsapp
                        $this->sendTextToWhatsappByLanguage(
                            $content['contacts'][$k]['wa_id'],
                            $this->getISOLanguageCodeUsingNumber($content['contacts'][$k]['wa_id']),
                            "Salut $name, cette inscription n'est pas dans notre base de données.",
                            "Hallo $name, diese Registrierung ist nicht in unserem datenbank.",
                            "Hola $name, este matricula no esta en nuestra base de datos.",
                            "Hello $name, this registration is not in our database."
                        );

                        $this->menuBasedChatBot($content['contacts'][$k]['wa_id'], '0');
                    }
                } else {
                    $lastCar = $this->doctrine->getRepository(Car::class)->getLastCarByWaId(
                        $content['contacts'][$k]['wa_id']
                    );

                    if (!$lastCar) {
                        // send WhatsApp message into different language
                        $this->sendTextToWhatsappByLanguage(
                            $content['contacts'][$k]['wa_id'],
                            $this->getISOLanguageCodeUsingNumber($content['contacts'][$k]['wa_id']),
                            "Désolé $name. Nous n'avons pas trouvé de plaque d'immatriculation pour vous au cours de la dernière heure. vous devez le saisir à nouveau",
                            "Es tut mir leid $name, Wir haben in der letzten Stunde kein Nummernschild für dich gefunden. Sie müssen es erneut eingeben",
                            "Lo siento $name, No encontramos una matricula tuya en la ultima hora. Tienes que introducirlo de nuevo",
                            "I am sorry $name, We didn't find a license plate for you in the last hour. you have to enter it again",
                        );
                    } else {
                        $media = $this->messageService->getMedia($message[$type]['id']);
                        $mediaId = $this->messageService->postMedia($media, $message[$type]['mime_type']);
                        $messageObj->setMessageType($type);
                        $messageObj->setMessageContent($message[$type]['id']);

                        $this->messageService->sendWhatsAppMedia(
                            $this->data[$lastCar->getMatricula()]['tel'],
                            [$name, $content['contacts'][$k]['wa_id'], $lastCar->getMatricula()],
                            $this->getMediaTemplateUsingLanguage($lastCar->getMatricula(), strtoupper($type)),
                            $this->getISOLanguageCode($lastCar->getMatricula()),
                            $_ENV['WHATSAPP_TEMPLATE_NAMESPACE'],
                            $type,
                            $mediaId
                        );

                        $this->sendTextToWhatsappByLanguage(
                            $content['contacts'][$k]['wa_id'],
                            $this->data[$lastCar->getMatricula()]['lang'],
                            ($type == 'image' ? "Photo" : "Vidéo") . " reçue et envoyée à " . $this->data[$lastCar->getMatricula()]['tel'],
                            ($type == 'image' ? "Foto" : "Video") . " erhalten und gesendet an " . $this->data[$lastCar->getMatricula()]['tel'],
                            ($type == 'image' ? "Foto" : "Video") . " recibido y enviado a " . $this->data[$lastCar->getMatricula()]['tel'],
                            ($type == 'image' ? "Photo" : "Video") . " received and sent to " . $this->data[$lastCar->getMatricula()]['tel']
                        );

                        $messageRepository->add($messageObj, true);
                    }
                }
            }
        }
    }

    public function updateMessageStatus(array $content): void
    {
        $messageRepository = $this->doctrine->getRepository(Message::class);

        if (isset($content['statuses'])) {
            foreach ($content['statuses'] as $status) {
                if ($status['type'] == 'message') {
                    $message = $messageRepository->getLastMessageByWaId($status['recipient_id']);

                    if ($message) {
                        if ($status['status'] === 'sent') {
                            $message->setSent(true);
                        } else if ($status['status'] === 'delivered') {
                            $message->setDelivered(true);
                        } else if ($status['status'] === 'read') {
                            $message->setRead(true);
                        }

                        $messageRepository->add($message, true);
                    }
                }
            }
        }
    }

    private function processXml(): void
    {
        $xmldata = simplexml_load_file($_ENV['ROOT_DIR'] . "xml/resentrega.xml") or die("Failed to load");

        foreach ($xmldata->FormattedAreaPair->FormattedAreaPair as $k => $item) {
            $matricula = false;
            $tel = false;
            $language = '';


            foreach ($item->FormattedArea->FormattedSections->FormattedSection[0]->FormattedReportObjects->FormattedReportObject as $i) {
                try {
                    $field = (string)$i->ObjectName[0];

                    // matricula
                    if ($field == 'Matr1') {
                        $matricula = (string)$i->FormattedValue;
                        $matricula = str_replace(" ", "", $matricula);
                    }

                    if ($field == 'REFERENCIARES11') {
                        $language = (string)$i->FormattedValue;
                    }

                    if ($field == 'Text21') {
                        $tel = (string)$i->TextValue;
                        $tel = str_replace("Telf.: ", "", $tel);
                        $expl = explode(" ", $tel);
                        if (!str_starts_with($expl[0], '+')) {
                            $tel = "34" . $expl[0];
                        }
                        $tel = str_replace("+", "", $tel);
                    }

                    //if(isset())
                    //$this->data[$matricula] = $tel;
                } catch (\Exception $exception) {
                    //print $exception->getMessage();
                    dd($exception->getMessage());
                }
            }

            if ($matricula && $tel) {
                $this->data[$matricula]['tel'] = $tel;
                $this->data[$matricula]['lang'] = $language;
            }
        }
    }

    private function sendTextToWhatsappByLanguage(
        $whatsAppNumber,
        $lang,
        $frenchMessage,
        $germanMessage,
        $spanishMessage,
        $englishMessage
    ): void {
        switch ($lang) {
            case 'FRANCES':
                // send the message in french
                $this->messageService->sendWhatsAppText($whatsAppNumber, $frenchMessage);
                break;

            case 'ALEMAN':
                // send the message in german
                $this->messageService->sendWhatsAppText($whatsAppNumber, $germanMessage);
                break;

            case 'INGLES':
                // send the message in english
                $this->messageService->sendWhatsAppText($whatsAppNumber, $englishMessage);
                break;

            default:
                // send the message in spanish
                $this->messageService->sendWhatsAppText($whatsAppNumber, $spanishMessage);
                break;
        }
    }

    private function getISOLanguageCode(string $matricula): string
    {
        if ($this->data[$matricula]['lang'] === 'FRANCES')
            return 'fr';
        elseif ($this->data[$matricula]['lang'] === 'ALEMAN')
            return 'de';
        elseif ($this->data[$matricula]['lang'] === 'INGLES')
            return 'en';
        else
            return 'es';
    }

    #[Pure]
    private function getISOLanguageCodeUsingNumber(string $number): string
    {
        $code = substr($number, 0, 2);

        if ($code === '33')
            return 'fr';
        elseif ($code === '49')
            return 'de';
        elseif ($code === '44')
            return 'en';
        else
            return 'es';
    }

    private function getMediaTemplateUsingLanguage(string $matricula, string $type): string
    {
        if ($this->data[$matricula]['lang'] === 'FRANCES')
            return $_ENV['WHATSAPP_' . $type . '_TEMPLATE_FR'];
        elseif ($this->data[$matricula]['lang'] === 'ALEMAN')
            return $_ENV['WHATSAPP_' . $type . '_TEMPLATE_DE'];
        elseif ($this->data[$matricula]['lang'] === 'INGLES')
            return $_ENV['WHATSAPP_' . $type . '_TEMPLATE_EN'];
        else
            return $_ENV['WHATSAPP_' . $type . '_TEMPLATE_ES'];
    }

    public function menuBasedChatBot($number, $menuIndex, $subMenuIndex = ''): void
    {
        $menu = $this->menu();
        $subMenu = ['a' => 1, 'b' => 2, 'c' => 3];

        if ($subMenuIndex !== '') {
            if (array_key_exists($subMenu[$subMenuIndex], $menu[$menuIndex]['subQuestions'])) {
                $question = $menu[$menuIndex]['subQuestions'][$subMenu[$subMenuIndex]];

                if ($question['type'] === 'text') {
                    $this->messageService->sendWhatsAppText($number, $question['answer']);
                }
            }

            return;
        }

        if (array_key_exists($menuIndex, $menu)) {
            if ($menu[$menuIndex]['type'] === 'text') {
                $this->messageService->sendWhatsAppText($number, $menu[$menuIndex]['answer']);
            }

            if (sizeof($menu[$menuIndex]['subQuestions']) > 0) {
                $questions = '';
                foreach ($menu[$menuIndex]['subQuestions'] as $key => $question) {
                    $questions .= $menuIndex . array_flip($subMenu)[$key] . ' -> ' . $question['question'] . "\r\n";
                }
                $this->messageService->sendWhatsAppText(
                    $number,
                    $questions
                );
            }

            return;
        }

        $questions = '';
        foreach ($menu as $key => $question) {
            if ($question['type'] === 'text') {
               $questions .= $key . ' -> ' . $question['question'] . "\r\n";
            }
        }

        if ($questions !== '') {
            $this->messageService->sendWhatsAppText($number,  $questions);
        }
    }

    private function menu(): array
    {
        return [
            '1' => [
                'question' => 'Ya llegamos, dónde está el coche?',
                'type' => 'text',
                'answer' => 'Hola, en el parking público del aeropuerto. En las fotos enviadas verá la ubicación su coche alquilado',
                'namespace' => 'ubicación_coche',
                'subQuestions' => [],
            ],
            '2' => [
                'question' => 'Cómo llego al parking, aparcamiento?',
                'type' => 'text',
                'answer' => 'Hola, en qué Aeropuerto se encuentra? ',
                'namespace' => 'opciones_aeropuerto',
                'subQuestions' => [
                    '1' => [
                        'question' => 'Rodeos Tenerife Norte',
                        'type' => 'text',
                        'answer' => 'Después de recoger el equipaje, diríjase a los ascensores situados a la izquierda, pasado el mostrador de AENA o la Famacia. Ya en el ascensor, pulse el botón S-2 (planta Sótano 2). También puede bajar por las escaleras mecánicas',
                        'namespace' => 'equipaje_rodeos_tenerife_norte',
                        'subQuestions' => [],
                    ],
                    '2' => [
                        'question' => 'Reina Sofía Tenerife Sur',
                        'type' => 'text',
                        'answer' => 'Después de recoger su equipaje, salga de la terminal de llegadas por la puerta 10 ó 11 y siga todo recto hacia el parking público. Tb. puede serguir las indicaciones de cómo llegar al PUNTO de ENCUENTRO que está en el parking público.',
                        'namespace' => 'equipaje_reina_sofia_tenerife_sur',
                        'subQuestions' => [],
                    ]
                ]
            ],
            '3' => [
                'question' => 'Dónde está la llave?',
                'type' => 'text',
                'answer' => 'Hola, en la tapa de la gasolina del coche',
                'namespace' => 'ubicación_llave',
                'subQuestions' => []
            ],
            '4' => [
                'question' => 'Qué gasolina lleva?',
                'type' => 'text',
                'answer' => 'Hola, mire en el llavero de su coche alquilado, debajo de la matrícula viene el combustible que lleva.',
                'namespace' => 'tipo_gasolina',
                'subQuestions' => []
            ],
            '5' => [
                'question' => 'El coche está arañado, tiene un roce',
                'type' => 'text',
                'answer' => 'Hola, no se preocupe, saque fotos de los daños del coche y nos las remite por whatssapp al +34 65618 0379 o por email a: carmen@teneriferentacar.com .',
                'namespace' => 'estado_coche',
                'subQuestions' => []
            ],
            '6' => [
                'question' => 'Cómo se pone la silla de bebé?',
                'type' => 'video',
                'answer' => '',
                'namespace' => 'silla_bebe',
                'subQuestions' => []
            ],
            '7' => [
                'question' => 'Dónde se paga el ticket del parking?',
                'type' => 'text',
                'answer' => 'Hola, en qué Aeropuerto se encuentra?',
                'namespace' => 'ticket_parking',
                'subQuestions' => [
                    '1' => [
                        'question' => 'Rodeos Tenerife Norte',
                        'type' => 'text',
                        'answer' => 'Entre en la terminal y al lado de la puerta de Salida encontrará un Cajero.',
                        'namespace' => 'parking_rodeos_tenerife_norte',
                        'subQuestions' => []
                    ],
                    '2' => [
                        'question' => 'Reina Sofía Tenerife Sur',
                        'type' => 'text',
                        'answer' => 'En cualquier cajero del parking público, tiene el cajero central entre las filas B2 y B3',
                        'namespace' => 'parking_reina_sofia_tenerife_sur',
                        'subQuestions' => []
                    ],
                ]
            ],
            '8' => [
                'question' => 'Cómo recupero el dinero del parking?',
                'type' => 'text',
                'answer' => 'Hola, escríbanos un email a: carmen@teneriferentacar.com con sus datos, número de la reserva y una imagen buena del ticket.',
                'namespace' => 'recuperar_dinero_parking',
                'subQuestions' => []
            ],
            '9' => [
                'question' => 'El coche no está lleno',
                'type' => 'text',
                'answer' => 'Su reserva incluye el combustible? 1. NO 2. SI',
                'namespace' => 'estado_combustible',
                'subQuestions' => [
                    '1' => [
                        'question' => 'NO',
                        'type' => 'text',
                        'answer' => 'El coche lo tiene que devolver con la misma cantidad de combustible que lo en contró.',
                        'namespace' => 'mismo_estado_deposito',
                        'subQuestions' => []
                    ],
                    '2' => [
                        'question' => 'SI',
                        'type' => 'text',
                        'answer' => 'Si ha reservado el alquiler del coche con el servicio de combustible lleno y el coche no lo está, Saque una foto de la cantidad actual y póngase en contacto con Autos Plaza',
                        'namespace' => 'estado_lleno_lleno',
                        'subQuestions' => []
                    ],
                ]
            ],
            '10' => [
                'question' => 'El coche no arranca',
                'type' => 'text',
                'answer' => 'Qué modelo de coche tiene?',
                'namespace' => 'modelo_coche',
                'subQuestions' => [
                    '1' => [
                        'question' => 'Aygo',
                        'type' => 'text',
                        'answer' => 'Sigua las siguientes instrucciones: 1) Pise el embrague, 2) introduzca la llave y gírela para arrancar. Si no lo logra póngase en contacto con Autos Plaza',
                        'namespace' => 'arrancar_coche',
                        'subQuestions' => []
                    ],
                    '2' => [
                        'question' => 'Yaris',
                        'type' => 'text',
                        'answer' => 'Sigua las siguientes instrucciones: 1) Pise el embrague, 2) introduzca la llave y gírela para arrancar. Si no lo logra póngase en contacto con Autos Plaza',
                        'namespace' => 'arrancar_coche',
                        'subQuestions' => []
                    ],
                    '3' => [
                        'question' => 'Corsa',
                        'type' => 'text',
                        'answer' => 'Sigua las siguientes instrucciones: 1) Pise el embrague, 2) introduzca la llave y gírela para arrancar. Si no lo logra póngase en contacto con Autos Plaza',
                        'namespace' => 'arrancar_coche',
                        'subQuestions' => []
                    ]
                ]
            ]
        ];
    }
}
