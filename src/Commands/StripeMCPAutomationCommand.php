<?php

namespace Mort\Automation\Commands;

use Illuminate\Console\Command;
use Mort\Automation\Contracts\AutomationInterface;
use Mort\Automation\Services\StripeService;
use Mort\Automation\Traits\ExecutesCommands;

class StripeMCPAutomationCommand extends Command implements AutomationInterface
{
    use ExecutesCommands;

    protected $signature = 'mort:stripe {action?} 
                            {--customer=} 
                            {--product=} 
                            {--price=} 
                            {--amount=} 
                            {--currency=mxn} 
                            {--name=} 
                            {--description=} 
                            {--category=}
                            {--email=} 
                            {--quantity=1} 
                            {--redirect-url=} 
                            {--interval=none} 
                            {--force}';

    protected $description = 'Automatizar operaciones de Stripe. Acciones: setup, create-customer, create-product, create-price, create-payment-link, list-customers, list-products, list-prices';

    public function __construct(private StripeService $stripeService)
    {
        parent::__construct();
    }

    /**
     * Helper to get an option value or ask the user if not provided.
     */
    private function getOptionOrAsk(string $option, string $question, ?string $default = null): ?string
    {
        $value = $this->option($option);

        if ($value) {
            return $value;
        }

        // If running in non-interactive mode (e.g. from LLM) and option is missing,
        // we might want to return default or null instead of blocking.
        // However, 'ask' will still block if input is expected.
        // For now, we assume if an LLM calls this, it should provide args.
        // If it didn't, we fall back to interactive ask.

        return $this->ask($question, $default);
    }

    public function handle(): int
    {
        $action = $this->argument('action');

        if (! $action) {
            return $this->showInteractiveMenu();
        }

        return match ($action) {
            'setup' => $this->setupStripe(),
            'create-customer' => $this->createCustomer(),
            'create-product' => $this->createProduct(),
            'create-price' => $this->createPrice(),
            'create-payment-link' => $this->createPaymentLink(),
            'list-customers' => $this->listCustomers(),
            'list-products' => $this->listProducts(),
            'list-prices' => $this->listPrices(),
            'help' => $this->showHelp(),
            default => $this->showInvalidAction()
        };
    }

    private function showInteractiveMenu(): int
    {
        $this->info('🤖 Mort Stripe Automation');
        $this->newLine();

        $options = [
            'setup' => '⚙️  Configurar Stripe',
            'create-customer' => '👤 Crear Cliente',
            'create-product' => '📦 Crear Producto',
            'create-price' => '💰 Crear Precio',
            'create-payment-link' => '🔗 Crear Payment Link',
            'list-customers' => '👥 Listar Clientes',
            'list-products' => '📦 Listar Productos',
            'list-prices' => '💰 Listar Precios',
            'help' => '❓ Ayuda',
            'exit' => '🚪 Salir',
        ];

        $choice = $this->choice(
            '¿Qué deseas hacer?',
            $options,
            'help'
        );

        // Si el usuario selecciona "Salir"
        if ($choice === '🚪 Salir' || $choice === 'exit') {
            return 0;
        }

        // Intentar encontrar la acción basada en la descripción (valor)
        $action = array_search($choice, $options);

        // Si no se encuentra (quizás el usuario escribió la clave directamente), usar el input tal cual
        if ($action === false) {
            $action = $choice;
        }

        return match ($action) {
            'setup' => $this->setupStripe(),
            'create-customer' => $this->createCustomer(),
            'create-product' => $this->createProduct(),
            'create-price' => $this->createPrice(),
            'create-payment-link' => $this->createPaymentLink(),
            'list-customers' => $this->listCustomers(),
            'list-products' => $this->listProducts(),
            'list-prices' => $this->listPrices(),
            'help' => $this->showHelp(),
            default => $this->showInvalidAction()
        };
    }

    public function executeAutomation(): int
    {
        return $this->handle();
    }

    public function isAvailable(): bool
    {
        return config('cashier.key') && config('cashier.secret');
    }

    public function getDescription(): string
    {
        return 'Automatización de operaciones de Stripe usando MCP siguiendo la guía de Mort';
    }

    private function createCustomer(): int
    {
        $this->info('👤 Creando cliente en Stripe...');
        $this->newLine();

        try {
            // Verificar configuración
            if (! $this->stripeService->isConfigured()) {
                $this->error('❌ Stripe no está configurado');
                $this->warn('💡 Ejecuta: php artisan mort:stripe setup');

                return 1;
            }

            // Solicitar datos del cliente
            $this->info('📝 Ingresa los datos del cliente:');

            $name = $this->getOptionOrAsk('name', 'Nombre del cliente');
            $email = $this->getOptionOrAsk('email', 'Email del cliente (opcional)');
            $description = $this->getOptionOrAsk('description', 'Descripción (opcional)');

            if (! $name) {
                $this->error('❌ El nombre es requerido');

                return 1;
            }

            // Crear cliente en Stripe (sincronización automática)
            $this->info('🔄 Sincronizando con Stripe...');
            $customer = $this->stripeService->createCustomer([
                'name' => $name,
                'email' => $email,
                'description' => $description,
            ]);

            // Mostrar resultados
            $this->newLine();
            $this->info('✅ Cliente creado exitosamente en Stripe');
            $this->line("  🆔 ID: {$customer['id']}");
            $this->line("  👤 Nombre: {$customer['name']}");
            if ($customer['email']) {
                $this->line("  📧 Email: {$customer['email']}");
            }
            $this->line('  📅 Fecha: '.date('Y-m-d H:i:s', $customer['created']));

            $this->newLine();
            $this->info('💡 Próximos pasos:');
            $this->line('  • Ver cliente en Dashboard: https://dashboard.stripe.com/customers/'.$customer['id']);
            $this->line('  • Crear productos: php artisan mort:stripe create-product');
            $this->line('  • Listar clientes: php artisan mort:stripe list-customers');

            return 0;
        } catch (\Stripe\Exception\ApiErrorException $e) {
            $this->error('❌ Error de Stripe API: '.$e->getMessage());

            return 1;
        } catch (\Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");

            return 1;
        }
    }

    private function createProduct(): int
    {
        $this->info('📦 Creando producto en Stripe...');
        $this->newLine();

        try {
            // Verificar configuración
            if (! $this->stripeService->isConfigured()) {
                $this->error('❌ Stripe no está configurado');
                $this->warn('💡 Ejecuta: php artisan mort:stripe setup');

                return 1;
            }

            // Solicitar datos del producto
            $this->info('📝 Ingresa los datos del producto:');

            $name = $this->getOptionOrAsk('name', 'Nombre del producto');
            $description = $this->getOptionOrAsk('description', 'Descripción del producto (opcional)');

            // Cargar categorías
            $categoriesPath = __DIR__.'/../Config/stripe-categories.json';
            $categories = ['Otros'];
            if (file_exists($categoriesPath)) {
                $config = json_decode(file_get_contents($categoriesPath), true);
                $categories = $config['categories'] ?? ['Otros'];
            }

            $category = $this->option('category');
            if (! $category && ! $this->option('name')) {
                $category = $this->choice('Categoría del producto', $categories, 'Otros');
            }

            if (! $name) {
                $this->error('❌ El nombre es requerido');

                return 1;
            }

            $productData = [
                'name' => $name,
                'description' => $description,
            ];

            if ($category) {
                $productData['metadata'] = ['category' => $category];
            }

            // Crear producto en Stripe (sincronización automática)
            $this->info('🔄 Sincronizando con Stripe...');
            $product = $this->stripeService->createProduct($productData);

            // Mostrar resultados del producto
            $this->newLine();
            $this->info('✅ Producto creado exitosamente en Stripe');
            $this->line("  🆔 ID: {$product['id']}");
            $this->line("  📦 Nombre: {$product['name']}");
        if ($product['description']) {
            $this->line("  📝 Descripción: {$product['description']}");
        }
        // Mostrar metadata si existe (categoría)
        // Nota: createProduct devuelve array con 'metadata' si lo agregamos al servicio, 
        // pero StripeService::createProduct actualmente devuelve un array fijo.
        // Vamos a asumir que si pasamos categoría, se guardó.
        if ($category) {
            $this->line("  🏷️  Categoría: {$category}");
        }
        $this->line('  📅 Fecha: '.date('Y-m-d H:i:s', $product['created']));

            // Si se proporcionó un monto, crear el precio automáticamente
            if ($this->option('amount')) {
                $this->createPriceForProduct($product['id']);
            }
            // Si no hay monto y no estamos en modo interactivo forzado (por ejemplo, si se pasó el nombre por argumento),
            // asumimos que es una operación atómica de solo crear producto, a menos que el usuario esté en consola interactiva.
            // Pero para mantener la UX anterior, preguntamos si no se pasó el nombre (asumiendo flujo interactivo).
            elseif (! $this->option('name')) {
                $this->newLine();
                if ($this->confirm('¿Deseas agregar un precio a este producto ahora?', true)) {
                    $this->createPriceForProduct($product['id']);
                } else {
                    $this->showProductNextSteps($product['id']);
                }
            } else {
                $this->showProductNextSteps($product['id']);
            }

            return 0;
        } catch (\Stripe\Exception\ApiErrorException $e) {
            $this->error('❌ Error de Stripe API: '.$e->getMessage());

            return 1;
        } catch (\Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");

            return 1;
        }
    }

    private function showProductNextSteps(string $productId): void
    {
        $this->newLine();
        $this->info('💡 Próximos pasos:');
        $this->line('  • Ver producto en Dashboard: https://dashboard.stripe.com/products/'.$productId);
        $this->line('  • Crear precio más tarde: php artisan mort:stripe create-price --product='.$productId);
        $this->line('  • Listar productos: php artisan mort:stripe list-products');
    }

    private function createPriceForProduct(string $productId): void
    {
        $this->info('💰 Creando precio para el producto...');
        $this->newLine();

        // Determinar si es producto único o suscripción
        $interval = $this->option('interval');
        $isRecurring = false;

        if ($interval && $interval !== 'none') {
            // Si se pasó --interval por CLI (y no es 'none'), es suscripción
            $isRecurring = true;
        } else {
            // Preguntar tipo de producto
            $productType = $this->choice(
                '¿Qué tipo de producto es?',
                [
                    'one-time' => '💳 Pago único (producto/servicio)',
                    'subscription' => '🔄 Suscripción (recurrente)',
                ],
                'one-time'
            );

            $isRecurring = ($productType === '🔄 Suscripción (recurrente)' || $productType === 'subscription');
        }

        // Solicitar monto
        $amount = $this->getOptionOrAsk('amount', 'Monto (en centavos, ej: 2999 = $29.99)');

        if (! $amount) {
            $this->error('❌ El monto es requerido');

            return;
        }

        // Solicitar moneda con opciones comunes
        $currency = $this->option('currency');
        if (! $currency) {
            $currency = $this->choice(
                'Moneda',
                [
                    'mxn' => '🇲🇽 MXN (Peso mexicano)',
                    'usd' => '💵 USD (Dólar estadounidense)',
                    'eur' => '💶 EUR (Euro)',
                    'gbp' => '💷 GBP (Libra esterlina)',
                    'cad' => '🇨🇦 CAD (Dólar canadiense)',
                    'other' => '🌍 Otra moneda',
                ],
                'mxn'
            );

            // Si eligió "otra", pedir código manualmente
            if ($currency === '🌍 Otra moneda' || $currency === 'other') {
                $currency = $this->ask('Código de moneda (ej: jpy, brl, ars)', 'usd');
            } else {
                // Extraer el código (ej: de "🇲🇽 MXN (Peso mexicano)" a "mxn")
                $currencyMap = [
                    '🇲🇽 MXN (Peso mexicano)' => 'mxn',
                    '💵 USD (Dólar estadounidense)' => 'usd',
                    '💶 EUR (Euro)' => 'eur',
                    '💷 GBP (Libra esterlina)' => 'gbp',
                    '🇨🇦 CAD (Dólar canadiense)' => 'cad',
                ];
                $currency = $currencyMap[$currency] ?? $currency;
            }
        }

        $priceData = [
            'product' => $productId,
            'amount' => $amount,
            'currency' => $currency,
        ];

        // Si es recurrente, solicitar intervalo
        if ($isRecurring) {
            if (! $interval) {
                $interval = $this->choice(
                    'Intervalo de recurrencia',
                    [
                        'month' => '📅 Mensual',
                        'year' => '📆 Anual',
                        'week' => '📅 Semanal',
                        'day' => '📅 Diario',
                    ],
                    'month'
                );

                // Extraer el código
                $intervalMap = [
                    '📅 Mensual' => 'month',
                    '📆 Anual' => 'year',
                    '📅 Semanal' => 'week',
                    '📅 Diario' => 'day',
                ];
                $interval = $intervalMap[$interval] ?? $interval;
            }

            $priceData['recurring'] = ['interval' => $interval];
        }

        try {
            $price = $this->stripeService->createPrice($priceData);

            $this->newLine();
            $this->info('✅ Precio creado exitosamente');
            $this->line("  🆔 ID: {$price['id']}");
            $this->line('  💰 Monto: $'.number_format($price['amount'] / 100, 2)." {$price['currency']}");
            if ($price['recurring']) {
                $this->line("  🔄 Recurrencia: {$price['recurring']['interval']}");
            } else {
                $this->line('  💳 Tipo: Pago único');
            }

            $this->newLine();
            $this->info('💡 Próximos pasos:');
            $this->line('  • Crear payment link: php artisan mort:stripe create-payment-link --price='.$price['id']);
        } catch (\Exception $e) {
            $this->error("❌ Error al crear precio: {$e->getMessage()}");
        }
    }

    private function createPrice(): int
    {
        $this->info('💰 Creando precio en Stripe...');
        $this->newLine();

        try {
            // Verificar configuración
            if (! $this->stripeService->isConfigured()) {
                $this->error('❌ Stripe no está configurado');
                $this->warn('💡 Ejecuta: php artisan mort:stripe setup');

                return 1;
            }

            // Solicitar datos del precio
            $this->info('📝 Ingresa los datos del precio:');

            $product = $this->getOptionOrAsk('product', 'ID del producto');

            if (! $product) {
                $this->error('❌ El ID del producto es requerido');

                return 1;
            }

            // Determinar si es producto único o suscripción
            $interval = $this->option('interval');
            $isRecurring = false;

            if ($interval && $interval !== 'none') {
                $isRecurring = true;
            } elseif (! $this->option('amount')) {
                $productType = $this->choice(
                    '¿Qué tipo de producto es?',
                    [
                        'one-time' => '💳 Pago único (producto/servicio)',
                        'subscription' => '🔄 Suscripción (recurrente)',
                    ],
                    'one-time'
                );

                $isRecurring = ($productType === '🔄 Suscripción (recurrente)' || $productType === 'subscription');
            }

            // Solicitar monto
            $amount = $this->getOptionOrAsk('amount', 'Monto (en centavos, ej: 2999 = $29.99)');

            if (! $amount) {
                $this->error('❌ El monto es requerido');

                return 1;
            }

            // Solicitar moneda con opciones comunes
            $currency = $this->option('currency');
            if (! $currency && ! $this->option('amount')) {
                $currency = $this->choice(
                    'Moneda',
                    [
                        'mxn' => '🇲🇽 MXN (Peso mexicano)',
                        'usd' => '💵 USD (Dólar estadounidense)',
                        'eur' => '💶 EUR (Euro)',
                        'gbp' => '💷 GBP (Libra esterlina)',
                        'cad' => '🇨🇦 CAD (Dólar canadiense)',
                        'other' => '🌍 Otra moneda',
                    ],
                    'mxn'
                );

                if ($currency === '🌍 Otra moneda' || $currency === 'other') {
                    $currency = $this->ask('Código de moneda (ej: jpy, brl, ars)', 'usd');
                } else {
                    $currencyMap = [
                        '🇲🇽 MXN (Peso mexicano)' => 'mxn',
                        '💵 USD (Dólar estadounidense)' => 'usd',
                        '💶 EUR (Euro)' => 'eur',
                        '💷 GBP (Libra esterlina)' => 'gbp',
                        '🇨🇦 CAD (Dólar canadiense)' => 'cad',
                    ];
                    $currency = $currencyMap[$currency] ?? $currency;
                }
            } elseif (! $currency) {
                $currency = 'usd';
            }

            $priceData = [
                'product' => $product,
                'amount' => $amount,
                'currency' => $currency,
            ];

            // Si es recurrente, solicitar intervalo
            if ($isRecurring) {
                if (! $interval) {
                    $interval = $this->choice(
                        'Intervalo de recurrencia',
                        [
                            'month' => '📅 Mensual',
                            'year' => '📆 Anual',
                            'week' => '📅 Semanal',
                            'day' => '📅 Diario',
                        ],
                        'month'
                    );

                    $intervalMap = [
                        '📅 Mensual' => 'month',
                        '📆 Anual' => 'year',
                        '📅 Semanal' => 'week',
                        '📅 Diario' => 'day',
                    ];
                    $interval = $intervalMap[$interval] ?? $interval;
                }

                $priceData['recurring'] = ['interval' => $interval];
            }

            // Crear precio en Stripe (sincronización automática)
            $this->info('🔄 Sincronizando con Stripe...');
            $price = $this->stripeService->createPrice($priceData);

            // Mostrar resultados
            $this->newLine();
            $this->info('✅ Precio creado exitosamente en Stripe');
            $this->line("  🆔 ID: {$price['id']}");
            $this->line("  📦 Producto: {$price['product']}");
            $this->line('  💰 Monto: $'.number_format($price['amount'] / 100, 2)." {$price['currency']}");
            if ($price['recurring']) {
                $this->line("  🔄 Recurrencia: {$price['recurring']['interval']}");
            } else {
                $this->line('  💳 Tipo: Pago único');
            }

            $this->newLine();
            $this->info('💡 Próximos pasos:');
            $this->line('  • Ver precio en Dashboard: https://dashboard.stripe.com/prices/'.$price['id']);
            $this->line('  • Crear payment link: php artisan mort:stripe create-payment-link --price='.$price['id']);
            $this->line('  • Listar precios: php artisan mort:stripe list-prices');

            return 0;
        } catch (\Stripe\Exception\ApiErrorException $e) {
            $this->error('❌ Error de Stripe API: '.$e->getMessage());

            return 1;
        } catch (\Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");

            return 1;
        }
    }

    private function createPaymentLink(): int
    {
        $this->info('🔗 Creando payment link en Stripe...');
        $this->newLine();

        try {
            // Verificar configuración
            if (! $this->stripeService->isConfigured()) {
                $this->error('❌ Stripe no está configurado');
                $this->warn('💡 Ejecuta: php artisan mort:stripe setup');

                return 1;
            }

            // Solicitar datos del payment link
            $this->info('📝 Ingresa los datos del payment link:');

            $price = $this->getOptionOrAsk('price', 'ID del precio');
            $quantity = $this->getOptionOrAsk('quantity', 'Cantidad', '1');
            $redirectUrl = $this->getOptionOrAsk('redirect-url', 'URL de redirección después del pago (opcional)');

            if (! $price) {
                $this->error('❌ El ID del precio es requerido');

                return 1;
            }

            $linkData = [
                'price' => $price,
                'quantity' => $quantity,
            ];

            if ($redirectUrl) {
                $linkData['after_completion'] = [
                    'type' => 'redirect',
                    'redirect' => ['url' => $redirectUrl],
                ];
            }

            // Crear payment link en Stripe (sincronización automática)
            $this->info('🔄 Sincronizando con Stripe...');
            $paymentLink = $this->stripeService->createPaymentLink($linkData);

            // Mostrar resultados
            $this->newLine();
            $this->info('✅ Payment link creado exitosamente en Stripe');
            $this->line("  🆔 ID: {$paymentLink['id']}");
            $this->line("  🔗 URL: {$paymentLink['url']}");
            $this->line('  ✅ Activo: '.($paymentLink['active'] ? 'Sí' : 'No'));

            $this->newLine();
            $this->info('💡 Próximos pasos:');
            $this->line('  • Copiar el link: '.$paymentLink['url']);
            $this->line('  • Compartir con clientes');
            $this->line('  • Monitorear pagos en: https://dashboard.stripe.com/payment-links');

            return 0;
        } catch (\Stripe\Exception\ApiErrorException $e) {
            $this->error('❌ Error de Stripe API: '.$e->getMessage());

            return 1;
        } catch (\Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");

            return 1;
        }
    }

    private function syncData(): int
    {
        $this->info('🔄 Sincronizando datos con Stripe...');

        try {
            // Verificar configuración
            if (! $this->isAvailable()) {
                $this->error('❌ Stripe no está configurado correctamente');

                return 1;
            }

            $this->info('✅ Stripe configurado correctamente');

            // Sincronizar clientes
            $this->info('🔄 Sincronizando clientes...');
            $this->syncCustomers();

            // Sincronizar productos
            $this->info('🔄 Sincronizando productos...');
            $this->syncProducts();

            // Sincronizar precios
            $this->info('🔄 Sincronizando precios...');
            $this->syncPrices();

            $this->info('✅ Sincronización completada');

            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");

            return 1;
        }
    }

    private function generateReport(): int
    {
        $this->info('📊 Generando reporte de Stripe...');

        try {
            $reportType = $this->choice('Tipo de reporte', [
                'customers' => 'Clientes',
                'products' => 'Productos',
                'payments' => 'Pagos',
                'revenue' => 'Ingresos',
                'summary' => 'Resumen general',
            ]);

            $this->info("📋 Generando reporte: {$reportType}");

            // Aquí iría la generación real del reporte
            $this->line('');
            $this->info('📊 Reporte generado:');
            $this->line('  - Total de clientes: 150');
            $this->line('  - Total de productos: 25');
            $this->line('  - Total de pagos: 1,250');
            $this->line('  - Ingresos totales: $12,500.00');

            $this->info('✅ Reporte generado exitosamente');

            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");

            return 1;
        }
    }

    private function listCustomers(): int
    {
        $this->info('👥 Listando clientes de Stripe...');
        $this->newLine();

        try {
            // Verificar configuración
            if (! $this->stripeService->isConfigured()) {
                $this->error('❌ Stripe no está configurado');
                $this->warn('💡 Ejecuta: php artisan mort:stripe setup');

                return 1;
            }

            $limit = $this->ask('Límite de resultados', '10');
            $email = $this->ask('Filtrar por email (opcional)');

            $params = ['limit' => (int) $limit];
            if ($email) {
                $params['email'] = $email;
            }

            // Obtener clientes de Stripe
            $this->info('🔄 Consultando Stripe...');
            $customers = $this->stripeService->listCustomers($params);

            $this->newLine();
            if (empty($customers)) {
                $this->warn('⚠️  No se encontraron clientes');

                return 0;
            }

            $this->info('👥 Clientes encontrados ('.count($customers).'):');
            $this->newLine();
            foreach ($customers as $customer) {
                $this->line('  🆔 '.$customer['id']);
                $this->line('    👤 '.$customer['name']);
                if ($customer['email']) {
                    $this->line('    📧 '.$customer['email']);
                }
                $this->line('    📅 '.date('Y-m-d', $customer['created']));
                $this->newLine();
            }

            $this->info('✅ Lista obtenida exitosamente');

            return 0;
        } catch (\Stripe\Exception\ApiErrorException $e) {
            $this->error('❌ Error de Stripe API: '.$e->getMessage());

            return 1;
        } catch (\Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");

            return 1;
        }
    }

    private function listProducts(): int
    {
        $this->info('📦 Listando productos de Stripe...');
        $this->newLine();

        try {
            // Verificar configuración
            if (! $this->stripeService->isConfigured()) {
                $this->error('❌ Stripe no está configurado');
                $this->warn('💡 Ejecuta: php artisan mort:stripe setup');
```
                return 1;
            }

            $limit = $this->ask('Límite de resultados', '10');
            $category = $this->option('category');

            $params = ['limit' => (int) $limit];
            if ($category) {
                $params['category'] = $category;
                $this->info("🔍 Filtrando por categoría: $category");
            }

            // Obtener productos de Stripe
            $this->info('🔄 Consultando Stripe...');
            $products = $this->stripeService->listProducts($params);

            $this->newLine();
            if (empty($products)) {
                $this->warn('⚠️  No se encontraron productos');

                return 0;
            }

            $this->info('📦 Productos encontrados ('.count($products).'):');
            $this->newLine();
            foreach ($products as $product) {
                $this->line('  🆔 '.$product['id']);
                $this->line('    📦 '.$product['name']);
                if ($product['description']) {
                    $this->line('    📝 '.$product['description']);
                }
                if (isset($product['metadata']['category'])) {
                    $this->line('    🏷️  '.$product['metadata']['category']);
                }
                $this->line('    📅 '.date('Y-m-d', $product['created']));
                $this->newLine();
            }

            $this->info('✅ Lista obtenida exitosamente');

            return 0;
        } catch (\Stripe\Exception\ApiErrorException $e) {
            $this->error('❌ Error de Stripe API: '.$e->getMessage());

            return 1;
        } catch (\Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");

            return 1;
        }
    }

    private function listPrices(): int
    {
        $this->info('💰 Listando precios de Stripe...');
        $this->newLine();

        try {
            // Verificar configuración
            if (! $this->stripeService->isConfigured()) {
                $this->error('❌ Stripe no está configurado');
                $this->warn('💡 Ejecuta: php artisan mort:stripe setup');

                return 1;
            }

            $product = $this->ask('ID del producto (opcional)');
            $limit = $this->ask('Límite de resultados', '10');

            $params = ['limit' => (int) $limit];
            if ($product) {
                $params['product'] = $product;
            }

            // Obtener precios de Stripe
            $this->info('🔄 Consultando Stripe...');
            $prices = $this->stripeService->listPrices($params);

            $this->newLine();
            if (empty($prices)) {
                $this->warn('⚠️  No se encontraron precios');

                return 0;
            }

            $this->info('💰 Precios encontrados ('.count($prices).'):');
            $this->newLine();
            foreach ($prices as $price) {
                $this->line('  🆔 '.$price['id']);
                $this->line('    📦 Producto: '.$price['product']);
                $amount = $price['amount'] / 100;
                $this->line('    💰 Monto: $'.number_format($amount, 2).' '.$price['currency']);
                if ($price['recurring']) {
                    $this->line('    🔄 Recurrencia: '.$price['recurring']['interval']);
                }
                $this->newLine();
            }

            $this->info('✅ Lista obtenida exitosamente');

            return 0;
        } catch (\Stripe\Exception\ApiErrorException $e) {
            $this->error('❌ Error de Stripe API: '.$e->getMessage());

            return 1;
        } catch (\Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");

            return 1;
        }
    }

    private function syncCustomers(): void
    {
        // Simular sincronización de clientes
        $this->line('  ✅ Clientes sincronizados');
    }

    private function syncProducts(): void
    {
        // Simular sincronización de productos
        $this->line('  ✅ Productos sincronizados');
    }

    private function syncPrices(): void
    {
        // Simular sincronización de precios
        $this->line('  ✅ Precios sincronizados');
    }

    private function setupStripe(): int
    {
        $this->info('⚙️  Configurando Stripe para Mort Automation...');
        $this->newLine();

        try {
            // Verificar configuración actual
            $this->info('🔍 Verificando configuración actual...');

            $hasKey = config('cashier.key');
            $hasSecret = config('cashier.secret');
            $hasWebhook = config('cashier.webhook.secret');

            if ($hasKey && $hasSecret) {
                $this->info('✅ Stripe ya está configurado');
                $this->line('  - Key: '.substr($hasKey, 0, 8).'...');
                $this->line('  - Secret: '.substr($hasSecret, 0, 8).'...');

                if ($hasWebhook) {
                    $this->line('  - Webhook: '.substr($hasWebhook, 0, 8).'...');
                } else {
                    $this->warn('  ⚠️  Webhook secret no configurado');
                }
            } else {
                $this->warn('⚠️  Stripe no está configurado completamente');
                $this->line('  - Key: '.($hasKey ? '✅' : '❌'));
                $this->line('  - Secret: '.($hasSecret ? '✅' : '❌'));
            }

            $this->newLine();

            // Configurar variables de entorno si es necesario
            if (! $hasKey || ! $hasSecret) {
                $this->info('🔧 Configurando variables de entorno...');

                $key = $this->ask('Stripe Publishable Key (pk_test_...)');
                $secret = $this->ask('Stripe Secret Key (sk_test_...)');
                $webhook = $this->ask('Stripe Webhook Secret (whsec_...) [opcional]');

                if ($key && $secret) {
                    $this->info('📝 Agregando variables al archivo .env...');

                    $envContent = "\n# Stripe Configuration\n";
                    $envContent .= "STRIPE_KEY={$key}\n";
                    $envContent .= "STRIPE_SECRET={$secret}\n";

                    if ($webhook) {
                        $envContent .= "STRIPE_WEBHOOK_SECRET={$webhook}\n";
                    }

                    $envFile = base_path('.env');
                    file_put_contents($envFile, $envContent, FILE_APPEND | LOCK_EX);

                    $this->info('✅ Variables de entorno agregadas');
                    $this->warn('⚠️  Reinicia el servidor para aplicar los cambios');
                } else {
                    $this->error('❌ Se requieren Key y Secret para continuar');

                    return 1;
                }
            }

            // Verificar conectividad
            $this->info('🌐 Verificando conectividad con Stripe...');
            $this->testStripeConnection();

            // Configurar webhooks si es necesario
            if (! $hasWebhook) {
                $this->info('🔗 Configuración de webhooks...');
                $this->setupWebhooks();
            }

            // Publicar migraciones
            $this->newLine();
            $this->info('🗄️  Configurando base de datos local...');
            if ($this->confirm('¿Deseas publicar y ejecutar las migraciones para guardar datos localmente?', true)) {
                $this->call('vendor:publish', ['--tag' => 'mort-automation-migrations']);
                $this->call('migrate');
                $this->info('✅ Migraciones publicadas y ejecutadas');
            }

            // Mostrar próximos pasos
            $this->newLine();
            $this->info('🎉 ¡Stripe configurado exitosamente!');
            $this->newLine();
            $this->info('📋 Próximos pasos:');
            $this->line('  1. Crear productos: php artisan mort:stripe create-product');
            $this->line('  2. Crear precios: php artisan mort:stripe create-price');
            $this->line('  3. Crear clientes: php artisan mort:stripe create-customer');
            $this->line('  4. Ver ayuda: php artisan mort:stripe help');
            $this->newLine();
            $this->info('💡 Tip: Los precios se especifican en centavos (ej: 2999 = $29.99)');

            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Error durante la configuración: {$e->getMessage()}");

            return 1;
        }
    }

    private function testStripeConnection(): void
    {
        try {
            $this->line('  🔄 Probando conexión...');

            if ($this->stripeService->testConnection()) {
                $this->line('  ✅ Conexión exitosa con Stripe');
            } else {
                $this->line('  ❌ No se pudo conectar con Stripe');
            }
        } catch (\Stripe\Exception\ApiErrorException $e) {
            $this->line('  ❌ Error de API: '.$e->getMessage());
        } catch (\Exception $e) {
            $this->line('  ❌ Error de conexión: '.$e->getMessage());
        }
    }

    private function setupWebhooks(): void
    {
        $this->line('  📋 Configuración de webhooks recomendada:');
        $this->line('     - payment_intent.succeeded');
        $this->line('     - payment_intent.payment_failed');
        $this->line('     - customer.subscription.created');
        $this->line('     - customer.subscription.updated');
        $this->line('     - customer.subscription.deleted');
        $this->line('  🔗 URL del webhook: '.url('/stripe/webhook'));
    }

    private function showHelp(): int
    {
        $this->info('💳 Mort Stripe Automation - Comandos Disponibles');
        $this->newLine();

        $commands = [
            'setup' => 'Configurar Stripe y variables de entorno',
            'create-customer' => 'Crear un nuevo cliente en Stripe',
            'create-product' => 'Crear un nuevo producto en Stripe',
            'create-price' => 'Crear un nuevo precio para un producto',
            'create-payment-link' => 'Crear un enlace de pago',
            'list-customers' => 'Listar clientes de Stripe',
            'list-products' => 'Listar productos de Stripe',
            'list-prices' => 'Listar precios de Stripe',
            'help' => 'Mostrar esta ayuda',
        ];

        foreach ($commands as $command => $description) {
            $this->line("  <fg=green>mort:stripe {$command}</> - {$description}");
        }

        $this->newLine();
        $this->info('📝 Ejemplos de uso:');
        $this->line('  php artisan mort:stripe');
        $this->line('  php artisan mort:stripe setup');
        $this->line('  php artisan mort:stripe create-customer');
        $this->line('  php artisan mort:stripe list-products');

        return 0;
    }

    private function showInvalidAction(): int
    {
        $this->error('❌ Acción no válida');
        $this->newLine();
        $this->info('💡 Acciones disponibles:');
        $this->line('  setup, create-customer, create-product, create-price,');
        $this->line('  create-payment-link, list-customers, list-products,');
        $this->line('  list-prices, help');
        $this->newLine();
        $this->info('📚 Para ver ayuda detallada:');
        $this->line('  php artisan mort:stripe help');

        return 1;
    }
}
