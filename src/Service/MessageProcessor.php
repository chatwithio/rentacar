<?php

namespace App\Service;

use App\Entity\Car;
use App\Entity\Message;
use Doctrine\Persistence\ManagerRegistry;

use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\NoReturn;

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

    #[NoReturn]
    public function process($content)
    {
        $messageRepository = $this->doctrine->getRepository(Message::class);

        if (isset($content['messages'])) {
            foreach ($content['messages'] as $k => $message) {
                $message = new Message();
                $message->setSent(false);
                $message->setDelivered(false);
                $message->setRead(false);
                $message->setMessageFrom($content['contacts'][$k]['wa_id']);
                $message->setMessageTo($content['contacts'][$k]['wa_id']);
                $name = $content['contacts'][$k]['profile']['name'];

                if ($message['type'] == 'text') {
                    $this->processXml();

                    $matricula = $message['text']['body'];
                    $matricula = strtoupper($matricula);
                    $matricula = str_replace(" ", '', $matricula);

                    if (isset($this->data[$matricula])) {
                        $message->setMessageType('text');
                        $message->setMessageContent($matricula);

                        //save the data
                        $car = new Car();
                        $car->setMatricula($matricula);
                        $car->setWaId($content['contacts'][$k]['wa_id']);
                        $car->setTelFrom($this->data[$matricula]);
                        $car->setCreated(new \DateTime('now'));
                        $this->em->persist($car);
                        $this->em->flush();

                        //send the message
                        $this->messageService->sendWhatsAppText(
                            $content['contacts'][$k]['wa_id'],
                            "Hola,$name, puedes empezar a enviarnos photos!"
                        );

                        $messageRepository->add($message, true);
                    } else {
                        // error whatsapp
                        $this->messageService->sendWhatsAppText(
                            $content['contacts'][$k]['wa_id'],
                            "Hola,$name, este matricula no esta en nuestra BBDD."
                        );
                    }
                } elseif ($message['type'] == 'image') {
                    $this->processXml();

                    $lastCar = $this->doctrine->getRepository(Car::class)->getLastCarByWaId(
                        $content['contacts'][$k]['wa_id']
                    );

                    if (!$lastCar) {
                        $this->messageService->sendWhatsAppText(
                            $content['contacts'][$k]['wa_id'],
                            "Lo siento,$name. No encontramos una matricula tuya en la ultima hora. Tienes que introducirlo de nuevo"
                        );
                    } else {
                        $image = $this->messageService->getMedia($message['image']['id']);
                        $mediaId = $this->messageService->postMedia($image, $message['image']['mime_type']);
                        $message->setMessageType('image');
                        $message->setMessageContent($message['image']['id']);

                        $this->messageService->sendWhatsAppMedia(
                            $this->data[$lastCar->getMatricula()],
                            [$name, $content['contacts'][$k]['wa_id'], $lastCar->getMatricula()],
                            'matricula_foto',
                            'es',
                            'f6baa15e_fb52_4d4f_a5a0_cde307dc3a85',
                            'image',
                            $mediaId
                        );

                        $this->messageService->sendWhatsAppText(
                            $content['contacts'][$k]['wa_id'],
                            "Foto recibido y enviado a " . $this->data[$lastCar->getMatricula()]
                        );

                        $messageRepository->add($message, true);
                    }
                } elseif ($message['type'] == 'video') {
                    $this->processXml();

                    $lastCar = $this->doctrine->getRepository(Car::class)->getLastCarByWaId(
                        $content['contacts'][$k]['wa_id']
                    );

                    if (!$lastCar) {
                        $this->messageService->sendWhatsAppText(
                            $content['contacts'][$k]['wa_id'],
                            "Lo siento,$name. No encontramos una matricula tuya en la ultima hora. Tienes que introducirlo de nuevo"
                        );
                    } else {
                        $video = $this->messageService->getMedia($message['video']['id']);
                        $mediaId = $this->messageService->postMedia($video, $message['video']['mime_type']);
                        $message->setMessageType('video');
                        $message->setMessageContent($message['video']['id']);

                        $this->messageService->sendWhatsAppMedia(
                            $this->data[$lastCar->getMatricula()],
                            [$name, $content['contacts'][$k]['wa_id'], $lastCar->getMatricula()],
                            'matricula_video',
                            'es',
                            'f6baa15e_fb52_4d4f_a5a0_cde307dc3a85',
                            'video',
                            $mediaId
                        );

                        $this->messageService->sendWhatsAppText(
                            $content['contacts'][$k]['wa_id'],
                            "Foto recibido y enviado a " . $this->data[$lastCar->getMatricula()]
                        );


                        $messageRepository->add($message, true);
                    }
                }
            }
        }
    }

    #[NoReturn]
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

    private function processXml()
    {
        $xmldata = simplexml_load_file($_ENV['ROOT_DIR'] . "xml/resentrega.xml") or die("Failed to load");

        foreach ($xmldata->FormattedAreaPair->FormattedAreaPair as $k => $item) {
            $matricula = false;
            $tel = false;

            foreach ($item->FormattedArea->FormattedSections->FormattedSection[0]->FormattedReportObjects->FormattedReportObject as $i) {
                try {
                    $field = (string)$i->ObjectName[0];

                    //matricula
                    if ($field == 'Matr1') {
                        $matricula = (string)$i->FormattedValue;
                        $matricula = str_replace(" ", "", $matricula);
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
                $this->data[$matricula] = $tel;
            }
        }
    }
}
