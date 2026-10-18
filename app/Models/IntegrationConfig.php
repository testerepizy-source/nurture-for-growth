<?php
namespace App\Models;
use Core\Model;

class IntegrationConfig extends Model
{
    protected static string $table = 'integration_configs';

    public static function byProvider(string $provider): ?array { return self::findBy('provider', $provider); }
}
