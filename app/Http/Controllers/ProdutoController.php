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
        // 1. Validação (Opcional, mas altamente recomendado)
        // Garante que os parâmetros de entrada são válidos.
        $validator = Validator::make($request->all(), [
            'ean' => 'nullable|string',
            'nome' => 'nullable|string|max:100',
            'categoria_id' => 'nullable|integer|exists:categorias,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $validated = $validator->validated();

        // 2. Inicia a construção da query do Eloquent
        $query = Produto::query()->with('categoria');

        // 3. Aplica os filtros condicionalmente

        // Se o parâmetro 'ean' for fornecido, busca por ele
        if (isset($validated['ean'])) {
            $query->where('codigo_barras', $validated['ean']);
        }

        // Se o parâmetro 'nome' for fornecido, faz uma busca com 'LIKE'
        if (isset($validated['nome'])) {
            $query->where('nome', 'like', '%' . $validated['nome'] . '%');
        }

        // Se o parâmetro 'categoria_id' for fornecido, filtra pela categoria
        if (isset($validated['categoria_id'])) {
            $query->where('categoria_id', $validated['categoria_id']);
        }

        // 4. Ordena e executa a query com paginação
        $produtos = $query->orderBy('nome', 'asc')->paginate(15);

        // 5. Retorna a coleção de recursos
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
