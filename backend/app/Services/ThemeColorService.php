<?php

declare(strict_types=1);

namespace App\Services;

final class ThemeColorService
{
    /**
     * Génère une palette de couleurs (50..900) à partir d'une couleur hex de base.
     *
     * @param  string  $hex  Couleur de base (ex: #635bff)
     * @return array<int,string>
     */
    public static function generateColorPalette(string $hex): array
    {
        $base = self::normalizeHex($hex) ?: '#635bff';
        [$r, $g, $b] = self::hexToRgb($base);

        // Génération des variantes (50 à 400 = éclaircies, 600 à 900 = assombries)
        $palette = [];
        foreach ([50 => 0.90, 100 => 0.80, 200 => 0.70, 300 => 0.60, 400 => 0.45] as $step => $lightFactor) {
            $palette[$step] = self::rgbToHex(...self::mixWithWhite($r, $g, $b, $lightFactor));
        }
        $palette[500] = $base;
        foreach ([600 => 0.15, 700 => 0.25, 800 => 0.35, 900 => 0.50] as $step => $darkFactor) {
            $palette[$step] = self::rgbToHex(...self::mixWithBlack($r, $g, $b, $darkFactor));
        }

        // Sécurité : s'assurer de toutes les clés
        $expected = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900];
        foreach ($expected as $k) {
            if (! isset($palette[$k])) {
                $palette[$k] = $base;
            }
        }
        ksort($palette);

        return $palette;
    }

    private static function normalizeHex(string $hex): ?string
    {
        $hex = mb_trim($hex);
        if (! preg_match('/^#?[0-9a-fA-F]{3,6}$/', $hex)) {
            return null;
        }
        if ($hex[0] !== '#') {
            $hex = '#'.$hex;
        }
        if (mb_strlen($hex) === 4) { // #abc -> #aabbcc
            $hex = '#'.$hex[1].$hex[1].$hex[2].$hex[2].$hex[3].$hex[3];
        }

        return mb_strtolower($hex);
    }

    private static function hexToRgb(string $hex): array
    {
        $hex = mb_ltrim($hex, '#');

        return [
            hexdec(mb_substr($hex, 0, 2)),
            hexdec(mb_substr($hex, 2, 2)),
            hexdec(mb_substr($hex, 4, 2)),
        ];
    }

    private static function rgbToHex(int $r, int $g, int $b): string
    {
        return sprintf('#%02x%02x%02x', max(0, min(255, $r)), max(0, min(255, $g)), max(0, min(255, $b)));
    }

    private static function mixWithWhite(int $r, int $g, int $b, float $factor): array
    {
        return [
            (int) round($r + (255 - $r) * $factor),
            (int) round($g + (255 - $g) * $factor),
            (int) round($b + (255 - $b) * $factor),
        ];
    }

    private static function mixWithBlack(int $r, int $g, int $b, float $factor): array
    {
        return [
            (int) round($r * (1 - $factor)),
            (int) round($g * (1 - $factor)),
            (int) round($b * (1 - $factor)),
        ];
    }
}
