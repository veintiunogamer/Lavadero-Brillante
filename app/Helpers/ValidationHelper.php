<?php

namespace App\Helpers;

use NumberFormatter;

/**
 * Helper para validaciones y formatos personalizados.
 *
 * @author Jose Alzate <josealzate97@gmail.com>
 */
class ValidationHelper
{
    /**
     * Valida un número de teléfono español.
     * Formato esperado: +34 600 123 456 (o variantes sin espacios/prefijo).
     *
     * @param string $phone
     * @return bool
     */
    public static function validateSpanishPhone(string $phone): bool
    {
        $cleaned = preg_replace('/\D/', '', $phone);
        return strlen($cleaned) === 9 || (strlen($cleaned) === 11 && str_starts_with($cleaned, '34'));
    }

    /**
     * Formatea un monto a formato de euro español.
     * Ejemplo: 1234.56 -> 1.234,56 €
     *
     * @param float $amount
     * @return string
     */
    public static function formatEuro(float $amount): string
    {
        $formatter = new NumberFormatter('es_ES', NumberFormatter::CURRENCY);
        return $formatter->formatCurrency($amount, 'EUR');
    }

    /**
     * Valida un email con reglas adicionales (ejemplo básico).
     * Extensible para dominios específicos si es necesario.
     *
     * @param string $email
     * @return bool
     */
    public static function validateEmailCustom(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}