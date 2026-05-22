<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        // 1. Leer el parámetro 'v' de la URL (ej: misitio.com/?v=v2)
        // Retornará null si el parámetro no viene en la URL
        $parametro = $this->request->getGet('v')?:'';
        $vista = 'landing';

        // 2. Definir la vista por defecto
        if($parametro){
            $vista = 'landing_' . $parametro;

        }

        // 4. Retornar la vista final
        return view($vista);
    }
}
