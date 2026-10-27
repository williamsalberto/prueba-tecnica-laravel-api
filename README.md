# Prueba Técnica Backend Laravel - API RESTful de Productos y Precios Multidivisa

API RESTful desarrollada en **Laravel** con soporte para gestión de productos, su divisa base y precios secundarios en múltiples divisas con tasas de cambio, siguiendo buenas prácticas de arquitectura limpia, validaciones formales mediante FormRequests, transformadores API Resources, relaciones Eloquent y medidas de seguridad (Rate Limiting, transacciones de base de datos y control de integridad referencial).

---

## 🚀 Requisitos y Tecnologías
* **PHP:** ^8.2 / ^8.3 / ^8.5
* **Laravel Framework:** 11.x / 12.x
* **Base de Datos:** SQLite (por defecto) o MySQL / PostgreSQL
* **Documentación:** Colección Postman JSON v2.1 (`postman_collection.json`)

---

## 🛠️ Instalación y Puesta en Marcha

1. **Entrar al directorio de la API:**
   ```bash
   cd api
   ```

2. **Instalar dependencias del proyecto:**
   ```bash
   composer install
   ```

3. **Configurar el archivo de entorno:**
   ```bash
   copy .env.example .env
   php artisan key:generate
   ```

4. **Ejecutar migraciones y sembrar datos iniciales (divisas y tasas de cambio):**
   ```bash
   php artisan migrate --seed
   ```
   *Nota: El seeder `CurrencySeeder` creará automáticamente las divisas base (`USD`, `EUR`, `GBP`, `COP`) requeridas para interactuar con los endpoints.*

5. **Levantar el servidor local:**
   ```bash
   php artisan serve
   ```
   La API quedará escuchando en `http://127.0.0.1:8000`.

---

## 🧪 Ejecución de Pruebas Automatizadas

La suite de pruebas automatizadas con PHPUnit cubre el 100% de los endpoints requeridos por el PDF, validando flujos de éxito (200, 201), errores de validación (422), recursos inexistentes (404), borrado en cascada y reglas de negocio multidivisa:

```bash
php vendor/phpunit/phpunit/phpunit tests/Feature/ProductApiTest.php
```

O ejecutar la suite completa de pruebas:
```bash
php vendor/phpunit/phpunit/phpunit
```

---

## 📌 Documentación de la API (Colección Postman)

Según lo estipulado en las opciones de entrega del documento PDF, la documentación se entrega en formato **JSON Postman** a través del archivo [`postman_collection.json`](./postman_collection.json).

### Cómo importar y probar la colección en Postman:
1. Abre **Postman**.
2. Haz clic en el botón superior **Import**.
3. Selecciona o arrastra el archivo `postman_collection.json` ubicado en la raíz del proyecto.
4. La colección cuenta con la variable de entorno `{{base_url}}` preconfigurada en `http://127.0.0.1:8000/api`.

---

## 📋 Endpoints Disponibles

| Método | Endpoint | Descripción | Códigos HTTP |
| :--- | :--- | :--- | :--- |
| **GET** | `/api/products` | Obtener lista paginada de productos con divisas y precios. | `200 OK` |
| **POST** | `/api/products` | Crear un nuevo producto en divisa base. | `201 Created`, `422 Unprocessable` |
| **GET** | `/api/products/{id}` | Obtener detalle de un producto por ID. | `200 OK`, `404 Not Found` |
| **PUT** | `/api/products/{id}` | Actualizar un producto existente. | `200 OK`, `404 Not Found`, `422 Unprocessable` |
| **DELETE** | `/api/products/{id}` | Eliminar un producto y sus precios en cascada. | `200 OK`, `404 Not Found` |
| **GET** | `/api/products/{id}/prices` | Obtener lista de precios multidivisa de un producto. | `200 OK`, `404 Not Found` |
| **POST** | `/api/products/{id}/prices` | Registrar un precio en divisa secundaria para un producto. | `201 Created`, `422 Unprocessable` |

---

## 🛡️ Medidas de Seguridad Implementadas
* **Rate Limiting:** Todas las rutas API cuentan con limitación de tasa `throttle:60,1` para prevenir ataques de denegación de servicio (DoS) y abusos de peticiones masivas.
* **Integridad Transaccional:** Uso de `DB::transaction()` en el servicio de productos para garantizar atomicidad y evitar estados inconsistentes ante fallos de persistencia.
* **Prevención de Ataques de Asignación Masiva:** Control estricto con propiedades `$fillable` en los modelos Eloquent y tipado con FormRequests.
* **Sanitización y Validación Compuesta:** Longitudes máximas en descripciones (`max:5000`) y validación de unicidad compuesta con `Rule::unique` para evitar precios duplicados por divisa.
* **Excepciones en Formato JSON:** Configuración de `shouldRenderJsonWhen` en el kernel para asegurar que las excepciones nunca expongan trazas internas en HTML a clientes no autorizados.
