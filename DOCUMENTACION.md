# FoodTiendyn — Documentación del Sistema

**Stack:** Laravel 11 (API + Admin Web) + React Native Expo (App Móvil)
**Bundle:** `com.ezequielhn.foodtiendyn`
**API URL local:** `http://192.168.1.7:8000/api`
**Notificaciones:** Firebase Firestore (tiempo real)

---

## ROLES DE USUARIO

| Rol | Acceso | Descripción |
|-----|--------|-------------|
| `customer` | App móvil | Busca restaurantes, hace pedidos, deja reseñas |
| `owner` | App móvil + Panel Admin Web | Gestiona su restaurante, menú, pedidos y cocina |
| `kitchen` | App móvil (cocina) + Web `/kitchen` | Ve y prepara pedidos enviados por el dueño |
| `superadmin` | Panel Web `/superadmin` | Administra todo el sistema |

---

## APP MÓVIL

### Navegación

```
LoginScreen
├── Tab "Iniciar sesión"  → Home (owner/customer)
├── Tab "Registrarse"     → Home
└── Tab "Cocina"          → KitchenDisplay

Home (owner)     → Dashboard / Menu / RestaurantProfile / OwnerReviews
Home (customer)  → RestaurantDetail → Cart → CustomerOrders → OrderDetails
KitchenDisplay   → KitchenQueue / KitchenNotifications
```

---

### Pantallas

#### LoginScreen
- 3 tabs: **Iniciar sesión** (email + contraseña), **Registrarse**, **Cocina** (username + contraseña)
- Registro: selección de rol (Cliente / Restaurante), validación de contraseña (mín. 8 chars, letras y números)
- Botón para mostrar/ocultar contraseña en todos los formularios
- Tras login: guarda token y `user_data` en AsyncStorage, activa listener de notificaciones Firebase

---

#### Pantallas Cliente (role: customer)

**HomeScreen**
- Buscar y explorar restaurantes
- Carrusel de anuncios/destacados
- Filtros por categoría
- BottomMenu: Inicio | Pedidos | Notificaciones | Favoritos | Perfil

**RestaurantDetailScreen**
- Logo, nombre, categoría, horarios, métodos de pago, zonas de entrega
- Tabs de categorías de menú
- Agregar ítems al carrito (modal con selector de cantidad)
- Toggle de favorito
- Lista de reseñas con promedio de estrellas
- Carrito flotante al tener ítems

**CartScreen**
- Lista de ítems con controles de cantidad (+/-)
- Selector de modo de entrega (Recoger / Delivery, según configuración del restaurante)
- Si delivery: selector de zona con costo de envío
- Nota del pedido (max 200 caracteres)
- Resumen: subtotal + envío + total
- Confirma pedido → `POST /orders`

**CustomerOrdersScreen**
- Lista paginada de pedidos propios con estado (color-coded)
- Pull-to-refresh + "Cargar más"
- Modal para dejar reseña (estrellas + comentario) cuando el pedido está entregado

**OrderDetailsScreen**
- Detalle completo: número, ítems, precios, dirección, notas
- Acciones si cliente: Confirmar entrega (listo → entregado) / Cancelar (pendiente → cancelado)
- Acciones si dueño: Rechazar / Aceptar Pedido (→ preparando) / Marcar como Listo / Confirmar Entrega

**FavoritesScreen**
- Grid de restaurantes favoritos
- Tap para ver detalle, toggle para remover

**AddressesScreen**
- CRUD de direcciones (label, dirección, referencia)
- Marcar como dirección por defecto

**CustomerProfileScreen**
- Foto de perfil (image picker)
- Editar nombre y teléfono
- Cambiar contraseña
- Stats: # órdenes, # favoritos, # reseñas
- Logout

---

#### Pantallas Dueño (role: owner)

**HomeScreen (owner)**
- Resumen de estadísticas del restaurante
- Accesos rápidos: Pedidos activos, Gestionar menú, Estadísticas, Mi perfil
- BottomMenu: Inicio | Ordenes | Notificaciones | Menú | Perfil

**DashboardScreen**
- Lista de pedidos con filtros: Todos | Pendientes | Preparando | Listos | Entregados | Rechazados
- Badge con conteo en Pendientes y Preparando
- Alerta pulsante cuando hay pedidos nuevos
- Acciones por tarjeta según estado:
  - Pendiente: [Rechazar] [Aceptar] → cambia a `preparando`
  - Preparando: [Marcar listo] → cambia a `listo`
  - Listo: [Entregado] → cambia a `entregado`
- Tap en tarjeta → OrderDetailsScreen
- Notificaciones Firebase `new_order` activan refresco automático

**MenuScreen**
- Ítems agrupados por categoría
- Por ítem: emoji, nombre, precio, stock, disponibilidad (toggle), imagen, complementos
- Crear/editar/eliminar ítems (con image picker + crop)
- Crear categorías
- Complementos predefinidos: Arroz, Frijoles, Ensalada, Tajadas, etc.

**RestaurantProfileScreen**
- Logo del restaurante (image picker + crop)
- Nombre, categoría, descripción, teléfono
- Horarios por día (on/off + hora apertura/cierre)
- Tipo de servicio: Local / Delivery / Ambos
- Zonas de entrega con precio
- Métodos de pago aceptados

**OwnerReviewsScreen**
- Promedio de estrellas con distribución (5⭐ X, 4⭐ Y, etc.)
- Lista de reseñas con foto de usuario, puntaje y comentario

**NotificationsScreen**
- Notificaciones Firebase en tiempo real
- Tipos: nuevo pedido (🔴), actualización de estado (🔵), nueva reseña (⭐), pedido cancelado (❌), general
- Marcar como leída individual o todas
- Eliminar notificación

---

#### Pantallas Cocina (role: kitchen)

BottomMenu de cocina: **Inicio** | **Notificaciones** | **En Cola**

**KitchenDisplayScreen (Inicio)**
- Muestra **solo el primer pedido** en estado `preparando` (el más antiguo)
- Muestra: número de pedido, nombre del cliente, modo de entrega, mesa (si aplica), ítems con cantidad, notas
- Botón "✅ Marcar como listo" → notifica al dueño y al cliente vía Firebase, siguiente pedido aparece automáticamente
- Pill "+N en cola" si hay más pedidos esperando
- Se actualiza automáticamente cuando llega notificación Firebase `new_order_kitchen`

**KitchenQueueScreen (En Cola)**
- Lista los pedidos restantes en `preparando` (desde el #2 en adelante)
- Muestra posición en cola (#2, #3...), número, cliente, modo, ítems
- Se actualiza con notificaciones Firebase

**KitchenNotificationsScreen (Notificaciones)**
- Notificaciones del usuario cocina (Firebase)
- Marcar como leída / todas

---

### Contextos Globales

**CartContext**
- Estado global del carrito
- Funciones: `addItem`, `removeItem`, `updateQty`, `clearCart`
- Valida que no se mezclen ítems de distintos restaurantes (pregunta al usuario)
- Computed: `totalItems`, `totalPrice`

**NotificationContext**
- Listener Firestore en tiempo real para el usuario autenticado
- Funciones: `setCurrentUserId`, `markAsRead`, `markAllAsRead`, `removeNotification`, `clearNotifications`
- `unreadCount` calculado automáticamente
- Se desuscribe automáticamente al cambiar de usuario

---

### Servicios API (`api.ts`)

| Servicio | Métodos |
|----------|---------|
| `menuService` | `getItems`, `getCategories`, `createItem`, `updateItem`, `deleteItem`, `createCategory` |
| `restaurantService` | `getMyRestaurant`, `getStats`, `updateProfile` |
| `userService` | `updateProfile`, `updateInfo`, `getStats`, `changePassword` |
| `addressService` | `getAll`, `create`, `update`, `delete`, `setDefault` |
| `ratingService` | `getByRestaurant`, `create` |
| `orderService` | `getOrders`, `updateStatus`, `getOrderDetails` |

---

### Componentes

| Componente | Descripción |
|------------|-------------|
| `BottomMenu` | Barra de navegación inferior, 3 variantes: owner (5 tabs), customer (5 tabs), kitchen (3 tabs) |
| `NotificationBell` | Icono con badge de no leídas, navega a Notifications |
| `CropModal` | Selector y recortador de imágenes |

---

## API LARAVEL

### Endpoints Públicos

| Método | Ruta | Descripción |
|--------|------|-------------|
| POST | `/api/register` | Registro (name, email, password, role: owner\|customer) |
| POST | `/api/login` | Login por email + contraseña |
| POST | `/api/kitchen/login` | Login cocina por username + contraseña |
| GET | `/api/categories` | Categorías de restaurantes (solo con restaurantes activos) |
| GET | `/api/announcements` | Anuncios activos ordenados por prioridad |

### Endpoints Protegidos (auth:sanctum)

**Perfil**

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/api/profile` | Datos del usuario autenticado |
| GET | `/api/profile/stats` | Conteo de órdenes, favoritos y reseñas |
| PUT/POST | `/api/profile` | Actualizar nombre, teléfono, foto |
| POST | `/api/profile/change-password` | Cambiar contraseña |
| POST | `/api/logout` | Cerrar sesión (elimina token) |

**Restaurantes**

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/api/restaurants` | Listar restaurantes abiertos (filtro por categoría) |
| GET | `/api/restaurants/{id}` | Detalle con rating promedio |
| POST | `/api/restaurant/my-data` | Datos completos del restaurante del owner |
| GET | `/api/restaurant/stats` | Ingresos hoy, órdenes hoy, rating promedio |
| POST/PUT | `/api/restaurant/profile` | Actualizar perfil, horarios, zonas, métodos de pago |

**Menú**

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/api/restaurants/{id}/categories` | Categorías del restaurante |
| POST/PUT/DELETE | `/api/menu-categories` | CRUD de categorías |
| GET | `/api/restaurants/{id}/items` | Ítems del menú |
| POST/PUT/DELETE | `/api/menu-items` | CRUD de ítems (con imagen Firebase) |

**Pedidos**

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/api/orders` | Listar (clientes: los suyos; owners: los de su restaurante) |
| POST | `/api/orders` | Crear pedido (solo customers) |
| GET | `/api/orders/{id}` | Detalle del pedido |
| PUT | `/api/orders/{id}/status` | Cambiar estado (con notificaciones Firebase) |

**Cocina (kitchen)**

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/api/kitchen/orders` | Pedidos en `preparando` del restaurante |
| POST | `/api/kitchen/orders/{id}/listo` | Marcar como listo (notifica owner y cliente) |

**Otros**

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET/POST | `/api/favorites` | Listar favoritos / toggle |
| GET | `/api/favorites/{id}/check` | Verificar si es favorito |
| GET/POST/PUT/DELETE | `/api/addresses` | CRUD de direcciones |
| POST | `/api/addresses/{id}/default` | Marcar dirección por defecto |
| GET/POST | `/api/restaurants/{id}/ratings` | Reseñas (crear solo si pedido entregado) |

---

### Lógica de Negocio Clave

**Número de pedido:** Secuencial (`PED-100`, `PED-101`...), basado en el último registrado.

**Estados de pedido:**

```
pendiente → preparando → listo → entregado
     ↓            ↓
  rechazado    (cancelado por cliente)
```

**Notificaciones Firebase enviadas automáticamente:**

| Evento | Tipo | Destinatario |
|--------|------|--------------|
| Cliente crea pedido | `new_order` | Owner del restaurante |
| Pedido pasa a `preparando` | `new_order_kitchen` | Usuarios kitchen del restaurante |
| Cambio de estado | `status_update` | Cliente |
| Cocina marca `listo` | `status_update` | Owner + Cliente |
| Cliente cancela | `order_cancelled` | Owner |
| Nueva reseña | `new_review` | Owner |

---

## PANEL ADMIN WEB (`/admin`)

Acceso para dueños de restaurante (`role = owner`).

| Sección | URL | Descripción |
|---------|-----|-------------|
| Dashboard | `/admin/dashboard` | Estadísticas del día, gráficos semanales, ítems populares |
| Pedidos | `/admin/orders` | Lista con filtros (default: Pendientes), cambio de estado en línea |
| Mesas | `/admin/mesas` | Gestión de mesas del local |
| Crear orden | `/admin/orders/create` | Crear pedido desde el local (con selector de mesa e ítems) |
| Menú | `/admin/menu` | CRUD de categorías e ítems del menú |
| Clientes | `/admin/customers` | Clientes que han ordenado (nombre, total pedidos, gasto) |
| Reseñas | `/admin/ratings` | Reseñas recibidas con distribución de estrellas |
| Cocina | `/admin/kitchen-users` | Crear/eliminar usuarios de cocina (username + password) |

---

## PANEL COCINA WEB (`/kitchen`)

Acceso para usuarios `role = kitchen`. Login con username (no email).

- `/kitchen/display` — Pantalla de pedidos en `preparando` con auto-refresh
- Botón "Listo" por pedido → cambia status y notifica a owner + cliente

---

## PANEL SUPERADMIN (`/superadmin`)

| Sección | Descripción |
|---------|-------------|
| Dashboard | Métricas globales (restaurantes, usuarios, órdenes, ingresos) |
| Restaurantes | Listar, buscar, activar/desactivar |
| Usuarios | Listar owners y clientes |
| Categorías | CRUD de categorías de tipo de restaurante |
| Métodos de pago | CRUD de métodos disponibles en el sistema |
| Anuncios | CRUD de banners que aparecen en el app |

---

## SISTEMA DE NOTIFICACIONES (Firebase Firestore)

Colección: `notifications`

```json
{
  "user_id": "123",
  "type": "new_order | status_update | new_order_kitchen | order_cancelled | new_review | general",
  "title": "Título",
  "message": "Mensaje descriptivo",
  "read": false,
  "created_at": 1710000000000,
  "data": { "order_id": 1, "order_number": "PED-100", ... }
}
```

- Listener en tiempo real en la app (Firestore `onSnapshot`)
- En cocina: cuando llega `new_order_kitchen` → refetch automático de pedidos
- `clearNotifications()` marca como leídas en Firestore (no elimina)

---

## ALMACENAMIENTO DE IMÁGENES

Firebase Storage, gestionado por `FirebaseStorageService`:

| Carpeta | Contenido |
|---------|-----------|
| `profiles/` | Fotos de perfil de usuarios |
| `restaurants/` | Logos de restaurantes |
| `products/` | Imágenes de ítems del menú |
