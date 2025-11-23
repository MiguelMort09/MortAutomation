# Guía de Integración con LLMs (IA)

Mort Automation está diseñado para ser utilizado tanto por humanos como por Inteligencias Artificiales (LLMs) como Claude, GPT-4, etc.

Existen dos formas principales de integración:

## 1. Integración Nativa con Claude (vía MCP)

Esta es la forma recomendada para usuarios de **Claude Desktop**. Utiliza el "Model Context Protocol" para que Claude pueda descubrir y ejecutar herramientas automáticamente.

### Configuración
Edita tu archivo de configuración de Claude (`claude_desktop_config.json`):

```json
{
  "mcpServers": {
    "mort-automation": {
      "command": "php",
      "args": ["artisan", "mort:mcp-server"],
      "cwd": "C:\\Ruta\\A\\Tu\\Proyecto\\Laravel",
      "env": { "APP_ENV": "local" }
    }
  }
}
```

### Uso
Una vez conectado, simplemente pídele cosas a Claude:
- "Crea un producto en Stripe llamado 'Suscripción Gold' por $50"
- "Lista los últimos 5 clientes"
- "Genera un link de pago para el producto X"

Claude usará el comando `mort:mcp-server` internamente para ejecutar estas acciones.

---

## 2. Integración vía CLI (Terminal)

Si estás usando scripts, GitHub Copilot, o un agente de IA personalizado que tiene acceso a la terminal, puedes usar los comandos de Artisan directamente.

Todos los comandos han sido optimizados para soportar el flag `--force` y argumentos explícitos, evitando preguntas interactivas que bloquearían a la IA.

### Comandos Clave para IA

#### Stripe
- **Crear Cliente**:
  `php artisan mort:stripe create-customer --name="John Doe" --email="john@example.com"`
- **Crear Producto**:
  `php artisan mort:stripe create-product --name="Premium Plan" --amount=1000 --currency=usd`
- **Crear Link de Pago**:
  `php artisan mort:stripe create-payment-link --price="price_xxx" --quantity=1`

#### Workflow (Git)
- **Iniciar Feature**:
  `php artisan mort:workflow start-feature --name="login-page" --force`
- **Completar Feature**:
  `php artisan mort:workflow complete-feature --force`
  *(El flag `--force` confirma automáticamente commits y linting)*
- **Deploy**:
  `php artisan mort:workflow deploy-production --force`

#### Release
- **Crear Release**:
  `php artisan mort:release patch --force`
  *(Crea tag, actualiza changelog y hace push sin preguntar)*

### Consejos para Agentes de IA
1.  **Siempre usa argumentos**: No dependas de los valores por defecto si puedes ser explícito.
2.  **Usa `--force`**: Para comandos destructivos o de flujo de trabajo (deploy, release), usa siempre `--force` para evitar bloqueos por confirmación.
3.  **Lee la salida JSON**: El servidor MCP devuelve respuestas en JSON, lo cual es ideal para parsear. Los comandos CLI devuelven texto legible por humanos, pero estructurado.
