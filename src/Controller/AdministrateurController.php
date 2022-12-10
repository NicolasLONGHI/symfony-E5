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
use Symfony\Component\HttpFoundation\Request;
use App\Form\CreerFormationType;

class AdministrateurController extends AbstractController
{

    #[Route('/interfacePrincipaleAdmin', name: 'app_interfacePrincipaleAdmin')]
    public function interfacePrincipaleAdminAction(Session $session, ManagerRegistry $doctrine) 
    {
        $retour = $doctrine->getManager()->getRepository(Employe::class)->verifConnexion($session, 1);
        if ($retour != null) { 
            return $this->redirectToRoute($retour);
        }
        
        $lesInscriptions = $doctrine->getManager()->getRepository(Inscription::class)->findAll();
        if(!$lesInscriptions){
            $messageInscription = "Aucune inscription";
        }
        else{
            $messageInscription = null;
        }

        $lesFormations = $doctrine->getManager()->getRepository(Formation::class)->findAll();
        if(!$lesFormations){
            $messageFormation = "Aucune formation";
        }
        else{
            $messageFormation = null;
        }
        
        return $this->render('administrateur/interfacePrincipale.html.twig', array('lesInscriptions'=>$lesInscriptions, 'messageInscription'=>$messageInscription, 'lesFormations'=>$lesFormations, 'messageFormation'=>$messageFormation));
    }
    
    
    #[Route('/creerFormation', name: 'app_creer_formation')]
    public function creerFormationAction(Session $session, Request $request, ManagerRegistry $doctrine, $formation = null) 
    {
        
        $retour = $doctrine->getManager()->getRepository(Employe::class)->verifConnexion($session, 1);
        if ($retour != null) { 
            return $this->redirectToRoute($retour);
        }
        
        if ($formation == null) {
             $formation = new Formation();
        }
        $form = $this->createForm(CreerFormationType::class, $formation);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em = $doctrine->getManager();
            $em->persist($formation);
            $em->flush();
            return $this->redirectToRoute('app_interfacePrincipaleAdmin');
        }
        return $this->render('administrateur/creerFormation.html.twig', array('form'=>$form->createView()));
    } 


    #[Route('/supprimerUneFormation/{id}', name: 'app_supprimer_une_formation')]
    public function supprimerFormationAction(Session $session, $id, ManagerRegistry $doctrine) 
    {
        $retour = $doctrine->getManager()->getRepository(Employe::class)->verifConnexion($session, 1);
        if ($retour != null) { 
            return $this->redirectToRoute($retour);
        }

        $message = null;
        $formation = $doctrine->getManager()->getRepository(Formation::class)->find($id);
        if ($formation) {
            $inscription = $doctrine->getManager()->getRepository(Inscription::class)->verifInscriExiste($id);
            if (count($inscription) == 0) { //Seulement s'il n'y a aucune inscription
                $entityManager = $doctrine->getManager();
                $entityManager->remove($formation);
                $entityManager->flush();
            }
        }
        return $this->redirectToRoute('app_interfacePrincipaleAdmin');
    }


    #[Route('/refuserUneFormation/{id}', name: 'app_refuser_une_formation')]
    public function refuserFormationAction(Session $session, $id, ManagerRegistry $doctrine) 
    {
        $retour = $doctrine->getManager()->getRepository(Employe::class)->verifConnexion($session, 1);
        if ($retour != null) { 
            return $this->redirectToRoute($retour);
        }

        $inscription = $doctrine->getManager()->getRepository(Inscription::class)->find($id);

        $inscription->setStatut("refuser");

        $entityManager = $doctrine->getManager();
        $entityManager->persist($inscription);
        $entityManager->flush();
        
        return $this->redirectToRoute('app_interfacePrincipaleAdmin');
    }

    
    #[Route('/accepterUneFormation/{id}', name: 'app_accepter_une_formation')]
    public function accepterFormationAction(Session $session, $id, ManagerRegistry $doctrine) 
    {  
        $retour = $doctrine->getManager()->getRepository(Employe::class)->verifConnexion($session, 1);
        if ($retour != null) { 
            return $this->redirectToRoute($retour);
        }

        $inscription = $doctrine->getManager()->getRepository(Inscription::class)->find($id);

        $inscription->setStatut("accepter");

        $entityManager = $doctrine->getManager();
        $entityManager->persist($inscription);
        $entityManager->flush();
        
        return $this->redirectToRoute('app_interfacePrincipaleAdmin');
    }
}
