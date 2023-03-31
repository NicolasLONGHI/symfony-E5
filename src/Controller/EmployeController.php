<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Entity\Inscription;
use App\Entity\Formation;
use App\Entity\Employe;
use App\Controller\verifConnexionController;

class EmployeController extends AbstractController
{

    

    #[Route('/interfacePrincipaleEmploye', name: 'app_interfacePrincipaleEmploye')]
    public function interfacePrincipaleEmployeAction(Session $session, ManagerRegistry $doctrine) 
    {
        $retour = $doctrine->getManager()->getRepository(Employe::class)->verifConnexion($session, 0);
        if ($retour != null) { 
            return $this->redirectToRoute($retour);
        }
        
        $lesFormations = $doctrine->getManager()->getRepository(Formation::class)->findOnlyNewer();
        if (!$lesFormations) {
            $message = "Pas de formations disponible";
        }
        else {
            $message = null;
        }

        return $this->render('employe/interfacePrincipale.html.twig', array('lesFormations'=>$lesFormations, 'message'=>$message));
    }

    #[Route('/creerInscription/{id}', name: 'app_creer_inscription')]
    public function CreerInscriptionAction(Session $session, $id, ManagerRegistry $doctrine) 
    {
        $retour = $doctrine->getManager()->getRepository(Employe::class)->verifConnexion($session, 0);
        if ($retour != null) { 
            return $this->redirectToRoute($retour);
        }

        $employe = $doctrine->getManager()->getRepository(Employe::class)->find($session->get('employeConnecte')->getId());
        $formation = $doctrine->getManager()->getRepository(Formation::class)->find($id);
        $inscription = new Inscription();

        $inscription->setStatut("enCours");
        $inscription->setLemploye($employe);
        $inscription->setLaFormation($formation);

        $entityManager = $doctrine->getManager();
        $entityManager->persist($inscription);
        $entityManager->flush();
        
        return $this->redirectToRoute('app_interfacePrincipaleEmploye');
    }
}
