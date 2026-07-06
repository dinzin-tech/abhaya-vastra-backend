<?php

namespace App\Services;

/**
 * ShiprocketService stub.
 * This service handles shipment creation and tracking via the Shiprocket API.
 * Full implementation requires valid Shiprocket credentials in .env.
 */
class ShiprocketService
{
    public function __construct()
    {
        // Stub: credentials would be loaded from config here
    }

    public function createShipment(array $order): array
    {
        return ['status' => 'not_configured', 'message' => 'Shiprocket not configured.'];
    }

    public function trackShipment(string $awbCode): array
    {
        return ['status' => 'not_configured'];
    }

    public function cancelShipment(string $awbCode): bool
    {
        return false;
    }
}
