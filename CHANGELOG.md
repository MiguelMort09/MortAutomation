# Changelog

Todos los cambios notables de este proyecto serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/),
y este proyecto adhiere a [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
