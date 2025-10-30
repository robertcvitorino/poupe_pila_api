<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProdutoResource;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProdutoController extends Controller
{
    public function index(Request $request)
    {
        $params = $request->query();

        $query = Produto::query();



        $query->when(array_key_exists('nome',$params), function ($q) use ($params) {
            return $q->where('nome','like',"%{$params['nome']}%");
        });

        $query->when(array_key_exists('ean',$params), function ($q, $ean) {
            return $q->where('codigo_barras', $ean);
        });

        $produtos = $query->paginate(15);


        return ProdutoResource::collection($produtos);
    }


    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nome' => 'required|string|max:150',
            'categoria_id' => 'required|exists:categorias,id',
            'codigo_barras' => 'nullable|string|max:100|unique:produtos'
        ]);

        $produto = Produto::create($validatedData);
        return new ProdutoResource($produto);
    }

    public function show(Produto $produto)
    {
        return new ProdutoResource($produto->load('categoria'));
    }

    public function update(Request $request, Produto $produto)
    {
        $validatedData = $request->validate([
            'nome' => 'sometimes|required|string|max:150',
            'categoria_id' => 'sometimes|required|exists:categorias,id',
            'codigo_barras' => 'nullable|string|max:100|unique:produtos,codigo_barras,' . $produto->id,
        ]);

        $produto->update($validatedData);
        return new ProdutoResource($produto);
    }

    public function destroy(Produto $produto)
    {
        $produto->delete();
        return response()->noContent(); // Resposta 204
    }
}
