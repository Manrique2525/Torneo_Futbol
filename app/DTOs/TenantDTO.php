<?php

namespace App\DTOs;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TenantDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly string $plan,
    ) {}

    /**
     * Crear DTO desde Request
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->input('name'),
            slug: self::generateSlug($request->input('slug') ?? $request->input('name')),
            plan: $request->input('plan', 'basic'),
        );
    }

    /**
     * Crear DTO manualmente (útil para tests o servicios)
     */
    public static function make(string $name, ?string $slug = null, string $plan = 'basic'): self
    {
        return new self(
            name: $name,
            slug: self::generateSlug($slug ?? $name),
            plan: $plan,
        );
    }

    /**
     * Generar slug limpio
     */
    private static function generateSlug(string $value): string
    {
        return Str::slug($value);
    }

    /**
     * Convertir a array (opcional)
     */
    public function toArray(): array
    {
        return [
            'name'   => $this->name,
            'slug'   => $this->slug,
            'plan'   => $this->plan,
        ];
    }
}
