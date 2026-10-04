<?php

return [
    'required' => 'El campo :attribute es obligatorio.',
    'string' => 'El campo :attribute debe ser texto.',
    'email' => 'Introduce un correo electrónico válido.',
    'integer' => 'El campo :attribute debe ser un número entero.',
    'numeric' => 'El campo :attribute debe ser numérico.',
    'unique' => 'Ya existe un registro con este :attribute.',
    'exists' => 'El :attribute seleccionado no existe.',
    'distinct' => 'No repitas el mismo producto en una venta.',
    'array' => 'El campo :attribute debe contener una lista.',
    'not_in' => 'El valor de :attribute no está permitido.',
    'confirmed' => 'Las contraseñas no coinciden.',
    'min' => ['numeric' => 'El campo :attribute debe ser al menos :min.', 'string' => 'El campo :attribute debe tener al menos :min caracteres.', 'array' => 'Añade al menos :min elemento.'],
    'max' => ['numeric' => 'El campo :attribute no puede superar :max.', 'string' => 'El campo :attribute no puede superar :max caracteres.', 'array' => 'El campo :attribute no puede tener más de :max elementos.'],
    'attributes' => ['name' => 'nombre', 'email' => 'correo', 'password' => 'contraseña', 'sku' => 'SKU', 'category_id' => 'categoría', 'product_id' => 'producto', 'price' => 'precio', 'stock' => 'stock', 'minimum_stock' => 'stock mínimo', 'customer' => 'cliente', 'quantity' => 'cantidad', 'reason' => 'motivo', 'items' => 'productos de la venta', 'items.*.product_id' => 'producto', 'items.*.quantity' => 'cantidad'],
];
