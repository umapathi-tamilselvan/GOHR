<?php

namespace App\Models;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Designation extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public static $HUMAN_RESOURCES = 'Human Resources';
    public static $ADMINISTRATION = 'Administration';
    public static $DEVELOPER = 'Developer';
    public static $DESIGNER = 'Designer';
    public static $MARKETING = 'Marketing';
    public static $SALES = 'Sales';
    public static $CUSTOMER_SERVICE = 'Customer Service';
    public static $QA_MANAGER = 'QA Manager';
    public static $QA_TEAM_LEAD = 'QA Team Lead';
    public static $QA_TEAM_MEMBER = 'QA Team Member';

    
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public static function getDefaults()
    {
        return [
            ['name' => self::$HUMAN_RESOURCES],
            ['name' => self::$ADMINISTRATION],
            ['name' => self::$DEVELOPER],
            ['name' => self::$DESIGNER],
            ['name' => self::$MARKETING],
            ['name' => self::$SALES],
            ['name' => self::$CUSTOMER_SERVICE],
            ['name' => self::$QA_MANAGER],
            ['name' => self::$QA_TEAM_LEAD],
            ['name' => self::$QA_TEAM_MEMBER],
        ];
    }
}
