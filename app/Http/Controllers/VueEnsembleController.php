<?php

namespace App\Http\Controllers;

use App\Services\VueEnsembleService;
use Illuminate\Http\Request;

class VueEnsembleController extends Controller
{
    public function __construct(private VueEnsembleService $vueEnsembleService) {}

    public function index(Request $request)
    {
        return view('vue-ensemble.index', $this->vueEnsembleService->getIndexData($request));
    }
}
