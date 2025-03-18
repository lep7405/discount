<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Coupon extends Model
{
    use HasFactory;
    use LogsActivity;
    protected static $logAttributes = ['code', 'shop', 'discount_id', 'times_used', 'status'];

    protected static $logOnlyDirty = true;

    protected static $logName = 'coupon_activity';

    protected $table = 'coupons';

    protected $fillable = ['code', 'shop', 'discount_id', 'times_used', 'status', 'automatic'];

    public static function changeLogName($log_name)
    {
        self::$logName = $log_name;
    }

    public function discount()
    {
        return $this->belongsTo(Discount::class, 'discount_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(self::$logAttributes)
            ->useLogName(self::$logName);
    }
}
