<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Generate extends Model
{
    use HasFactory;
    use LogsActivity;

    protected static $logOnlyDirty = true;

    protected static $logName = 'generate_activity';

    protected static $logAttributes = [
        'app_name', 'discount_id', 'conditions', 'expired_range', 'limit', 'header_message', 'success_message', 'used_message', 'fail_message', 'app_url',
    ];

    protected $table = 'generates';

    protected $fillable = ['app_name', 'discount_id', 'conditions', 'expired_range', 'limit', 'header_message', 'success_message', 'used_message', 'fail_message', 'app_url'];
    protected $casts = [
        'conditions' => 'json',
        'success_message' => 'json',
        'fail_message' => 'json',

    ];

    public static function changeLogName($logName)
    {
        self::$logName = $logName;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(self::$logAttributes)
            ->useLogName(self::$logName);
    }
}
