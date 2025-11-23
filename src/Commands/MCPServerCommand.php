<?php

namespace Mort\Automation\Commands;

use Illuminate\Console\Command;
use Mort\Automation\Services\StripeService;
use Throwable;

class MCPServerCommand extends Command
{
    protected $signature = 'mort:mcp-server';

    protected $description = 'Run the MCP Server for Mort Automation (Stripe Integration)';

    public function __construct(private StripeService $stripeService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        // Disable output buffering to ensure real-time communication
        if (ob_get_level()) {
            ob_end_clean();
        }

        $stdin = fopen('php://stdin', 'r');

        while (! feof($stdin)) {
            $line = fgets($stdin);
            if ($line === false) {
                break;
            }

            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            try {
                $request = json_decode($line, true, 512, JSON_THROW_ON_ERROR);
                $this->handleRequest($request);
            } catch (Throwable $e) {
                $this->sendError(null, -32700, 'Parse error: ' . $e->getMessage());
            }
        }

        fclose($stdin);

        return 0;
    }

    private function handleRequest(array $request): void
    {
        if (! isset($request['jsonrpc']) || $request['jsonrpc'] !== '2.0') {
            // Ignore non-JSON-RPC messages or invalid versions for now, or send error
            return;
        }

        $id = $request['id'] ?? null;
        $method = $request['method'] ?? '';
        $params = $request['params'] ?? [];

        try {
            $result = match ($method) {
                'initialize' => $this->handleInitialize($params),
                'notifications/initialized' => null, // No response needed
                'tools/list' => $this->handleListTools(),
                'tools/call' => $this->handleCallTool($params),
                default => throw new \Exception("Method not found: $method", -32601),
            };

            if ($id !== null && $method !== 'notifications/initialized') {
                $this->sendResponse($id, $result);
            }
        } catch (\Exception $e) {
            if ($id !== null) {
                $this->sendError($id, $e->getCode() ?: -32603, $e->getMessage());
            }
        }
    }

    private function handleInitialize(array $params): array
    {
        return [
            'protocolVersion' => '2024-11-05',
            'capabilities' => [
                'tools' => [
                    'listChanged' => false,
                ],
            ],
            'serverInfo' => [
                'name' => 'mort-automation-mcp',
                'version' => '1.0.0',
            ],
        ];
    }

    private function handleListTools(): array
    {
        return [
            'tools' => [
                [
                    'name' => 'stripe_create_customer',
                    'description' => 'Create a new customer in Stripe',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => ['type' => 'string', 'description' => 'Customer name'],
                            'email' => ['type' => 'string', 'description' => 'Customer email'],
                            'description' => ['type' => 'string', 'description' => 'Customer description'],
                        ],
                        'required' => ['name'],
                    ],
                ],
                [
                    'name' => 'stripe_create_product',
                    'description' => 'Create a new product in Stripe',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => ['type' => 'string', 'description' => 'Product name'],
                            'description' => ['type' => 'string', 'description' => 'Product description'],
                        ],
                        'required' => ['name'],
                    ],
                ],
                [
                    'name' => 'stripe_create_price',
                    'description' => 'Create a new price for a product in Stripe',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'product' => ['type' => 'string', 'description' => 'Product ID'],
                            'amount' => ['type' => 'integer', 'description' => 'Amount in cents'],
                            'currency' => ['type' => 'string', 'description' => 'Currency code (e.g., usd)'],
                            'interval' => ['type' => 'string', 'enum' => ['day', 'week', 'month', 'year'], 'description' => 'Recurring interval'],
                        ],
                        'required' => ['product', 'amount', 'currency'],
                    ],
                ],
                [
                    'name' => 'stripe_create_payment_link',
                    'description' => 'Create a payment link for a price',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'price' => ['type' => 'string', 'description' => 'Price ID'],
                            'quantity' => ['type' => 'integer', 'description' => 'Quantity'],
                            'redirect_url' => ['type' => 'string', 'description' => 'Redirect URL after payment'],
                        ],
                        'required' => ['price'],
                    ],
                ],
                [
                    'name' => 'stripe_list_customers',
                    'description' => 'List customers from Stripe',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'limit' => ['type' => 'integer', 'description' => 'Number of results'],
                            'email' => ['type' => 'string', 'description' => 'Filter by email'],
                        ],
                    ],
                ],
                [
                    'name' => 'stripe_list_products',
                    'description' => 'List products from Stripe',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'limit' => ['type' => 'integer', 'description' => 'Number of results'],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function handleCallTool(array $params): array
    {
        $name = $params['name'];
        $args = $params['arguments'] ?? [];

        try {
            $result = match ($name) {
                'stripe_create_customer' => $this->stripeService->createCustomer($args),
                'stripe_create_product' => $this->stripeService->createProduct($args),
                'stripe_create_price' => $this->handleCreatePrice($args),
                'stripe_create_payment_link' => $this->handleCreatePaymentLink($args),
                'stripe_list_customers' => $this->stripeService->listCustomers($args),
                'stripe_list_products' => $this->stripeService->listProducts($args),
                default => throw new \Exception("Tool not found: $name", -32601),
            };

            return [
                'content' => [
                    [
                        'type' => 'text',
                        'text' => json_encode($result, JSON_PRETTY_PRINT),
                    ],
                ],
            ];
        } catch (\Exception $e) {
            return [
                'content' => [
                    [
                        'type' => 'text',
                        'text' => "Error: " . $e->getMessage(),
                    ],
                ],
                'isError' => true,
            ];
        }
    }

    private function handleCreatePrice(array $args): array
    {
        $data = [
            'product' => $args['product'],
            'amount' => $args['amount'],
            'currency' => $args['currency'],
        ];

        if (isset($args['interval'])) {
            $data['recurring'] = ['interval' => $args['interval']];
        }

        return $this->stripeService->createPrice($data);
    }

    private function handleCreatePaymentLink(array $args): array
    {
        $data = [
            'price' => $args['price'],
            'quantity' => $args['quantity'] ?? 1,
        ];

        if (isset($args['redirect_url'])) {
            $data['after_completion'] = [
                'type' => 'redirect',
                'redirect' => ['url' => $args['redirect_url']],
            ];
        }

        return $this->stripeService->createPaymentLink($data);
    }

    private function sendResponse($id, $result): void
    {
        $response = [
            'jsonrpc' => '2.0',
            'id' => $id,
            'result' => $result,
        ];

        $this->sendJson($response);
    }

    private function sendError($id, $code, $message): void
    {
        $response = [
            'jsonrpc' => '2.0',
            'id' => $id,
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ];

        $this->sendJson($response);
    }

    private function sendJson(array $data): void
    {
        // Write to stdout directly
        fwrite(STDOUT, json_encode($data) . "\n");
        fflush(STDOUT);
    }
}
