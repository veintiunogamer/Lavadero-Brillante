<?php

namespace App\Helpers;

/**
 * LicensePlateHelper - Presentación de matrículas con estilo de placa europea.
 *
 * Genera el marcado de una placa europea (estilo placa española) para poder
 * mostrarse dentro de los PDF (informe de ventas, informe de clientes).
 *
 * Solo es presentación: no modifica datos ni la validación del formulario.
 *
 * Nota técnica: DomPDF no dibuja el SVG inline en el HTML, por lo que la
 * banda de las 12 estrellas se emite como <img> con un data URI SVG. Así el
 * gráfico sigue siendo vectorial (nitidez al imprimir) en lugar de rasterizado.
 *
 * @author Jose Alzate <josealzate97@gmail.com>
 */
class LicensePlateHelper
{
    /**
     * Puntos de una estrella de 5 puntas (R=8.5, r=3.6) centrada en (0,0).
     * Debe coincidir con la constante STAR_POINTS de
     * resources/js/orders/utils/licensePlate.js.
     */
    public const STAR_POINTS = '0,-8.5 2.017,-2.777 8.083,-2.627 3.266,1.06 4.995,6.874 0,3.433 -4.995,6.874 -3.266,1.06 -8.083,-2.627 -2.017,-2.777';

    /**
     * Posiciones de las 12 estrellas en círculo (viewBox 0 0 100 100, radio 38).
     * Debe coincidir con la constante STAR_POSITIONS de
     * resources/js/orders/utils/licensePlate.js.
     */
    public const STAR_POSITIONS = [
        [50, 12], [69, 17.09], [82.91, 31], [88, 50],
        [82.91, 69], [69, 82.91], [50, 88], [31, 82.91],
        [17.09, 69], [12, 50], [17.09, 31], [31, 17.09],
    ];

    /**
     * Normaliza una matrícula: mayúsculas, sin guiones ni espacios sobrantes.
     */
    public static function normalize(string $plate): string
    {
        return strtoupper(preg_replace('/[\s\-_.]+/', '', trim($plate)));
    }

    /**
     * Descompone la matrícula en sus partes visibles.
     * Formato actual español: 1234 ABC. Formato histórico: ABC 1234.
     *
     * @return array{number: string, letters: string, legacy: bool, valid: bool}
     */
    public static function parse(string $plate): array
    {
        $normalized = self::normalize($plate);

        if (preg_match('/^(\d{4})([A-Z]{3})$/', $normalized, $m)) {
            return ['number' => $m[1], 'letters' => $m[2], 'legacy' => false, 'valid' => true];
        }

        if (preg_match('/^([A-Z]{3})(\d{4})$/', $normalized, $m)) {
            return ['number' => $m[2], 'letters' => $m[1], 'legacy' => true, 'valid' => true];
        }

        return ['number' => $normalized, 'letters' => '', 'legacy' => false, 'valid' => false];
    }

    /**
     * SVG con las 12 estrellas de la Unión Europea.
     */
    public static function starsSvg(): string
    {
        $polygons = '';

        foreach (self::STAR_POSITIONS as [$x, $y]) {
            $polygons .= '<polygon points="' . self::STAR_POINTS
                . '" transform="translate(' . $x . ' ' . $y . ')" fill="#ffcc00"/>';
        }

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"'
            . ' preserveAspectRatio="xMidYMid meet">' . $polygons . '</svg>';
    }

    /**
     * Data URI SVG de la banda azul con las 12 estrellas.
     * DomPDF solo dibuja el SVG cuando llega como imagen, no en línea.
     */
    public static function starsDataUri(): string
    {
        return 'data:image/svg+xml;base64,' . base64_encode(self::starsSvg());
    }

    /**
     * Escapa el texto para poder insertarlo dentro de HTML.
     */
    public static function escape(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Marcado HTML de la placa europea, listo para insertar en el PDF.
     *
     * Estructura (clases definidas en la hoja de estilos del PDF):
     *   <span class="eu-plate">
     *     <span class="eu-plate__band">  <-- banda azul con las 12 estrellas
     *     <span class="eu-plate__body">  <-- cuerpo blanco con la matrícula
     *   </span>
     *
     * Una matrícula vacía se muestra como "--", sin banda.
     *
     * @param string|null $plate  Matrícula a mostrar.
     * @param bool        $compact Variante más estrecha para tablas con poco espacio.
     */
    public static function render(?string $plate, bool $compact = false): string
    {
        $parsed = self::parse((string) $plate);

        if ($parsed['number'] === '') {
            return '<span class="eu-plate eu-plate--empty">--</span>';
        }

        $number = self::escape($parsed['number']);
        $letters = self::escape($parsed['letters']);

        $body = $parsed['valid']
            ? '<span class="eu-plate__number">' . $number . '</span>'
                . '<span class="eu-plate__letters">' . $letters . '</span>'
            : '<span class="eu-plate__raw">' . $number . '</span>';

        $classes = 'eu-plate' . ($compact ? ' eu-plate--sm' : '');

        return '<span class="' . $classes . '">'
            . '<span class="eu-plate__band"><img src="' . self::starsDataUri() . '" alt=""></span>'
            . '<span class="eu-plate__body">' . $body . '</span>'
            . '</span>';
    }
}
