<?php

namespace App\Http\Controllers;

// App/Http/Controllers/EconomiesController.php

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Renda; // Crie este Model antes

class EconomiesController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validação (Opcional, mas altamente recomendada!)
        $request->validate([
            'rendas.*.origem' => 'required|string|max:255',
            'rendas.*.valor' => 'required|numeric|min:0',
        ]);

        $rendas = $request->input('rendas');
        $sessionUser = session('user');
        
        // Supondo que o usuário esteja autenticado

        if(!$sessionUser || !isset($sessionUser['id'])){
            return redirect('/prohibited')->with('loginError','Sua sessão expirou. Faça login novamente');
        }


        $userId = $sessionUser['id'];
        // 2. Apagar rendas antigas para evitar duplicação (se for um "update" completo)
        // Se for um formulário de primeira vez, esta linha não é necessária.
        // Renda::where('user_id', $userId)->delete(); 

        // 3. Iterar e salvar cada renda
        foreach ($rendas as $index => $renda) {
            
            // Se o valor estiver vazio ou zero e não for a renda principal (index 0), podemos pular
            if (empty($renda['valor']) && $index > 0) {
                 continue; 
            }
            
            Renda::create([
                'user_id' => $userId,
                'origem' => $renda['origem'],
                'valor' => $renda['valor'],
                'is_principal' => ($index == 0), // O primeiro item (index 0) é a principal
            ]);
        }

        return redirect('/')->with('success', 'Rendas salvas com sucesso!');
    }
}
