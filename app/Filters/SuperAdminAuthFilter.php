<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class SuperAdminAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Si no existe la variable 'superadmin_id' en la sesión, no es un super admin
        if (!session()->has('superadmin_id')) {
            // Guardamos un mensaje de error y redirigimos al login
            return redirect()->to('/super/login')->with('error', 'Acceso denegado. Inicie sesión para continuar.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No necesitamos hacer nada después de que el controlador se ejecute
    }
}