<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Form\ConnexionType;
use App\Entity\Employe;

class ConnexionController extends AbstractController
{


    #[Route('/connexion', name: 'app_connexion')]
    public function ConnexionAction(Request $request, ManagerRegistry $doctrine, $employe = null) 
    {
        
        if ($employe == null) {
             $employe = new Employe();
        }
        $form = $this->createForm(ConnexionType::class, $employe);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $login = $employe->getLogin();
            $mdp = md5($employe->getMdp());
            $employe = $doctrine->getManager()->getRepository(Employe::class)->findByLoginEtMdp($login, $mdp);
            if ($employe == null) {
                return $this->redirectToRoute('app_connexion');
            }
            else {
                $session = new Session();
                $session->set('employeConnecte', $employe);
                if ($employe->getStatut() == 0) { // 0 -> Employé | 1 -> Administrateur
                    return $this->redirectToRoute('app_interfacePrincipaleEmploye');
                }
                else {
                    return $this->redirectToRoute('app_interfacePrincipaleAdmin');
                }
            }
        }
        return $this->render('connexion/connexion.html.twig', array('form'=>$form->createView()));
    }

    #[Route('/deconnexion', name: 'app_deconnexion')]
    public function DeconnexionAction(Session $session, Request $request, ManagerRegistry $doctrine) 
    {
        $session->set('employeConnecte', null);  
        return $this->redirectToRoute('app_connexion');
    }


}
