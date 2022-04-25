<?php

declare(strict_types=1);


namespace App\Domain;


use DateTimeImmutable;



final class Ad
{
    public function __construct(
        private int $id,
        private String $typology,
        private String $description,
        private array $pictures, // App\Domain\Picture  // estaría bien implementar un iterador
        private int $houseSize,
        private ?int $gardenSize = null,
        private ?int $score = null,
        private ?DateTimeImmutable $irrelevantSince = null,
    ) {
        //
    }
    
    
    public function quantify ( $quantifier, $min ) :int
    {
        return $quantifier::Quantify ( $this, $min );
    }
    
    
    //
    // Aunque esto no sea buena práctica porque ignora las declaraciones 
    // private de __contruct() me gusta dejarlo aquí para demostrar que 
    // se puede usar.
    // 
    // Esto me evita tener que cear todos los getters.
    // 
    // Por otro lado ¿no pensáis que se abusa demasiado de los getters 
    // y los setters?
    //
    function __get ( $name )
    {
        return $this->$name;
    }
    
        
    //
    // Creado por cortesía profesional
    //
    function getTypology ( )
    {
        return $this->typology;
    }
    
    
    function setScore ( ?int $score )
    {
        $this->score = $score;
    }
    
    
    function incScore ( int $score ): int
    {
        $this->setScore ( $this->score + $score );
        
        return $this->score;
    }
    
    
    function getFinalScore ( ): int
    {
        $score = $this->score;
        
        if ( $score < 0 )
        {
            $score = 0;
        }
        
        return (int) $score;
    }
    
    
    function setIrrelevant ( )
    {
        if ( !$this->irrelevantSince )
        {
            $this->irrelevantSince = new DateTimeImmutable();
        }
    }
}

