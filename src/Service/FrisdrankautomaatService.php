<?php 
declare(strict_types = 1);

namespace App\Service;

use App\Entity\Frisdrank;
use App\Entity\Muntje;
use App\Exceptions\GeenWisselingException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class FrisdrankautomaatService {    
        private $request;
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CacheItemPoolInterface $cachePool,
        RequestStack $request_stack) {
            $this->request = $request_stack->getCurrentRequest();
    }

    public function getDeMuntjes() {
        $cacheKey = 'muntjes';

        $cachedItem = $this->cachePool->getItem($cacheKey);

        if (!$cachedItem->isHit()) {
            $muntjes = $this->entityManager->getRepository(Muntje::class)->findBy([], ['id' => 'DESC']);

            $cachedItem->set($muntjes);

            $this->cachePool->save($cachedItem);
        } else {
            $muntjes = $cachedItem->get();
        }

        return $muntjes;
    }

    public function getFrisdranken() {
        $cacheKey = 'frisdranken';

        $cachedItem = $this->cachePool->getItem($cacheKey);

        if (!$cachedItem->isHit()) {
            $frisdranken = $this->entityManager->getRepository(Frisdrank::class)->findAll();

            $cachedItem->set($frisdranken);

            $this->cachePool->save($cachedItem);
        } else {
            $frisdranken = $cachedItem->get();
        }

        return $frisdranken;
    }

    public function checkWisseling() {
        $session = $this->request->getSession();
        $remainSaldo = $session->get('saldo');
        $muntjes = $this->getDeMuntjes();
        $muntjesTerugTeGeven = [];
        foreach ($muntjes as $muntje) {
            $aantalMuntjesTerug = floor(round($remainSaldo, 2)/round($muntje->getWaarde(), 2));
            if ($aantalMuntjesTerug > 0) {
                if ($aantalMuntjesTerug > $muntje->getAantal()) {
                    if ($muntje->getWaarde() == 0.1) {
                        throw new GeenWisselingException();    
                    }          
                } else {
                    array_push($muntjesTerugTeGeven, ['muntjeId' => $muntje->getId(), 'aantalMuntjesTerug' => $aantalMuntjesTerug]);
                    $remainSaldo = round($remainSaldo, 2)-round($aantalMuntjesTerug*$muntje->getWaarde(), 2);
                }    
            }
            if ($remainSaldo == 0) {
                $session->set('muntjesTerugTeGeven', $muntjesTerugTeGeven);
                break;
            }
        }
    }

    public function doeDeBestelling() {
        $session = $this->request->getSession();

        foreach($session->get('ingestokenMuntjes') as $muntstuk) {
            $muntje = $this->entityManager->find(Muntje::class,$muntstuk);
            $muntje->setAantal($muntje->getAantal()+1);
            $this->entityManager->flush();     
        }
        foreach($session->get('muntjesTerugTeGeven') as $muntstuk) {
            $muntje = $this->entityManager->find(Muntje::class,$muntstuk['muntjeId']);
            $muntje->setAantal((int)($muntje->getAantal()-(int)$muntstuk['aantalMuntjesTerug']));
            $this->entityManager->flush();
        }
        $gekozenFrisdrank = $this->entityManager->find(Frisdrank::class,$session->get('gekozenFrisdrank'));
        $gekozenFrisdrank->setAantal($gekozenFrisdrank->getAantal()-1);
        $this->entityManager->flush();
        $this->cachePool->deleteItem('muntjes');
        $this->cachePool->deleteItem('frisdranken');

    }

}

?>