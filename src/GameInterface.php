<?php

namespace Life;

interface GameInterface
{
    public function run( string $inputFile, string $outputFile): void;
}