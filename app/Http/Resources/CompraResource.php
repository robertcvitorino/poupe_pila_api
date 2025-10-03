<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompraResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'descricao' => $this->descricao,
            'tipo' => $this->tipo,
            'status' => $this->status,
            'total' => $this->total,
            'local' => $this->local,
            'data_compra' => $this->data_compra,
            'criado_em' => $this->created_at,
            'itens' => ItemCompraResource::collection($this->whenLoaded('itens')),
        ];
    }
}
