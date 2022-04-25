<?php

namespace App\Controller\Quality;


use App\Domain\Ad;
use App\Classes\Base\ScoresBaseController;
use Symfony\Component\HttpFoundation\Response;
use App\Infrastructure\Persistence\InFileSystemPersistence;
use App\Classes\QualityQuantifier;


class ScoresController extends ScoresBaseController
{
    private $minQuality;
    
    //
    // case 1: para calcular la puntuación de todos los anuncios
    //
    public function showScoresV1 ( )
    {
        $quantities = [];
        $adsData    = InFileSystemPersistence::getAds();
        $min        = $this->getParameter('quantify.min');
       
        foreach ( $adsData as $key => $adData )
        {
            $quantities [ ] = $this->quantify ( $key, $adData, $min );
        }
        
        
        //
        // Muestra anuncios con los scores ya calculados.
        //
        // Quizá usar dd() no sea lo más óptimo pero cumple 
        // con el objetivo de la prueba.
        //
        dd ( $quantities );
        
        
        // Nunca llegará aquí.
        return new Response ( $quantities );
    }
    
    
    private function quantify ( $key, $array, $min ): Ad
    {
        $ad = new Ad ( $key, $array[0], $array[1], $array[2], $array[3], $array[4], $array[5], $array[6] );
        $ad->quantify ( QualityQuantifier::class, $min );
                
        return $ad;
    }
}