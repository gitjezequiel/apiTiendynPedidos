<?php

return [
    'required' => 'El campo :attribute es obligatorio.',
    'email' => 'El campo :attribute debe ser una dirección de correo válida.',
    'unique' => 'El :attribute ya está en uso.',
    'min' => [
        'string' => 'El campo :attribute debe tener al menos :min caracteres.',
    ],
    'string' => 'El campo :attribute debe ser una cadena de texto.',
    'max' => [
        'string' => 'El campo :attribute no debe exceder los :max caracteres.',
    ],
    'in' => 'El :attribute seleccionado no es válido.',
    'exists' => 'El :attribute seleccionado no es válido.',
    'numeric' => 'El campo :attribute debe ser un número.',
    'boolean' => 'El campo :attribute debe ser verdadero o falso.',
    
    'attributes' => [
        'name' => 'nombre',
        'email' => 'correo electrónico',
        'password' => 'contraseña',
        'role' => 'rol',
        'phone' => 'teléfono',
        'address' => 'dirección',
        'city' => 'ciudad',
        'description' => 'descripción',
        'category' => 'categoría',
        'restaurant_id' => 'restaurante',
        'category_id' => 'categoría',
        'price' => 'precio',
        'image_url' => 'URL de imagen',
        'is_available' => 'disponibilidad',
    ],
];
