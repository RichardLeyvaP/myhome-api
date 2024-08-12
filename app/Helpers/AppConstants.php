<?php

namespace App\Helpers;

class AppConstants
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Tamaño de página para el paginado en consultas a la Base de Datos
     */
    const PAGE_SIZE = 15;

    /**
     * Es el multiplicador que se aplica a la moneda en la que opera el sistema. Ya que Stripe obtiene el monto en la representación más básica de la moneda. Ejemplo 1 peso mexicano son 100 centavos, el monto se envía en centavos.     
     */
    const CURRENCY_MULTIPLIER = 100;
}
