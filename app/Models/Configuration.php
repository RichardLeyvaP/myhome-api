<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{
    use HasFactory;

    // Define los campos que pueden ser asignados masivamente
    protected $fillable = [
        'appName',
        'appVersion',
        'language',
        'defaultCurrency',
        'themeColor',
        'backgroundColor',
        'textColor',
        'buttonColor',
        'isDarkModeEnabled',
        'notificationsEnabled',
        'apiEndpoint',
        'connectionTimeout',
        'retryAttempts',
        'useBiometricAuth',
        'requirePinForSensitiveActions',
        'storagePath',
        'maxCacheSize',
        'autoUpdateEnabled',
        'supportContactEmail',
        'lastSyncTime',
        'fontSize',
        'isDefault',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
