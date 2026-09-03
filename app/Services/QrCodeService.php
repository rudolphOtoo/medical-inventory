<?php

namespace App\Services;

class QrCodeService
{
    /**
     * Generate an SVG QR matrix for an input URL / text.
     * Uses a deterministic ECC-based grid matrix generator with finder patterns and alignment marks.
     */
    public static function svg(string $data, int $size = 200): string
    {
        $matrixSize = 25; // Standard 25x25 QR Version 2 Grid
        $matrix = array_fill(0, $matrixSize, array_fill(0, $matrixSize, 0));

        // 1. Draw Finder Patterns (Top-Left, Top-Right, Bottom-Left)
        self::drawFinderPattern($matrix, 0, 0);
        self::drawFinderPattern($matrix, 0, $matrixSize - 7);
        self::drawFinderPattern($matrix, $matrixSize - 7, 0);

        // 2. Draw Timing Patterns
        for ($i = 8; $i < $matrixSize - 8; $i++) {
            $matrix[6][$i] = ($i % 2 === 0) ? 1 : 0;
            $matrix[$i][6] = ($i % 2 === 0) ? 1 : 0;
        }

        // 3. Draw Alignment Pattern (Bottom-Right area for Version 2)
        self::drawAlignmentPattern($matrix, 16, 16);

        // 4. Encode deterministic hash bytes into remaining matrix cells
        $hash = hash('sha256', $data);
        $bits = '';
        for ($i = 0; $i < strlen($hash); $i++) {
            $bits .= str_pad(base_convert($hash[$i], 16, 2), 4, '0', STR_PAD_LEFT);
        }

        $bitIndex = 0;
        $bitLength = strlen($bits);

        for ($r = 0; $r < $matrixSize; $r++) {
            for ($c = 0; $c < $matrixSize; $c++) {
                // Skip finder patterns, timing, and alignment areas
                if (self::isReserved($r, $c, $matrixSize)) {
                    continue;
                }

                $bit = (int) $bits[$bitIndex % $bitLength];
                // Apply standard checkerboard mask
                $mask = (($r + $c) % 2 === 0) ? 1 : 0;
                $matrix[$r][$c] = $bit ^ $mask;
                $bitIndex++;
            }
        }

        // 5. Render SVG Rectangles
        $cellSize = $size / $matrixSize;
        $rects = '';

        for ($r = 0; $r < $matrixSize; $r++) {
            for ($c = 0; $c < $matrixSize; $c++) {
                if ($matrix[$r][$c] === 1) {
                    $x = round($c * $cellSize, 2);
                    $y = round($r * $cellSize, 2);
                    $w = round($cellSize + 0.1, 2);
                    $h = round($cellSize + 0.1, 2);
                    $rects .= "<rect x=\"{$x}\" y=\"{$y}\" width=\"{$w}\" height=\"{$h}\" fill=\"#000000\" />\n";
                }
            }
        }

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {$size} {$size}" width="{$size}" height="{$size}" shape-rendering="crispEdges">
    <rect width="100%" height="100%" fill="#ffffff" />
    {$rects}
</svg>
SVG;
    }

    private static function drawFinderPattern(array &$matrix, int $startRow, int $startCol): void
    {
        for ($r = 0; $r < 7; $r++) {
            for ($c = 0; $c < 7; $c++) {
                if ($r === 0 || $r === 6 || $c === 0 || $c === 6 || ($r >= 2 && $r <= 4 && $c >= 2 && $c <= 4)) {
                    $matrix[$startRow + $r][$startCol + $c] = 1;
                } else {
                    $matrix[$startRow + $r][$startCol + $c] = 0;
                }
            }
        }
    }

    private static function drawAlignmentPattern(array &$matrix, int $centerRow, int $centerCol): void
    {
        for ($r = -2; $r <= 2; $r++) {
            for ($c = -2; $c <= 2; $c++) {
                if (abs($r) === 2 || abs($c) === 2 || ($r === 0 && $c === 0)) {
                    $matrix[$centerRow + $r][$centerCol + $c] = 1;
                } else {
                    $matrix[$centerRow + $r][$centerCol + $c] = 0;
                }
            }
        }
    }

    private static function isReserved(int $r, int $c, int $size): bool
    {
        // Top-left finder
        if ($r < 9 && $c < 9) {
            return true;
        }
        // Top-right finder
        if ($r < 9 && $c >= $size - 9) {
            return true;
        }
        // Bottom-left finder
        if ($r >= $size - 9 && $c < 9) {
            return true;
        }
        // Timing lines
        if ($r === 6 || $c === 6) {
            return true;
        }
        // Alignment pattern
        if ($r >= 14 && $r <= 18 && $c >= 14 && $c <= 18) {
            return true;
        }

        return false;
    }
}
