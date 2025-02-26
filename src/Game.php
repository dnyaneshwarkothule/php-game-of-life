<?php declare(strict_types = 1);

namespace Life;

class Game implements GameInterface
{
    /** @var int */
    private $iterationsCount;

    /** @var int */
    private $size;

    /** @var int */
    private $species;

    /**
     * @var int[][]|null[][]
     * Array of available cells in the game with size x size dimensions
     * Indexed by y coordinate and than x coordinate
     */
    private $cells;

    /**
     * @var XmlFileReader
     */
    private $input;

    /**
     * @var XmlFileWriter
     */
    private $output;

    public function __construct( XmlFileReader $input, XmlFileWriter $output )
    {
        $this->input = $input;
        $this->output = $output;
    }

    public function run(string $inputFile, string $outputFile): void
    {
        [$size, $species, $cells, $iterationsCount] = $this->input->loadFile();

        $this->size = $size;
        $this->species = $species;
        $this->cells = $cells;
        $this->iterationsCount = $iterationsCount;

        for ($i = 0; $i < $this->iterationsCount; $i++) {
            $newCells = [];
            for ($y = 0; $y < $this->size; $y++) {
                $newCells[] = [];
                for ($x = 0; $x < $this->size; $x++) {
                    $newCells[$y][$x] = $this->evolveCell($x, $y);
                }
            }
            $this->cells = $newCells;
        }

        $this->output->saveWorld($this->size, $this->species, $this->cells);
    }

    private function evolveCell(int $x, int $y): ?int
    {
        $cell = $this->cells[$y][$x];
        $neighbours = [];

        if ($y - 1 >= 0 && $x - 1 >= 0) {
            $neighbours[] = $this->cells[$y - 1][$x - 1];
        }
        if ($y - 1 >= 0) {
            $neighbours[] = $this->cells[$y - 1][$x];
        }
        if ($y - 1 >= 0 && $x + 1 < $this->size) {
            $neighbours[] = $this->cells[$y - 1][$x + 1];
        }

        if ($x - 1 >= 0) {
            $neighbours[] = $this->cells[$y][$x - 1];
        }
        if ($x + 1 < $this->size) {
            $neighbours[] = $this->cells[$y][$x + 1];
        }

        if ($y + 1 < $this->size && $x - 1 >= 0) {
            $neighbours[] = $this->cells[$y + 1][$x - 1];
        }
        if ($y + 1 < $this->size) {
            $neighbours[] = $this->cells[$y + 1][$x];
        }
        if ($y + 1 < $this->size && $x + 1 < $this->size) {
            $neighbours[] = $this->cells[$y + 1][$x + 1];
        }

        $sameSpeciesCount = 0;
        foreach ($neighbours as $neighbour) {
            if ($neighbour === $cell) {
                $sameSpeciesCount++;
            }
        }

        if ($cell !== null && $sameSpeciesCount >= 2 && $sameSpeciesCount <= 3) {
            return $cell;
        }

        $speciesForBirth = [];
        for ($i = 0; $i < $this->species; $i++) {
            $oneSpeciesCount = 0;

            foreach ($neighbours as $neighbour) {
                if ($neighbour === $i) {
                    $oneSpeciesCount++;
                }
            }

            if ($oneSpeciesCount === 3) {
                $speciesForBirth[] = $i;
            }
        }

        if (count($speciesForBirth) > 0) {
            return $speciesForBirth[array_rand($speciesForBirth)];
        }

        return null;
    }
}
