<?php

namespace Database\Seeders\Support;

/**
 * Flat-lay garment illustrations (SVG) for the demo catalogue, so the
 * store looks complete before real product photography is uploaded.
 * All artwork is 900 × 1200 (3:4, the product frame) unless noted.
 */
class GarmentArt
{
    private const BACKGROUNDS = ['#efe9e0', '#e9e4dc', '#ece5e0', '#e6e7e0', '#ede6db'];

    private const SHAPES = [
        'tee' => 'M390,250 Q450,300 510,250 L600,265 L735,360 L680,470 L600,420 L600,900 Q450,915 300,900 L300,420 L220,470 L165,360 L300,265 Z',
        'shirt' => 'M395,235 L450,300 L505,235 L605,255 Q660,280 680,360 L740,770 L665,785 L615,430 L615,930 Q450,945 285,930 L285,430 L235,785 L160,770 L220,360 Q240,280 295,255 Z',
        'dress' => 'M380,230 Q450,282 520,230 L590,245 L640,330 L590,360 L575,470 Q600,520 700,1000 Q450,1040 200,1000 Q300,520 325,470 L310,360 L260,330 L310,245 Z',
        'trousers' => 'M300,230 L600,230 L615,300 L660,1010 L520,1020 L455,420 L445,420 L380,1020 L240,1010 L285,300 Z',
        'kurta' => 'M400,215 L500,215 L505,240 L600,260 L700,360 L745,640 L675,650 L620,420 L640,1010 Q450,1025 260,1010 L280,420 L225,650 L155,640 L200,360 L300,260 L395,240 Z',
        'hoodie' => 'M330,250 Q450,175 570,250 L620,270 Q680,300 700,380 L750,760 L675,775 L625,450 L630,880 L600,920 L300,920 L270,880 L275,450 L225,775 L150,760 L200,380 Q220,300 280,270 Z',
        'skirt' => 'M330,260 L570,260 L585,300 L700,980 Q450,1010 200,980 L315,300 Z',
        'overshirt' => 'M385,230 L450,290 L515,230 L630,255 Q690,285 705,370 L755,780 L675,795 L630,450 L635,930 L265,930 L270,450 L225,795 L145,780 L195,370 Q210,285 270,255 Z',
        'tote' => 'M250,430 L650,430 L690,1000 L210,1000 Z',
    ];

    public static function product(string $shape, string $color, int $variant = 0, bool $closeUp = false): string
    {
        $bg = self::BACKGROUNDS[$variant % count(self::BACKGROUNDS)];
        $edge = self::shade($color, self::isLight($color) ? -0.32 : -0.4);
        $seam = self::shade($color, self::isLight($color) ? -0.22 : 0.18);
        $id = 'g' . substr(md5($shape . $color . $variant . (int) $closeUp), 0, 6);

        $body = '<path d="' . self::SHAPES[$shape] . '" fill="' . $color . '" stroke="' . $edge . '" stroke-width="3" stroke-linejoin="round"/>'
            . '<path d="' . self::SHAPES[$shape] . '" fill="url(#' . $id . 's)"/>'
            . self::details($shape, $seam, $edge);

        $group = $closeUp
            ? '<g transform="translate(450 560) scale(1.9) translate(-450 -420)">' . $body . '</g>'
            : '<ellipse cx="450" cy="1080" rx="300" ry="26" fill="#000" opacity=".06"/>' . $body;

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 900 1200" width="900" height="1200">'
            . '<defs><linearGradient id="' . $id . 's" x1="0" x2="1" y1="0" y2="0">'
            . '<stop offset="0" stop-color="#fff" stop-opacity=".16"/><stop offset=".45" stop-color="#fff" stop-opacity="0"/>'
            . '<stop offset="1" stop-color="#000" stop-opacity=".14"/></linearGradient>'
            . '<radialGradient id="' . $id . 'b" cx=".5" cy=".42" r=".75"><stop offset="0" stop-color="#fff" stop-opacity=".55"/><stop offset="1" stop-color="#fff" stop-opacity="0"/></radialGradient></defs>'
            . '<rect width="900" height="1200" fill="' . $bg . '"/><rect width="900" height="1200" fill="url(#' . $id . 'b)"/>'
            . $group . '</svg>';
    }

    /** Wide editorial artwork for hero banners and story blocks: draped folds in one palette. */
    public static function drape(array $palette, int $width = 1920, int $height = 1080, int $seed = 1): string
    {
        mt_srand($seed);
        [$light, $mid, $dark, $bg] = $palette;
        $folds = 14;
        $defs = '';
        $paths = '';

        for ($i = 0; $i < $folds; $i++) {
            $t0 = $i / $folds;
            $t1 = ($i + 1) / $folds;
            $xt0 = $width * (0.38 + 0.6 * $t0);
            $xt1 = $width * (0.38 + 0.6 * $t1);
            $xb0 = $width * (0.02 + 0.98 * $t0);
            $xb1 = $width * (0.02 + 0.98 * $t1);
            $sway = mt_rand(-60, 60);
            $hem0 = $height - 30 + mt_rand(-30, 30);
            $hem1 = $height - 30 + mt_rand(-30, 30);
            $edge = $i % 2 ? $dark : $mid;

            $defs .= sprintf('<linearGradient id="d%d" x1="0" x2="1"><stop offset="0" stop-color="%s"/><stop offset=".55" stop-color="%s"/><stop offset="1" stop-color="%s"/></linearGradient>', $i, $light, $light, $edge);
            $paths .= sprintf(
                '<path d="M%.1f,0 L%.1f,0 C%.1f,%.1f %.1f,%.1f %.1f,%.1f Q%.1f,%.1f %.1f,%.1f C%.1f,%.1f %.1f,%.1f %.1f,0 Z" fill="url(#d%d)"/>',
                $xt0, $xt1, $xt1 + $sway, $height * .35, $xb1 - $sway, $height * .6, $xb1, $hem1,
                ($xb0 + $xb1) / 2, max($hem0, $hem1) + 30, $xb0, $hem0,
                $xb0 - $sway, $height * .6, $xt0 + $sway, $height * .35, $xt0, $i,
            );
        }

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $width . ' ' . $height . '" width="' . $width . '" height="' . $height . '" preserveAspectRatio="xMidYMid slice">'
            . '<defs>' . $defs . '<linearGradient id="v" x1="0" x2="1"><stop offset="0" stop-color="' . $bg . '"/><stop offset=".5" stop-color="' . $bg . '" stop-opacity=".2"/><stop offset="1" stop-color="' . $bg . '" stop-opacity="0"/></linearGradient></defs>'
            . '<rect width="100%" height="100%" fill="' . $bg . '"/>' . $paths
            . '<rect width="100%" height="100%" fill="url(#v)"/></svg>';
    }

    private static function details(string $shape, string $seam, string $edge): string
    {
        $line = fn (string $d, float $w = 2.5, float $o = .7) => '<path d="' . $d . '" fill="none" stroke="' . $seam . '" stroke-width="' . $w . '" stroke-opacity="' . $o . '" stroke-linecap="round"/>';
        $button = fn (int $x, int $y) => '<circle cx="' . $x . '" cy="' . $y . '" r="6" fill="' . $seam . '" fill-opacity=".85"/>';

        return match ($shape) {
            'tee' => $line('M390,250 Q450,312 510,250', 3) . $line('M300,420 L300,870', 1.5, .3) . $line('M310,880 Q450,893 590,880', 2, .5),
            'shirt' => '<path d="M395,235 L428,316 L450,300 Z M505,235 L472,316 L450,300 Z" fill="' . $seam . '" fill-opacity=".35" stroke="' . $edge . '" stroke-width="2"/>'
                . $line('M450,300 L450,930', 2) . implode('', array_map(fn ($y) => $button(450, $y), [370, 450, 530, 610, 690, 770, 850]))
                . $line('M530,420 L590,420 L590,490 L530,490 Z', 2, .5) . $line('M665,785 L740,770', 3, .6) . $line('M160,770 L235,785', 3, .6),
            'dress' => $line('M325,470 Q450,492 575,470', 3) . $line('M380,230 Q450,290 520,230', 3) . $line('M400,500 Q380,760 330,1000', 1.5, .35) . $line('M500,500 Q520,760 570,1000', 1.5, .35),
            'trousers' => $line('M300,272 L600,272', 3) . $line('M450,272 L450,400', 2) . $line('M330,330 L310,1005', 1.5, .3) . $line('M570,330 L590,1005', 1.5, .3) . $line('M320,272 Q350,330 395,330', 2, .5) . $line('M580,272 Q550,330 505,330', 2, .5),
            'kurta' => $line('M400,240 L500,240', 3) . $line('M450,240 L450,540', 2) . implode('', array_map(fn ($y) => $button(450, $y), [290, 360, 430, 500]))
                . $line('M630,1010 L622,860', 2.5) . $line('M270,1010 L278,860', 2.5) . $line('M330,560 Q450,600 570,560', 1.5, .35),
            'hoodie' => '<path d="M380,250 Q450,335 520,250 Q450,295 380,250 Z" fill="' . $edge . '" fill-opacity=".35"/>'
                . $line('M360,700 L540,700 L580,830 L320,830 Z', 2.5) . $line('M425,300 L418,420', 2) . $line('M475,300 L482,420', 2) . $line('M300,880 L600,880', 2.5) . $line('M675,775 L750,760', 3) . $line('M150,760 L225,775', 3),
            'skirt' => $line('M318,300 L582,300', 3) . implode('', array_map(fn ($i) => $line('M' . (340 + $i * 37) . ',305 L' . (235 + $i * 72) . ',985', 1.6, .4), range(0, 6))),
            'overshirt' => '<path d="M385,230 L420,330 L450,290 Z M515,230 L480,330 L450,290 Z" fill="' . $seam . '" fill-opacity=".35" stroke="' . $edge . '" stroke-width="2"/>'
                . $line('M450,290 L450,930', 2) . implode('', array_map(fn ($y) => $button(450, $y), [380, 480, 580, 680, 780, 880]))
                . $line('M320,430 L410,430 L410,530 L320,530 Z', 2.5) . $line('M490,430 L580,430 L580,530 L490,530 Z', 2.5) . $line('M320,455 L410,455', 2, .5) . $line('M490,455 L580,455', 2, .5),
            'tote' => $line('M330,430 C330,250 570,250 570,430', 14, .95) . $line('M250,500 L650,500', 2, .5) . $line('M360,620 L540,620 L540,760 L360,760 Z', 2, .4),
            default => '',
        };
    }

    private static function isLight(string $hex): bool
    {
        [$r, $g, $b] = self::rgb($hex);

        return (0.299 * $r + 0.587 * $g + 0.114 * $b) > 170;
    }

    /** Lighten (amount > 0) or darken (amount < 0) a hex colour. */
    private static function shade(string $hex, float $amount): string
    {
        $channels = array_map(function ($c) use ($amount) {
            $c = $amount < 0 ? $c * (1 + $amount) : $c + (255 - $c) * $amount;

            return max(0, min(255, (int) round($c)));
        }, self::rgb($hex));

        return vsprintf('#%02x%02x%02x', $channels);
    }

    private static function rgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    }
}
