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
3. `menu_categories`: Agrupación de platillos.
4. `menu_items`: Platillos, precios e imágenes.
5. `follows`: Seguimiento de clientes a restaurantes.
6. `orders`: Gestión de pedidos.
7. `order_items`: Detalle de productos por pedido.
8. `ratings`: Calificaciones y comentarios.

## 🚀 Progreso Actual
- [x] Conexión a base de datos remota configurada.
- [x] Instalación de Laravel Sanctum.
- [x] Modelos Eloquent creados con todas sus relaciones.
- [x] `AuthController` (Registro, Login, Perfil).
- [x] `RestaurantController` (CRUD completo).
- [x] Controladores para Menús y Categorías (Completado).
- [ ] Lógica de Pedidos y Carrito (Pendiente).

## 📌 Notas de Implementación
- El modelo de negocio es por suscripción (SaaS).
- El backend debe ser capaz de manejar múltiples restaurantes.
- Se utiliza Firebase Storage para las imágenes (por implementar en el frontend).
