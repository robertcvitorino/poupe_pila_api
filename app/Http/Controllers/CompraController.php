<?php

namespace App\Http\Controllers;

use App\Http\Resources\CompraResource;
use App\Models\Compra;
use App\Models\Produto;
use App\StatusEnum;
use App\TipoEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CompraController extends Controller
{

    public function lista()
    {
        $compras = Compra::where('usuario_id', Auth::id())
            ->where('tipo',  TipoEnum::Lista)
            ->orderBy('created_at', 'desc')
            ->get();

        return CompraResource::collection($compras);
    }
    public function carrinho()
    {
        $compras = Compra::where('usuario_id', Auth::id())
            ->where('tipo',  TipoEnum::Carrinho)
            ->orderBy('created_at', 'desc')
            ->get();

        return CompraResource::collection($compras);
    }

    public function historico()
    {
        $compras = Compra::where('usuario_id', Auth::id())
            ->where('tipo',  TipoEnum::Carrinho)
            ->where('status', StatusEnum::Finalizado)
            ->whereNotNull('total')
            ->orderBy('created_at', 'desc')
            ->get();

        return CompraResource::collection($compras);
    }

    // Criar uma nova lista ou carrinho
    public function store(Request $request)
    {
        // 1. VALIDAÇÃO CORRIGIDA
        $validatedData = $request->validate([
            'nome' => 'required|string|max:150',
            'descricao' => 'nullable|string',
            'tipo' => 'required|in:lista,carrinho',
            'local' => 'nullable|string|max:200',
            'lista_origem_id' => 'nullable|exists:compras,id',
            'itens' => 'required|array',
            'itens.*.produto_id' => 'required|exists:produtos,id',
            'itens.*.quantidade' => 'required|integer|min:1',
            'itens.*.preco_unitario' => 'required_if:tipo,carrinho|nullable|numeric|min:0',
        ]);

        // Usar uma variável para checar o tipo facilita a leitura
        $isCarrinho = $validatedData['tipo'] === 'carrinho';

        $compra = DB::transaction(function () use ($validatedData, $isCarrinho) {
            $compra = Compra::create([
                'usuario_id' => Auth::id(),
                'nome' => $validatedData['nome'],
                'descricao' => $validatedData['descricao'] ?? null,
                'tipo' => $validatedData['tipo'],
                'local' => $validatedData['local'] ?? null,
                'lista_origem_id' => $validatedData['lista_origem_id'] ?? null,
                // Definimos o status com base na variável. Um carrinho começa como 'aberto'.
                'status' => $isCarrinho ? 'aberto' : null,
            ]);

            $totalCarrinho = 0;

            foreach ($validatedData['itens'] as $itemData) {
                // 2. LÓGICA DE CRIAÇÃO DO ITEM SIMPLIFICADA
                // A validação já garantiu que 'preco_unitario' existe se for um carrinho.
                // Usamos '?? null' para segurança caso o campo venha vazio para uma 'lista'.
                $compra->itens()->create([
                    'produto_id' => $itemData['produto_id'],
                    'quantidade' => $itemData['quantidade'],
                    'preco_unitario' => $itemData['preco_unitario'] ?? null,
                ]);

                if ($isCarrinho) {
                    // O cálculo do total só acontece para carrinhos
                    $totalCarrinho += $itemData['preco_unitario'] * $itemData['quantidade'];
                }
            }

            if ($isCarrinho) {
                $compra->total = $totalCarrinho;
                $compra->save();
            }
            return $compra;
        });

        return new CompraResource($compra->load('itens.produto'));
    }

    // Exibir uma compra específica
    public function show(Compra $compra)
    {
        // Garante que o usuário só pode ver suas próprias compras
        if (Auth::id() !== $compra->usuario_id) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }
        return new CompraResource($compra->load('itens.produto'));
    }

    // Atualizar uma compra (apenas nome/descrição por simplicidade)
    public function update(Request $request, Compra $compra)
    {
        if (Auth::id() !== $compra->usuario_id) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }
        $validatedData = $request->validate([
            'nome' => 'sometimes|required|string|max:150',
            'descricao' => 'nullable|string',
        ]);
        $compra->update($validatedData);
        return new CompraResource($compra);
    }

    // Excluir uma compra
    public function destroy(Compra $compra)
    {
        if (Auth::id() !== $compra->usuario_id) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }
        $compra->delete();
        return response()->noContent();
    }

    // Finalizar um carrinho
    public function finalizar(Request $request, Compra $compra)
    {
        if (Auth::id() != $compra->usuario_id || $compra->tipo != TipoEnum::Carrinho || $compra->status != StatusEnum::Aberto) {
            return response()->json(['error' => 'Ação não permitida.'], 403);
        }

        $validatedData = $request->validate(['local' => 'required|string|max:200']);

        $totalFinal = $compra->itens->sum(fn($item) => $item->preco_unitario * $item->quantidade);

        $compra->update([
            'status' => StatusEnum::Finalizado,
            'total' => $totalFinal,
            'local' => $validatedData['local'],
            'data_compra' => now()
        ]);
        return new CompraResource($compra->load('itens.produto'));
    }

    // Obter histórico de compras finalizadas
    public function historicoComProduto()
    {
        $historico = Compra::where('usuario_id', Auth::id())
            ->where('tipo', TipoEnum::Carrinho)
            ->where('status', StatusEnum::Finalizado)
            ->whereNotNull('total')
            ->with('itens.produto')
            ->orderBy('data_compra', 'desc')
            ->get();
        return CompraResource::collection($historico);
    }
}
