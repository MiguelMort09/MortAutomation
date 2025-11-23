# Changelog

Todos los cambios notables de este proyecto serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/),
y este proyecto adhiere a [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.2.0] - 2025-11-22

### 🎉 Integración Real con Stripe SDK

Esta versión implementa la integración completa con el SDK de Stripe, permitiendo la sincronización automática de recursos creados desde la consola hacia la cuenta de Stripe.

### ✨ Agregado
- **StripeService**: Nuevo servicio centralizado para todas las operaciones de Stripe
  - Inicialización automática del cliente de Stripe
  - Métodos para crear clientes, productos, precios y payment links
  - Métodos para listar recursos existentes
  - Verificación de conexión con Stripe
  - Manejo robusto de errores de la API de Stripe

### 🚀 Mejorado
- **StripeMCPAutomationCommand**: Actualizado con integración real
  - ✅ Sincronización automática con Stripe al crear recursos
  - ✅ Instrucciones claras y específicas para cada acción
  - ✅ Validación de datos requeridos antes de crear recursos
  - ✅ Enlaces directos al Dashboard de Stripe para verificar recursos
  - ✅ Sugerencias contextuales de próximos pasos
  - ✅ Manejo específico de errores de Stripe API

### 📦 Comandos Mejorados
- `mort:stripe create-customer` - Ahora crea clientes reales en Stripe
- `mort:stripe create-product` - Crea productos reales en Stripe
- `mort:stripe create-price` - Crea precios (únicos o recurrentes) en Stripe
- `mort:stripe create-payment-link` - Genera enlaces de pago reales
- `mort:stripe list-customers` - Lista clientes reales de Stripe
- `mort:stripe list-products` - Lista productos reales de Stripe
- `mort:stripe list-prices` - Lista precios reales de Stripe

### 🔧 Configuración
- Service Provider actualizado para registrar StripeService como singleton
- Soporte completo para credenciales en `.env` (STRIPE_KEY, STRIPE_SECRET)

### ✅ Verificado
- Tests pasando: 4/4 exitosos
- Estilo de código verificado y corregido con Laravel Pint
- Integración funcional con Stripe API v16.0

### 📚 Documentación
- Walkthrough completo de la implementación
- Ejemplos de uso para cada comando
- Instrucciones de configuración actualizadas

# Release 1.4.0

## ✨ Features
- **Product Categories**: Added support for categorizing products using Stripe Metadata.
  - New `--category` option in `create-product` and `list-products`.
  - Configurable categories via `src/Config/stripe-categories.json`.
  - Uses Stripe Search API for filtering products by category.
  - Displays category in product details.

# Release 1.3.2

## ✨ Features
- **Default Currency**: Changed default currency from USD to MXN (Peso mexicano) for better localization.
- **Default Interval**: Changed default interval from 'month' to 'none' to indicate one-time payments by default.
- **Interval Handling**: Added support for `--interval=none` to explicitly create one-time payments via CLI.

# Release 1.3.1

## 🐛 Fixes
- **Interactive Prompts**: Fixed issue where product type and currency prompts were not showing in interactive mode when creating prices.

# Release 1.3.0

## ✨ Features
- **Improved Product/Price Creation**: Enhanced UX for creating products and prices with:
  - Clear distinction between one-time products and subscriptions
  - Currency selector with common options (USD, MXN, EUR, GBP, CAD) and custom input
  - Better interval selection for subscriptions (monthly, yearly, weekly, daily)
  - Formatted price display ($29.99 instead of 2999 cents)
  - Product type indicator in output

# Release 1.2.6

## 🐛 Fixes
- **Interactive Menu**: Fixed infinite loop in `mort:stripe` when selecting options from the menu. Now calls methods directly for better reliability.

## [1.2.5] - 2025-01-27

### 🐛 Fixes
- **Critical**: Fixed corrupted `composer.json` file that prevented installation.

## [1.2.4] - 2025-01-27

### 🎉 Release Estable Final
- **Versión estable y completamente funcional**
- **Package probado e instalado exitosamente en proyectos reales**
- **Todos los problemas resueltos y funcionalidades verificadas**

## [1.4.0] - 2025-01-27

### ✅ Verificado y Funcionando
- Package instalado correctamente en proyecto gym-sas
- 15 comandos disponibles y funcionando
- Service Provider registrado correctamente
- Autoloading funcionando sin errores
- Tests pasando (4/4) sin problemas
- GitHub Actions configurado y funcionando

### 🚀 Características Principales
- **Comandos de Desarrollo**: Automatización completa de tareas de desarrollo
- **Workflow Automatizado**: Gestión completa del ciclo de desarrollo
- **Integración MCP**: Operaciones con Model Context Protocol
- **Stripe Integration**: Automatización de operaciones de Stripe
- **Monitoreo del Sistema**: Métricas y monitoreo completo
- **Documentación Completa**: Guías y ayuda integrada

### 📦 Instalación
```bash
composer require mort/automation:^1.4
php artisan vendor:publish --provider="Mort\Automation\AutomationServiceProvider" --tag="config"
```

### 🎯 Uso Básico
```bash
php artisan mort:status      # Ver estado del sistema
php artisan mort:help        # Ver ayuda completa
php artisan mort:dev setup   # Configurar proyecto
php artisan mort:workflow start-feature --name="mi-feature"  # Iniciar feature
```

## [1.3.6] - 2025-01-27

### Corregido
- Error persistente de cache de Pest en GitHub Actions
- Scripts de Composer actualizados con parámetro --cache-directory
- Workflow de GitHub Actions mejorado con directorio de cache explícito

### Agregado
- Workflow de tests separado (.github/workflows/tests.yml)
- Creación automática del directorio .pest en CI/CD
- Verificaciones de estilo y análisis estático en CI

### Mejorado
- Estabilidad completa en GitHub Actions
- Tests funcionando correctamente en todos los entornos
- CI/CD robusto y confiable

## [1.3.5] - 2025-01-27

### Corregido
- Error de cache de Pest que causaba fallos en GitHub Actions
- Configuración de Pest actualizada con directorio de cache correcto
- Directorio .pest agregado al .gitignore

### Mejorado
- Configuración de Pest optimizada
- Tests funcionando correctamente sin errores de cache
- Estabilidad mejorada en entornos de CI/CD

## [1.3.4] - 2025-01-27

### Corregido
- Actualización de dependencias de testing a versiones compatibles
- Tests completamente funcionales con Pest y Orchestra Testbench
- Limpieza de archivos temporales y optimización del proyecto

### Mejorado
- Dependencias de testing actualizadas:
  - pestphp/pest-plugin-laravel: ^4.0
  - orchestra/testbench: ^10.6
- Suite de tests completa y funcional
- Autoloading optimizado

## [1.3.3] - 2025-01-27

### Corregido
- Carácter extraño al inicio de InitializeCommand.php que causaba error de namespace
- Problemas de PSR-4 autoloading en archivos PHP

## [1.3.2] - 2025-01-27

### Corregido
- Directorio de tests faltante que causaba errores en GitHub Actions
- Configuración de Pest para testing con Laravel
- Dependencias de testing agregadas (orchestra/testbench, pest-plugin-laravel)

### Agregado
- Estructura básica de tests con Pest
- Tests unitarios y de feature
- Configuración de TestCase para packages de Laravel

## [1.3.1] - 2025-01-27

### Corregido
- Sincronización de versiones entre todos los archivos del proyecto
- Inconsistencias en las versiones de composer.json, package.json y config/automation.php

## [1.3.0] - 2025-01-27

### Agregado
- Descripciones específicas y claras para todos los comandos según la guía de Mort
- Verificaciones de disponibilidad de NPM antes de ejecutar comandos Node.js
- Tips útiles en comandos MCP y Stripe para mejor experiencia de usuario
- Mejoras en el sistema de ayuda con descripciones más detalladas

### Corregido
- ExecutesCommands trait ahora es compatible con Laravel Process
- Rutas de configuración corregidas en InitializeCommand
- Manejo robusto de comandos NPM opcionales en todos los comandos de desarrollo
- Compatibilidad mejorada con diferentes entornos de desarrollo

### Mejorado
- README.md con descripciones específicas de qué hace cada comando
- HelpCommand con descripciones más detalladas y ejemplos claros
- SystemMonitoringCommand con encabezado más claro
- Arquitectura general siguiendo principios SOLID y Clean Code
- Documentación siguiendo convenciones de la guía de Mort

## [1.0.0] - 2025-09-02

### Agregado
- Comando `mort:status` para mostrar el estado del sistema
- Comando `mort:help` para mostrar ayuda completa
- Comando `mort:dev` para automatización de desarrollo
- Comando `mort:workflow` para automatización de workflow
- Comando `mort:mcp` para operaciones con MCPs
- Comando `mort:stripe` para operaciones con Stripe
- Comando `mort:monitor` para monitoreo del sistema
- Service Provider para registro automático de comandos
- Configuración flexible via archivo `config/automation.php`
- Trait `ExecutesCommands` para ejecución segura de comandos
- Interface `AutomationInterface` para estandarización
- Documentación completa en README.md
- Soporte para variables de entorno configurables
- Integración con Laravel Boost, GitHub MCP y Stripe MCP
- Monitoreo de métricas de sistema, base de datos y seguridad
- Workflow automatizado para desarrollo (feature, staging, producción)
- Sincronización automática con Stripe
- Exportación de métricas en formato JSON

### Características
- **Modularidad**: Componentes independientes y reutilizables
- **Extensibilidad**: Fácil agregar nuevos comandos
- **Configurabilidad**: Configuración flexible via archivos y variables de entorno
- **Mantenibilidad**: Código organizado siguiendo principios SOLID
- **Documentación**: Documentación completa con ejemplos
- **Testing**: Preparado para testing con Pest
- **Linting**: Compatible con Laravel Pint

### Compatibilidad
- PHP ^8.4
- Laravel ^12.0
- Stripe PHP SDK ^14.0

### Instalación
```bash
composer require mort/automation
php artisan vendor:publish --provider="Mort\Automation\AutomationServiceProvider" --tag="config"
```

### Uso Básico
```bash
# Ver estado del sistema
php artisan mort:status

# Configurar proyecto
php artisan mort:dev setup

# Iniciar nueva feature
php artisan mort:workflow start-feature --name="mi-feature"

# Monitorear sistema
php artisan mort:monitor

# Verificar MCPs
php artisan mort:mcp mcp-status
```

---

## [Unreleased]

### Planificado
- Dashboard web para monitoreo
- Integración completa con MCPs reales
- Métricas avanzadas de rendimiento
- Integración con CI/CD
- Soporte para múltiples bases de datos
- Integración con servicios de logging
- Soporte para microservicios
- Tests automatizados
- Integración con GitHub Actions
- Soporte para Docker
- Métricas en tiempo real
- Alertas automáticas
- Backup automatizado
- Restauración automatizada
