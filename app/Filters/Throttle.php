<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class Throttle implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (strcasecmp($request->getMethod(), 'post') !== 0) {
            return;
        }

        $throttler = \Config\Services::throttler();

        $ip = $request->getIPAddress();
        $key = 'login_attempt_' . str_replace([':', '.'], '_', $ip);

        $maxAttempts = 5;
        $perMinute = 60;

        if ($throttler->check($key, $maxAttempts, $perMinute) === false) {
            $remaining = $throttler->getTokenTime();

            session()->setFlashdata('error', "Too many login attempts. Please try again in {$remaining} seconds.");

            return redirect()->to('auth/login');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
