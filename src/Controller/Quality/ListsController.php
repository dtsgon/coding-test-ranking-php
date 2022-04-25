<?php

namespace App\Controller\Quality;


use App\Classes\Base\ListsBaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Domain\Ad;
use App\Classes\QualityQuantifier;
use App\Infrastructure\Persistence\InFileSystemPersistence;


class ListsController extends ListsBaseController
{
    private $minQuality;
    
    
    public function __construct(  )
    {
        //
        // En otras parte he optado por usar constantes para almacenar datos.
        //
        // En este caso he preferido utilizar config/sercices.yml. Para ello uso
        // la clase AbstractController de Symfony extendiendo ListsBaseController,
        // de la que a su vez, extiende la clase actual.
        // 

        $this->minQuality = 33; //$this->getParameter('quantify.min');
    }


    //
    // case 2: listar los anuncios para un usuario de idealista
    // 
    // 1. Debe estar ordenado de mayor a menor score
    // 2. No mostrar anuncios con score <40
    //
    public function listAdByUserV1 ( Request $request )
    {
        // Opcional, no se usa
        $userId = $request->get("userId");        
        
        
        $ads = $this->quantify_case2();
        dd ( $ads );
        
        // Nunca llegará aquí.
        return new Response ( "Woohoo, el endpoint del user" );
    }
    
    
    
    //
    // case 3: listar los anuncios para el responsable
    // 
    // 1. Lista anuncios para el resposable
    // 2. Muestra los anuncios irrelevantes
    // 3. Muestra la fecha desde cuando son irrelevantes
    //
    public function listAdsForManagerV1()
    {
        $ads = $this->quantify_case3();
        dd ( $ads );
        
        // Nunca llegará aquí.
        return new Response ( "Hey! soy el endpoint del Manager" );
    }
    
    
    

    
    private function quantify_case2 ( ): array
    {
        $ads     = [ ];
        $return  = [ ];
        $adsData = InFileSystemPersistence::getAds ( );
        $min     = $this->getParameter('quantify.min');

        foreach ( $adsData as $key => $adData )
        {
            $ad = new Ad ( $key, $adData[0], $adData[1], $adData[2], $adData[3], $adData[4], $adData[5], $adData[6] );
            $ad->quantify ( QualityQuantifier::class, $this->minQuality );
            
            $score = $ad->getFinalScore();
            
            if ( $score >= $min )
            {
                //
                // Usar $score como índice. 
                // 
                // Como puede haber anuncos con igual score 
                // el subarray ayudará a evitar colisiones.
                //
                $ads [ $score ] [ ] = $ad; 
            }
        }
        
        
        //
        // Ordenación easy peasy.
        // Ahora hay que desacoplar el subarray.
        // 
        // De un foreach a otro no he cambiado mis opiniones 
        // sobre los array_*($callback)
        //
        foreach ( $ads as $array )
        {
            foreach ( $array as $ad )
            {
               $return [] = $ad;
            }
        }
                
        return $return;
    }

    
    private function quantify_case3 ( ): array
    {
        $ads     = [ ];
        $adsData = InFileSystemPersistence::getAds ( );
        $min     = $this->getParameter('quantify.min');
   
        foreach ( $adsData as $key => $adData )
        {
            $ad    = new Ad ( $key, $adData[0], $adData[1], $adData[2], $adData[3], $adData[4], $adData[5], $adData[6] );
            $ad->quantify ( QualityQuantifier::class, $this->minQuality );
            
            $score = $ad->getFinalScore();

            if ( $score < $min )
            {
                $ads [ ] = $ad; 
            }
        }
        
        return $ads;
    }
}