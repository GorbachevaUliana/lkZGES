<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tariff extends Model
{
    protected $fillable = ['name', 'price_1', 'price_2', 'price_3', 'starts_at', 'ends_at', 'coefficient'];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at' => 'date',
    ];

    /**
     * Расчет стоимости потребления по ступеням
     */
    public function calculateCost($consumed)
    {
        if ($consumed <= 0) {
            return 0;
        }

        $sum = 0;
        $step1_limit = 3900;
        $step2_limit = 6000;

        // 1 диапазон (до 3900)
        if ($consumed <= $step1_limit) {
            $sum = $consumed * $this->price_1;
        }
        // 2 диапазон (от 3901 до 6000)
        elseif ($consumed <= $step2_limit) {
            $sum = ($step1_limit * $this->price_1) +
                   (($consumed - $step1_limit) * $this->price_2);
        }
        // 3 диапазон (свыше 6000)
        else {
            $sum = ($step1_limit * $this->price_1) +
                   (($step2_limit - $step1_limit) * $this->price_2) +
                   (($consumed - $step2_limit) * $this->price_3);
        }

        return round($sum, 2);
    }
}
