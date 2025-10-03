<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemCompraResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quantidade' => $this->quantidade,
            'preco_unitario' => $this->preco_unitario,
            'produto' => new ProdutoResource($this->whenLoaded('produto')),
        ];
    }
}
