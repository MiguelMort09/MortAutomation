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

### 🛠️ Desarrollo

```bash
# Configurar proyecto
php artisan mort:dev setup

# Ejecutar tests
php artisan mort:dev test

# Ejecutar linting
php artisan mort:dev lint

# Formatear código
php artisan mort:dev format

# Construir proyecto
php artisan mort:dev build

# Monitorear sistema
php artisan mort:dev monitor

# Limpiar proyecto
php artisan mort:dev cleanup

# Crear backup
php artisan mort:dev backup
```

### 🔄 Workflow

```bash
# Iniciar nueva feature
php artisan mort:workflow start-feature --name="mi-feature"

# Completar feature
php artisan mort:workflow complete-feature

# Deploy a staging
php artisan mort:workflow deploy-staging

# Deploy a producción
php artisan mort:workflow deploy-production

# Ciclo completo
php artisan mort:workflow full-cycle

# Fix de emergencia
php artisan mort:workflow emergency-fix --name="hotfix-critico"
```

### 🤖 MCP (Multi-Capability Platform)

```bash
# Buscar documentación
php artisan mort:mcp search-docs --query="authentication"

# Obtener docs de librería
php artisan mort:mcp get-library-docs --package="laravel/framework"

# Operaciones de Stripe
php artisan mort:mcp stripe-operations

# Operaciones de GitHub
php artisan mort:mcp github-operations

# Operaciones de Laravel Boost
php artisan mort:mcp laravel-boost

# Verificar estado de MCPs
php artisan mort:mcp mcp-status
```

### 💳 Stripe

```bash
# Crear cliente
php artisan mort:stripe create-customer

# Crear producto
php artisan mort:stripe create-product

# Crear precio
php artisan mort:stripe create-price --product="prod_xxx" --amount="2999"

# Crear payment link
php artisan mort:stripe create-payment-link --price="price_xxx"

# Sincronizar datos
php artisan mort:stripe sync-data

# Generar reporte
php artisan mort:stripe generate-report

# Listar clientes
php artisan mort:stripe list-customers

# Listar productos
php artisan mort:stripe list-products

# Listar precios
php artisan mort:stripe list-prices
```

### 📊 Monitoreo

```bash
# Monitoreo básico
php artisan mort:monitor

# Monitoreo detallado
php artisan mort:monitor --detailed

# Exportar métricas
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

### Principios de Diseño

- **SOLID**: Principios de diseño orientado a objetos
- **Clean Code**: Código limpio y mantenible
- **Modularidad**: Componentes independientes y reutilizables
- **Configurabilidad**: Fácil configuración y personalización

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
- [ ] Dashboard web para monitoreo
- [ ] Integración con CI/CD
- [ ] Soporte para múltiples bases de datos
- [ ] Métricas avanzadas de rendimiento
- [ ] Integración con servicios de logging
- [ ] Soporte para microservicios

---

**Desarrollado siguiendo la guía de Mort** 🚀
