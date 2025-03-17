<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Discount extends Model
{
    use HasFactory;
    use LogsActivity;
    //    protected $connection = 'cs';

    /**
     * Log Activity Attributes
     *
     * @var array
     */
    protected static $logAttributes = [
        'name', 'started_at', 'expired_at', 'type', 'value', 'usage_limit', 'trial_days',
    ];

    /**
     * Log only when data changes
     *
     * @var bool
     */
    protected static $logOnlyDirty = true;

    /**
     * Log Activity name
     *
     * @var string
     */
    protected static $logName = 'discount_activity'; // Giá trị mặc định

    protected $fillable = [
        'name', 'started_at', 'expired_at', 'type', 'value', 'usage_limit', 'trial_days', 'discount_month', 'discount1',
    ];

    /**
     * Discounts table of App
     *
     * @var string
     */
    protected $table = 'discounts';
    protected $casts = [
        'started_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public static function changeLogName($logName)
    {
        self::$logName = $logName;
    }

    /**
     * Get log options for activity log
     */

    /**
     * Relationship: discounts -> coupons
     */
    public function coupon()
    {
        return $this->hasMany(Coupon::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(self::$logAttributes)
            ->useLogName(self::$logName);
    }
}
