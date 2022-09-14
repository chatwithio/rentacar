<?php

namespace App\Service;

use App\Entity\Car;
use Doctrine\Persistence\ManagerRegistry;

use Doctrine\ORM\EntityManagerInterface;

class MessageProcessor
{

    private $doctrine;
    private $em;
    private $messageService;
    private $data = [];


    public function __construct(ManagerRegistry $doctrine, EntityManagerInterface $em, MessageService $messageService)
    {
        $this->doctrine = $doctrine;
        $this->em = $em;
        $this->messageService = $messageService;
    }

    public function process($content){


        if(isset($content['messages'])){

            foreach ($content['messages'] as $k => $message){

                $name = $content['contacts'][$k]['profile']['name'];

                if($message['type'] == 'text'){

                    $this->processXml();

                    $matricula = $message['text']['body'];
                    $matricula = strtoupper($matricula);
                    $matricula = str_replace(" ",'',$matricula);

                    if(isset($this->data[$matricula])){

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
                    }
                    else{
                        // error whatsapp
                        $this->messageService->sendWhatsAppText(
                            $content['contacts'][$k]['wa_id'],
                            "Hola,$name, este matricula no esta en nuestra BBDD."
                        );
                    }
                }
                elseif ($message['type'] == 'image'){

                    $this->processXml();

                    $lastCar = $this->doctrine->getRepository(Car::class)->getLastCarByWaId(
                        $content['contacts'][$k]['wa_id']
                    );

                    if(!$lastCar){
                        $this->messageService->sendWhatsAppText(
                            $content['contacts'][$k]['wa_id'],
                            "Lo siento,$name. No encontramos una matricula tuya en la ultima hora. Tienes que introducirlo de nuevo"
                        );
                    }
                    else{

                        $image = $this->messageService->getImage($message['image']['id']);
                        $mediaId= $this->messageService->postImage($image, $message['image']['mime_type']);

                        $this->messageService->sendWhatsAppImage(
                            $this->data[$lastCar->getMatricula()],
                            [$name,$content['contacts'][$k]['wa_id'],$lastCar->getMatricula()],
                            'matricula_foto',
                            'es',
                            'f6baa15e_fb52_4d4f_a5a0_cde307dc3a85',
                            $mediaId
                        );

                        $this->messageService->sendWhatsAppText(
                            $content['contacts'][$k]['wa_id'],
                            "Foto recibido y enviado a ".$this->data[$lastCar->getMatricula()]
                        );
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
                    if($field=='Matr1'){
                        $matricula = (string)$i->FormattedValue;
                        $matricula = str_replace(" ","",$matricula);
                    }

                    if($field=='Text21'){
                        $tel =(string)$i->TextValue;
                        $tel = str_replace("Telf.: ","",$tel);
                        $expl = explode(" ",$tel);
                        if(!str_starts_with($expl[0],'+')){
                            $tel = "34".$expl[0];
                        }
                        $tel = str_replace("+","",$tel);
                    }
                    //if(isset())
                    //$this->data[$matricula] = $tel;
                } catch (\Exception $exception) {
                    //print $exception->getMessage();
                    dd($exception->getMessage());
                }
            }
            if($matricula && $tel){
                $this->data[$matricula] = $tel;
            }
        }
    }
}
