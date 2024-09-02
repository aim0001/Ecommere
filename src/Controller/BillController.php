<?php

namespace App\Controller;

use App\Repository\CommandeRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

class BillController extends AbstractController
{
    #[Route('/editor/commande/{id}/bill', name: 'app_bill')]
    public function index($id, CommandeRepository $commandeRepository): Response
    {
        $commande = $commandeRepository->find($id);


        $pdfOptions = new Options();
        $pdfOptions->set('default', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true);
        $pdfOptions->set('isHtml5ParserEnabled', true);

        $domPdf = new Dompdf($pdfOptions);
        $domPdf->setPaper('A4', 'portrait');

        //  // Chemin absolu pour l'image du logo
        $logoPath = $this->getParameter('app_url'). '/Images/logo.png';

        $html = $this->renderView('bill/index.html.twig', [
            'commande' => $commande,
            'logo_path' => $logoPath,
        ]);

        $domPdf->loadHtml($html);

        $domPdf->render();

        $domPdf->stream("belbois-facture-".$commande->getId().'pdf', [
            'Attachment' => false
        ]);

        return new Response('',200,[
            'Content-type' => 'application/pdf'
        ]);
    }

    #[Route('/editor/commande/{id}/affiche', name: 'app_bill_affiche')]
    public function affiche($id, CommandeRepository $commandeRepository): Response
    {
        return $this->render('commande/affiche.html.twig', [
            'commande' => $commandeRepository->find($id),
            'image' => env('APP_URL'). '/Images/logo.png',
        ]);
    }
}
