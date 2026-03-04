# 🍽️ Proyecto ApiTiendynPedidos (RestaurApp)

Este proyecto es un sistema de gestión para restaurantes que funciona bajo un modelo de suscripción (SaaS).

## 🛠️ Stack Tecnológico
- **Backend:** Laravel 12 (API)
- **Frontend:** React Native (Próximamente)
- **Base de Datos:** MySQL (Remoto: 34.174.64.218)
- **Autenticación:** Laravel Sanctum (Tokens API)

## 🗄️ Esquema de Base de Datos (Tablas Creadas)
1. `users`: Dueños y clientes.
2. `restaurants`: Información del establecimiento.
3. `restaurant_categories`: **(NUEVO)** Categorías generales (Asados, Postres, etc.) con campo `icon_svg`.
4. `menu_categories`: Agrupación de platillos por restaurante.
5. `menu_items`: Platillos, precios e imágenes.
6. `follows`: Seguimiento de clientes a restaurantes.
7. `orders`: Gestión de pedidos.
8. `order_items`: Detalle de productos por pedido.
9. `ratings`: Calificaciones y comentarios.

## 🚀 Progreso Actual
- [x] Conexión a base de datos remota configurada.
- [x] Instalación de Laravel Sanctum.
- [x] Modelos Eloquent creados con todas sus relaciones.
- [x] `AuthController` (Registro, Login, Perfil).
- [x] `RestaurantController` (CRUD completo).
- [x] Controladores para Menús y Categorías.
- [x] **Estandarización de API:** Respuestas uniformes con `status`, `message` y `errors` (string).
- [x] **Localización:** Mensajes de validación y error en Español (`lang/es`).
- [x] **Categorías Generales:** Implementado sistema de categorías con soporte para iconos SVG.
- [ ] Lógica de Pedidos y Carrito (Pendiente).

## 📌 Notas de Implementación
- El backend está configurado en español (`APP_LOCALE=es`).
- Los errores de validación devuelven el primer mensaje como un string simple en la clave `errors`.
- El endpoint `GET /api/categories` es público.
---
*Última actualización: 4 de marzo, 2026*
