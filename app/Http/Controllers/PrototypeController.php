<?php

namespace App\Http\Controllers;

use App\Prototype\Dummy;
use Illuminate\Contracts\View\View;

class PrototypeController extends Controller
{
    public function home(): View
    {
        return $this->tampil('proto.home');
    }

    private function tampil(string $view, array $data = []): View
    {
        return view($view, $data + [
            'site' => Dummy::site(),
            'kategori' => Dummy::kategori(),
        ]);
    }
}
