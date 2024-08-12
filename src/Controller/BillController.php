<?php

namespace App\Controller;

use App\Repository\CommandeRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BillController extends AbstractController
{
    #[Route('/editor/commande/{id}/bill', name: 'app_bill')]
    public function index($id, CommandeRepository $commandeRepository): Response
    {
        $commande = $commandeRepository->find($id);


        $pdfOptions = new Options();
        $pdfOptions->set('default', 'Arial');

        $domPdf = new Dompdf($pdfOptions);

        $html = $this->renderView('bill/index.html.twig', [
            'commande' => $commande]);

        $domPdf->loadHtml($html);

        $domPdf->render();

        $domPdf->stream("belbois-facture-".$commande->getId().'pdf', [
            'Attachment' => false
        ]);

        return new Response('',200,[
            'Content-type' => 'application/pdf'
        ]);
    }
}
