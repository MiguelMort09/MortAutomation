<?php

namespace Mort\Automation\Services;

use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class StripeService
{
    private ?StripeClient $stripe = null;

    public function __construct()
    {
        $this->initializeStripe();
    }

    /**
     * Inicializa el cliente de Stripe con las credenciales configuradas.
     */
    private function initializeStripe(): void
    {
        $secretKey = config('cashier.secret');

        if ($secretKey) {
            $this->stripe = new StripeClient($secretKey);
        }
    }

    /**
     * Verifica si Stripe está configurado correctamente.
     */
    public function isConfigured(): bool
    {
        return $this->stripe !== null;
    }

    /**
     * Crea un nuevo cliente en Stripe.
     *
     * @param array $data Datos del cliente (name, email, description, etc.)
     *
     * @throws ApiErrorException
     *
     * @return array Datos del cliente creado
     */
    public function createCustomer(array $data): array
    {
        if (! $this->isConfigured()) {
            throw new \RuntimeException('Stripe no está configurado. Ejecuta: php artisan mort:stripe setup');
        }

        $customer = $this->stripe->customers->create([
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
            'description' => $data['description'] ?? null,
            'metadata' => $data['metadata'] ?? [],
        ]);

        return [
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'created' => $customer->created,
        ];
    }

    /**
     * Crea un nuevo producto en Stripe.
     *
     * @param array $data Datos del producto (name, description, etc.)
     *
     * @throws ApiErrorException
     *
     * @return array Datos del producto creado
     */
    public function createProduct(array $data): array
    {
        if (! $this->isConfigured()) {
            throw new \RuntimeException('Stripe no está configurado. Ejecuta: php artisan mort:stripe setup');
        }

        $product = $this->stripe->products->create([
            'name' => $data['name'] ?? null,
            'description' => $data['description'] ?? null,
            'metadata' => $data['metadata'] ?? [],
        ]);

        return [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'created' => $product->created,
        ];
    }

    /**
     * Crea un nuevo precio para un producto en Stripe.
     *
     * @param array $data Datos del precio (product, amount, currency, etc.)
     *
     * @throws ApiErrorException
     *
     * @return array Datos del precio creado
     */
    public function createPrice(array $data): array
    {
        if (! $this->isConfigured()) {
            throw new \RuntimeException('Stripe no está configurado. Ejecuta: php artisan mort:stripe setup');
        }

        $priceData = [
            'product' => $data['product'],
            'unit_amount' => (int) $data['amount'],
            'currency' => $data['currency'] ?? 'usd',
        ];

        // Si es precio recurrente
        if (isset($data['recurring'])) {
            $priceData['recurring'] = [
                'interval' => $data['recurring']['interval'] ?? 'month',
            ];
        }

        $price = $this->stripe->prices->create($priceData);

        return [
            'id' => $price->id,
            'product' => $price->product,
            'amount' => $price->unit_amount,
            'currency' => $price->currency,
            'recurring' => $price->recurring ? [
                'interval' => $price->recurring->interval,
            ] : null,
        ];
    }

    /**
     * Crea un payment link para un precio en Stripe.
     *
     * @param array $data Datos del payment link (price, quantity, etc.)
     *
     * @throws ApiErrorException
     *
     * @return array Datos del payment link creado
     */
    public function createPaymentLink(array $data): array
    {
        if (! $this->isConfigured()) {
            throw new \RuntimeException('Stripe no está configurado. Ejecuta: php artisan mort:stripe setup');
        }

        $linkData = [
            'line_items' => [
                [
                    'price' => $data['price'],
                    'quantity' => (int) ($data['quantity'] ?? 1),
                ],
            ],
        ];

        if (isset($data['after_completion'])) {
            $linkData['after_completion'] = $data['after_completion'];
        }

        $paymentLink = $this->stripe->paymentLinks->create($linkData);

        return [
            'id' => $paymentLink->id,
            'url' => $paymentLink->url,
            'active' => $paymentLink->active,
        ];
    }

    /**
     * Lista clientes de Stripe.
     *
     * @param array $params Parámetros de búsqueda (limit, email, etc.)
     *
     * @throws ApiErrorException
     *
     * @return array Lista de clientes
     */
    public function listCustomers(array $params = []): array
    {
        if (! $this->isConfigured()) {
            throw new \RuntimeException('Stripe no está configurado. Ejecuta: php artisan mort:stripe setup');
        }

        $queryParams = [
            'limit' => $params['limit'] ?? 10,
        ];

        if (isset($params['email'])) {
            $queryParams['email'] = $params['email'];
        }

        $customers = $this->stripe->customers->all($queryParams);

        return array_map(function ($customer) {
            return [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'created' => $customer->created,
            ];
        }, $customers->data);
    }

    /**
     * Lista productos de Stripe.
     *
     * @param array $params Parámetros de búsqueda (limit, etc.)
     *
     * @throws ApiErrorException
     *
     * @return array Lista de productos
     */
    public function listProducts(array $params = []): array
    {
        if (! $this->isConfigured()) {
            throw new \RuntimeException('Stripe no está configurado. Ejecuta: php artisan mort:stripe setup');
        }

        $queryParams = [
            'limit' => $params['limit'] ?? 10,
        ];

        $products = $this->stripe->products->all($queryParams);

        return array_map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'created' => $product->created,
            ];
        }, $products->data);
    }

    /**
     * Lista precios de Stripe.
     *
     * @param array $params Parámetros de búsqueda (limit, product, etc.)
     *
     * @throws ApiErrorException
     *
     * @return array Lista de precios
     */
    public function listPrices(array $params = []): array
    {
        if (! $this->isConfigured()) {
            throw new \RuntimeException('Stripe no está configurado. Ejecuta: php artisan mort:stripe setup');
        }

        $queryParams = [
            'limit' => $params['limit'] ?? 10,
        ];

        if (isset($params['product'])) {
            $queryParams['product'] = $params['product'];
        }

        $prices = $this->stripe->prices->all($queryParams);

        return array_map(function ($price) {
            return [
                'id' => $price->id,
                'product' => $price->product,
                'amount' => $price->unit_amount,
                'currency' => $price->currency,
                'recurring' => $price->recurring ? [
                    'interval' => $price->recurring->interval,
                ] : null,
            ];
        }, $prices->data);
    }

    /**
     * Verifica la conexión con Stripe.
     *
     * @throws ApiErrorException
     */
    public function testConnection(): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        try {
            // Intentar obtener información de la cuenta
            $this->stripe->customers->all(['limit' => 1]);

            return true;
        } catch (ApiErrorException $e) {
            throw $e;
        }
    }
}
