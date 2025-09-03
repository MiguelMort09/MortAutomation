# Changelog

Todos los cambios notables de este proyecto serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/),
y este proyecto adhiere a [Semantic Versioning](https://semver.org/spec/v2.0.0.html).



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
