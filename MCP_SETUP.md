# Guía de Configuración y Uso de MCP (Model Context Protocol)

Esta guía explica cómo conectar **Claude Desktop** con tu aplicación Laravel usando **Mort Automation**.

## 1. ¿Qué es esto?
El **Servidor MCP** (`mort:mcp-server`) permite que Claude Desktop "vea" y "ejecute" herramientas dentro de tu proyecto Laravel. Por ejemplo, puedes pedirle a Claude: *"Crea un producto en Stripe llamado 'Suscripción Premium' por $50"* y Claude ejecutará el código real de tu aplicación.

## 2. Configuración

Necesitas editar el archivo de configuración de Claude Desktop.

### Paso 1: Ubicar el archivo de configuración
- **Windows**: `%APPDATA%\Claude\claude_desktop_config.json`
  (Normalmente en `C:\Users\TuUsuario\AppData\Roaming\Claude\claude_desktop_config.json`)
- **Mac**: `~/Library/Application Support/Claude/claude_desktop_config.json`

Si el archivo no existe, créalo.

### Paso 2: Agregar el servidor Mort Automation
Agrega el siguiente bloque al archivo JSON. Asegúrate de ajustar la ruta `cwd` a la carpeta raíz de tu proyecto Laravel.

```json
{
  "mcpServers": {
    "mort-automation": {
      "command": "php",
      "args": [
        "artisan",
        "mort:mcp-server"
      ],
      "cwd": "C:\\Users\\mortm\\Herd\\MortAutomation", 
      "env": {
        "APP_ENV": "local"
      }
    }
  }
}
```
> **Nota**: Si ya tienes otros servidores MCP configurados, solo agrega la clave `"mort-automation"` dentro de `"mcpServers"`.

### Paso 3: Reiniciar Claude
Cierra completamente Claude Desktop y vuélvelo a abrir. Deberías ver un icono de enchufe 🔌 (o similar) indicando que las herramientas están conectadas.

## 3. Uso

Una vez conectado, puedes hablar con Claude normalmente y pedirle que realice tareas de Stripe.

### Ejemplos de Prompts:

- **Crear Cliente**:
  > "Crea un nuevo cliente en Stripe llamado 'Juan Pérez' con el email 'juan@example.com'."

- **Crear Producto y Precio**:
  > "Crea un producto 'Curso de Laravel' y asígnale un precio de $99 USD."

- **Listar Datos**:
  > "¿Cuáles son los últimos 5 productos que tengo en Stripe?"

- **Generar Link de Pago**:
  > "Genera un link de pago para el producto 'Curso de Laravel' (busca su precio primero)."

## 4. Solución de Problemas

- **Error "command not found"**: Asegúrate de que `php` está en tu PATH de Windows.
- **Error de conexión**: Verifica que la ruta en `cwd` sea correcta y apunte a la carpeta donde está `artisan`.
- **Logs**: El servidor MCP escribe errores en el log de Claude (o en stderr si lo ejecutas manualmente).
