<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * SalesAuthFilter
 * Protege las rutas /sales/* exigiendo sesión de sales_user.
 * Registrar en app/Config/Filters.php
 */
class SalesAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!session()->has('sales_user_id') || !session('sales_logged_in')) {
            return redirect()->to('/sales/login')->with('error', 'Debes iniciar sesión.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
