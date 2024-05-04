<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;

class SystemController extends Controller
{
    public function linkStorage() {
        try {
            Artisan::call('storage:link');
            return response()->json(['message' => 'Storage linked successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
        public function keyGenerate() {
        try {
            Artisan::call('key:generate');
            return response()->json(['message' => 'Codigo generado']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
