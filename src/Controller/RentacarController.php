<?php

namespace App\Controller;

use App\Message\WhatsappNotification;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\MessageService;
use App\Entity\Car;
use Doctrine\Persistence\ManagerRegistry;



class RentacarController extends AbstractController
{
    private $data;


    #[Route('/rentacar', name: 'app_rentacar')]
    public function index(Request $request, MessageBusInterface $bus, ManagerRegistry $doctrine): Response
    {
        $content = $request->getContent();

        $bus->dispatch(new WhatsappNotification($content));

        return $this->json([
            'message' => 'OK',
        ]);
    }

    #[Route('/xml', name: 'app_xml')]
    public function processXml()
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
        dd($this->data);
    }

}


