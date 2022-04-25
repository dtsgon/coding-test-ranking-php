<?php

namespace App\Classes;


use App\Domain\Ad;
use App\Interfaces\QualityQuantifierInterface;


class QualityQuantifier implements QualityQuantifierInterface
{
    const CHALET_COND      = +50;
    const CHALET_SCORE     = +20;
    const CHALET_SCORE_ALL = +40;
    
    const GARAGE_SCORE_ALL = +40;
    
    const FLAT_COND_1      = +20;
    const FLAT_COND_2      = +49;
    const FLAT_SCORE_1     = +30;
    const FLAT_SCORE_2     = +10;
    const FLAT_SCORE_ALL   = +40;
    
    const SCORE_PICTURE    = -10;
    const SCORE_DESC       = +5;
    const SCORE_KEYWORDS   = +5;

    const SCORE_IMAGE_HD   = +20;
    const SCORE_IMAGE_SD   = +10;
    

    
    const TYPOLOGIES = [
        'CHALET' => 'QuantifyChalet',
        'GARAGE' => 'QuantifyGarge',
        'FLAT'   => 'QuantifyFlat',
    ];
    
    const IMAGE_TYPES = [
        'HD' => self::SCORE_IMAGE_HD,
        'SD' => self::SCORE_IMAGE_SD,
    ];            
    
    const KEYWORDS = [
        'LUMINOSO', 
        'NUEVO', 
        'CÉNTRICO', 
        'REFORMADO', 
        'ÁTICO',
    ];
    
    
    
   
    //
    // A pesar de no ser buena práctica, elijo pasar $min como parámetro.
    // Lo ideal sería obtener $min de los parámetros de  config/services
    // 
    // $min es el mínimo por el cual un anuncio se procesará como irrelevante
    //  
    public static function Quantify ( Ad $ad, int $min ) :int
    {
        $description = strtoupper ( $ad->description );
        
        self::scorePictures            ( $ad );
        self::scoreDescriptiveText     ( $ad, $description );
        self::scoreDescriptionKeywords ( $ad, $description );

        $typology = $ad->getTypology();
        $function = self::TYPOLOGIES [ $typology ] ?? false;

        if ( $function )
        {
            self::$function ( $ad, $description );
        }
        
        $score = $ad->getFinalScore();
        
        if ( $score < $min )
        {
            $ad->setIrrelevant ( );
        }
            
        return $score;
    }
    
    
    
    public static function QuantifyChalet ( Ad $ad, string $description )
    {
        $len = strlen ( $description );
        
        if ( $len > self::CHALET_COND ) 
        {
            $ad->incScore ( self::CHALET_SCORE );
        }        
        
        if ( $len  AND  $ad->pictures  AND  $ad->houseSize  AND $ad->gardenSize )
        {
            $ad->incScore ( self::CHALET_SCORE_ALL );
        }
    }
    
    
    public static function QuantifyGarge ( Ad $ad, string $description )
    {
        if ( $ad->pictures )
        {
            $ad->incScore ( self::GARAGE_SCORE_ALL );
        }
    }
    
    
    public static function QuantifyFlat ( Ad $ad, string $description )
    {
        $len = strlen ( $description );
        
        if ( $len >= self::FLAT_COND_1 ) 
        {
            $score = $len > self::FLAT_COND_2 ? self::FLAT_SCORE_1 : self::FLAT_SCORE_2;

            $ad->incScore ( $score );
        }
        
        if ( $len  AND  $ad->pictures  AND  $ad->houseSize )
        {
            $ad->incScore ( self::FLAT_SCORE_ALL );
        }
    }
    
    
    private static function scorePictures ( Ad $ad )
    {
        if ( !$ad->pictures )
        {
            $ad->incScore ( self::SCORE_PICTURE );
        }
        
        foreach ( $ad->pictures as $pic )
        {
            $type  = $pic [ 1 ];
            $score = self::IMAGE_TYPES [ $type ];
            
            $ad->incScore ( $score );
        }
    }
    
    
    private static function scoreDescriptiveText ( Ad $ad, string $description )
    {
        if ( strlen ( $description ) !== false )
        {
            $ad->incScore ( self::SCORE_DESC );
        }
    }
    
    
    private static function scoreDescriptionKeywords ( Ad $ad, string $description )
    {
        //
        // Este foreach() sería más cool usando una funcion tipo array_*() y un callback.
        // Aunque sería elegante y moderno, opino que reduce la legibilidad del código y 
        // dificulta la depuración.
        //
        foreach ( self::KEYWORDS as $keyword )
        {
            if ( strpos ( $description, $keyword ) !== false )
            {
                $ad->incScore ( self::SCORE_KEYWORDS );
            }
        }
    }
}