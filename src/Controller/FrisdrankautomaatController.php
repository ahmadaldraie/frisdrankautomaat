<?php 
declare(strict_types = 1);

namespace App\Controller;

use App\Entity\Admin;
use App\Entity\Frisdrank;
use App\Entity\Muntje;
use App\Exceptions\AdminBestaatNietException;
use App\Exceptions\GeenWisselingException;
use App\Exceptions\NietOpVoorraadException;
use App\Exceptions\OnvoldoendeSaldoException;
use App\Exceptions\PasswordWrongException;
use App\Exceptions\UsernameReedsInGebruikException;
use App\Exceptions\WachtwoordenKomenNietOvereenException;
use App\Form\Type\LoginType;
use App\Form\Type\RegisterType;
use App\Form\Type\FrisdrankType;
use App\Service\FrisdrankautomaatService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FrisdrankautomaatController extends AbstractController {    

    #[Route('/', name: 'app_home')]
    function home(FrisdrankautomaatService $frisdrankautomaat, Request $request) {
        $frisdranken = $frisdrankautomaat->getFrisdranken();
        $muntjes = $frisdrankautomaat->getDeMuntjes();
        $session = $request->getSession();
        $anyDate = new DateTime('now');

        if ($session->has('feedback')) {
            $feedback = $session->get('feedback');
            $session->remove('feedback');
        } else
            $feedback = 'welkom';

        if ($feedback === 'besteld') {
            $terugGeld = $session->get('saldo');
            try {
                $frisdrankautomaat->checkWisseling($session->get('saldo'), $muntjes);
                $frisdrankautomaat->doeDeBestelling();
                $terugGeld = $session->get('saldo');
            } catch (GeenWisselingException $ex) {
                $feedback = 'geenWisseling';
            }
            $session->remove('saldo');
            $session->remove('ingestokenMuntjes');
            $session->remove('muntjesTerugTeGeven');
            $session->remove('gekozenFrisdrank');
        } else $terugGeld = 0;
        
        $saldo = round($session->get('saldo', 0),2);
            

        return $this->render('frisdrankautomaat.html.twig',[
            "frisdranken" => $frisdranken, 
            "muntjes" => $muntjes, 
            "saldo" => $saldo, 
            "feedback" => $feedback, 
            "terugGeld" => $terugGeld,
            "anyDate" => $anyDate,
        ]);
    }

    #[Route('/bestellen/{id}', name: 'app_bestellen')]
    function bestelEenFrisdrank(EntityManagerInterface $entityManager, Request $request, int $id) {

        $session = $request->getSession();
        $saldo = round($session->get('saldo', 0),2);
        
        $gekozenFrisdrank = $entityManager->find(Frisdrank::class, $id);

        try {
            if ($gekozenFrisdrank->getPrijs() > $saldo) {
                throw new OnvoldoendeSaldoException();
            } else {
                if ($gekozenFrisdrank->getAantal() === 0) {
                    throw new NietOpVoorraadException();
                } else {
                    $session->set('gekozenFrisdrank', $gekozenFrisdrank->getId());
                    $saldo -= round($gekozenFrisdrank->getPrijs(), 2);
                    $session->set('saldo', $saldo);
                    $session->set('feedback', 'besteld');
                    return $this->redirectToRoute('app_home');
                }
                
            }

        } catch (NietOpVoorraadException $ex) {
            $session->set('feedback', 'nietopvoorraad');
            return $this->redirectToRoute('app_home');
        } catch (OnvoldoendeSaldoException $ex) {
            $session->set('feedback', 'onvoldoend');
            return $this->redirectToRoute('app_home');
        }
    }

    #[Route('/muntjeInsteken/{id}', name: 'app_insteken')]
    function steekEenMuntjeIn(EntityManagerInterface $entityManager, Request $request, int $id) {
        $session = $request->getSession();
        $ingestokenMuntjes = $session->get('ingestokenMuntjes', []);
        $saldo = round($session->get('saldo', 0),2);
        
        $muntje = $entityManager->find(Muntje::class, $id);
        $ingestokenMuntjes[] = $muntje->getId();
        $session->set('ingestokenMuntjes', $ingestokenMuntjes);
        $saldo += (float) $muntje->getWaarde();
        $session->set('saldo', $saldo);
        return $this->redirectToRoute('app_home');
    }

    #[Route('/admin', name: 'app_admin')]
    function admin(Request $request, FrisdrankautomaatService $frisdrankautomaat) {
        $session = $request->getSession();
        if(!$session->has('admin')) {
            return $this->redirectToRoute('app_login');
        } else {
            $frisdranken = $frisdrankautomaat->getFrisdranken();
            $muntjes = $frisdrankautomaat->getDeMuntjes();

            return $this->render('adminDashboard.html.twig', [
                'frisdranken' => $frisdranken,
                'muntjes' => $muntjes
            ]);
        }
    }

    #[Route('/frisdrankWijzigen/{id}', name: 'app_frisdrankWijzigen')]
    function frisdrankWijzigen(Request $request, EntityManagerInterface $entityManager, int $id) {
        $session = $request->getSession();
        if(!$session->has('admin')) {
            return $this->redirectToRoute('app_login');
        } else {
            $frisdrank = $entityManager->find(Frisdrank::class, $id);
            $form = $this->createForm(FrisdrankType::class, $frisdrank);
            
            $form->handleRequest($request);

            return $this->render('frisdrankWijzigingForm.html.twig', [
                'frisdrank' => $frisdrank,
                'form' => $form
            ]);
        }
    }

    #[Route('/login', name: 'app_login')]
    function login(Request $request, EntityManagerInterface $entityManager) {
        $session = $request->getSession();
        if ($session->has('admin')) {
            return $this->redirectToRoute('app_admin');
        }
        $toegestanePogingen = 5;
        $uitsluitingsDuur = 300; // 5 minuten in seconden
        $buitengesloten = false;
        $resterendeTijd = 0;
        $form = $this->createForm(LoginType::class);

        if ($request->isMethod('POST')) {
            try {
                $data = $request->request->all();
                $admin = $entityManager->getRepository(Admin::class)->findOneBy([
                    'username' => $data['admin_username']
                ]);
                if (is_null($admin)) {
                    throw new AdminBestaatNietException();
                }
                $wachtwoordCorrect = password_verify($data['admin_wachtwoord'], $admin->getWachtwoord());
                if (!$wachtwoordCorrect) {
                    throw new PasswordWrongException();
                }
                $session->set('admin', $admin->getUsername());
                return $this->redirectToRoute('app_admin');
            } catch (AdminBestaatNietException $ex) {
                $this->addFlash('error', 'De username bestaat niet!');
                return $this->redirectToRoute('app_login');
            } catch (PasswordWrongException $ex) {
                $this->addFlash('error', 'Het Wachtwoord is fout! Probeer het opnieuw');
                if ($session->has('pogingen')) {
                    $session->set('pogingen',$session->get('pogingen')+1);
                } else {
                    $session->set('pogingen', 1);
                }
                return $this->redirectToRoute('app_login');
            }


        }

        if ($session->has('pogingen') && $session->get('pogingen') >= $toegestanePogingen) {
            if (!$session->has('uitsluitingsTijd')) {
                $session->set('uitsluitingsTijd', time() + $uitsluitingsDuur);
                $buitengesloten = true;
            } elseif ($session->get('uitsluitingsTijd') > time()) {
                $resterendeTijd = $session->get('uitsluitingsTijd') - time();
                $this->addFlash('error', 'U bent buitengesloten. Probeer het over' . floor($resterendeTijd/60) . 'minuten en' . $resterendeTijd%60 . 'seconden opnieuw.');
                $buitengesloten = true;
            } else {
                $session->remove('pogingen');
                $session->remove('uitsluitingsTijd');
            }
        }

        

        return $this->render('loginForm.html.twig',[
            'resterendeTijd' => $resterendeTijd, 
            'buitengesloten' => $buitengesloten,
            'form' => $form
        ]);
    }

    #[Route('/register', name: 'app_register')]
    function register(EntityManagerInterface $entityManager, Request $request) {
        $session = $request->getSession();
        if ($session->has('admin')) {
            return $this->redirectToRoute('app_admin');
        }
        $admin = new Admin();
        $form = $this->createForm(RegisterType::class, $admin, [
            'action' => $request->getRequestUri()
        ]);
        if ($request->isMethod('POST')) {
            try {
                $data = $request->request->all();
                if (!is_null($entityManager->getRepository(Admin::class)->findOneBy([
                    'username' => $data['admin_username']
                ]))) {
                    throw new UsernameReedsInGebruikException();
                }
                if ($data['admin_wachtwoord'] != $data['admin_herhaalWachtwoord']) {
                    throw new WachtwoordenKomenNietOvereenException();
                }

                $admin->setUsername($data['admin_username']);
                $admin->setWachtwoord($data['admin_wachtwoord']);
                $entityManager->persist($admin);
                $entityManager->flush();
                $session->set('admin', $admin->getUsername());
                return $this->redirectToRoute('app_admin');
            } catch (WachtwoordenKomenNietOvereenException $ex) {
                $this->addFlash('error', 'De wachtwoorden moeten overeenkomen!');
            } catch (UsernameReedsInGebruikException $ex) {
                $this->addFlash('error', 'De username bestaat al!');
            }
        } 

        return $this->render('registerForm.html.twig',[
            'admin' => $admin,
            'form' => $form
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(Request $request) {
        $session = $request->getSession();
        if($session->has('admin')) {
            $session->remove('admin');
        }

        return $this->redirectToRoute('app_home');
    }

}

?>