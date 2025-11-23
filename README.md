# Mort Automation Package

Package de automatización para desarrollo siguiendo la guía de Mort. Este package proporciona comandos Artisan para automatizar tareas comunes de desarrollo, workflow, monitoreo y integración con MCPs.

## 🚀 Instalación

### Instalación Local (Desarrollo)

```bash
# En el directorio packages/mort/automation
composer install
```

### Instalación en Proyecto

```bash
# Agregar al composer.json del proyecto
composer require mort/automation

# Publicar configuración
php artisan vendor:publish --provider="Mort\Automation\AutomationServiceProvider" --tag="config"
```

## 📋 Comandos Disponibles

> **Según la Guía de Mort**: Cada comando está diseñado para ser práctico, específico y seguir principios SOLID. Las descripciones indican exactamente qué hace cada comando para evitar confusión.

### 🛠️ Desarrollo

```bash
# Configurar proyecto - Instala dependencias, configura archivos y prepara el entorno
php artisan mort:dev setup

# Ejecutar tests - Ejecuta todos los tests con Pest y genera reportes de cobertura
php artisan mort:dev test

# Ejecutar linting - Analiza el código con PHPStan y Larastan para detectar errores
php artisan mort:dev lint

# Formatear código - Aplica Laravel Pint para formatear código según estándares PSR
php artisan mort:dev format

# Construir proyecto - Compila assets, optimiza código y prepara para producción
php artisan mort:dev build

# Monitorear sistema - Verifica estado de BD, performance y métricas del sistema
php artisan mort:dev monitor

# Limpiar proyecto - Elimina archivos temporales, cache y logs innecesarios
php artisan mort:dev cleanup

# Crear backup - Genera respaldo completo de BD y archivos importantes
php artisan mort:dev backup
```

### 🔄 Workflow

```bash
# Iniciar nueva feature - Crea rama, configura entorno y prepara para desarrollo
php artisan mort:workflow start-feature --name="mi-feature"

# Completar feature - Ejecuta tests, linting y mergea a rama principal
php artisan mort:workflow complete-feature

# Deploy a staging - Despliega cambios a entorno de pruebas y ejecuta validaciones
php artisan mort:workflow deploy-staging

# Deploy a producción - Despliega a producción con verificaciones de seguridad
php artisan mort:workflow deploy-production

# Ciclo completo - Ejecuta todo el flujo: feature → staging → producción
php artisan mort:workflow full-cycle

# Fix de emergencia - Crea hotfix rápido para problemas críticos en producción
php artisan mort:workflow emergency-fix --name="hotfix-critico"
```

### 🤖 MCP (Multi-Capability Platform)

```bash
# Buscar documentación - Busca en Context7 y GitHub docs sobre un tema específico
php artisan mort:mcp search-docs --query="authentication"

# Obtener docs de librería - Descarga documentación completa de una librería específica
php artisan mort:mcp get-library-docs --package="laravel/framework"

# Operaciones de Stripe - Gestiona productos, precios, clientes y pagos en Stripe
php artisan mort:mcp stripe-operations

# Operaciones de GitHub - Crea repos, issues, PRs y gestiona el repositorio
php artisan mort:mcp github-operations

# Operaciones de Laravel Boost - Integra con Laravel Boost para optimizaciones
php artisan mort:mcp laravel-boost

# Verificar estado de MCPs
php artisan mort:mcp mcp-status
```

### 💳 Stripe
> **Nota**: Ejecuta `php artisan mort:stripe` sin argumentos para ver el menú interactivo.

```bash
# Menú Interactivo - Muestra todas las opciones disponibles
php artisan mort:stripe

# Crear cliente - Registra un nuevo cliente en Stripe con datos básicos
php artisan mort:stripe create-customer

# Crear producto - Crea un nuevo producto en el catálogo de Stripe
php artisan mort:stripe create-product

# Crear precio - Define precio para un producto (en centavos, ej: 2999 = $29.99)
php artisan mort:stripe create-price --product="prod_xxx" --amount="2999"

# Crear payment link - Genera enlace de pago para un precio específico
php artisan mort:stripe create-payment-link --price="price_xxx"

# Listar clientes - Muestra todos los clientes registrados en Stripe
php artisan mort:stripe list-customers

# Listar productos - Muestra todos los productos del catálogo de Stripe
php artisan mort:stripe list-products

# Listar precios
php artisan mort:stripe list-prices
```

### 📊 Monitoreo

```bash
# Monitoreo básico - Verifica estado general del sistema y servicios
php artisan mort:monitor

# Monitoreo detallado - Análisis profundo de BD, performance y logs
php artisan mort:monitor --detailed

# Exportar métricas - Genera reporte en JSON/CSV con métricas del sistema
php artisan mort:monitor --export
```

## ⚙️ Configuración

### Variables de Entorno

```env
# Stripe
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...

# GitHub
GITHUB_TOKEN=ghp_...
GITHUB_DEFAULT_BRANCH=main

# Monitoreo
MONITORING_ENABLED=true
SLOW_QUERY_THRESHOLD=1000
MEMORY_LIMIT_WARNING=80

# Workflow
WORKFLOW_AUTO_COMMIT=false
FEATURE_BRANCH_PREFIX=feature
HOTFIX_BRANCH_PREFIX=hotfix

# Desarrollo
AUTO_INSTALL_DEPS=true
RUN_TESTS_AFTER_SETUP=true
FORMAT_CODE_AFTER_SETUP=true
```

### Archivo de Configuración

El package publica un archivo de configuración en `config/automation.php` que puedes personalizar según tus necesidades.

## 🏗️ Arquitectura

### Estructura del Package

```
src/
├── Commands/           # Comandos Artisan
├── Config/            # Configuración
├── Contracts/         # Interfaces
├── Services/          # Servicios
└── Traits/           # Traits reutilizables
```

### Principios de Diseño (Guía de Mort)

- **SOLID**: Principios de diseño orientado a objetos aplicados en toda la arquitectura
- **Clean Code**: Código limpio, mantenible y bien documentado
- **Modularidad**: Componentes independientes y reutilizables
- **Configurabilidad**: Fácil configuración y personalización
- **Practicidad**: Cada comando resuelve un problema real de desarrollo
- **Claridad**: Descripciones específicas de qué hace cada comando
- **Consistencia**: Naming en inglés para código, español para documentación

## 🧪 Testing

```bash
# Ejecutar tests
composer test

# Ejecutar tests con coverage
composer test-coverage
```

## 📚 Documentación

### Guías de Desarrollo

- [MORT-DEVELOPMENT-GUIDE.md](../MORT-DEVELOPMENT-GUIDE.md) - Guía completa de desarrollo
- [AI-ASSISTANT-RULES.md](../AI-ASSISTANT-RULES.md) - Reglas para asistentes de IA
- [PROJECT-SPECIFIC-RULES.md](../PROJECT-SPECIFIC-RULES.md) - Reglas específicas del proyecto

### Convenciones

- **Naming**: Inglés para código, español para documentación
- **Commits**: Conventional Commits en español
- **Testing**: Pest para tests
- **Linting**: Laravel Pint para PHP, ESLint para JavaScript

## 🤝 Contribución

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'feat: agregar AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo [LICENSE](LICENSE) para más detalles.

## 🆘 Soporte

Si tienes problemas o preguntas:

1. Revisa la documentación
2. Busca en los issues existentes
3. Crea un nuevo issue con detalles del problema

## 🎯 Roadmap

- [ ] Integración completa con MCPs
- [ ] Sincronización bidireccional de datos (sync-data)
- [ ] Generación de reportes avanzados (generate-report)
- [ ] Dashboard web para monitoreo
- [ ] Integración con CI/CD
- [ ] Soporte para múltiples bases de datos
- [ ] Métricas avanzadas de rendimiento
- [ ] Integración con servicios de logging
- [ ] Soporte para microservicios

---

**Desarrollado siguiendo la guía de Mort** 🚀
