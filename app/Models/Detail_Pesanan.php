<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model representing an item within an order.
 *
 * @property int $id
 * @property int $pesanan_id
 * @property int $menu_id
 * @property int $jumlah
 * @property int $harga
 * @property int $total
 * @property \App\Models\Pesanan $pesanan The order this item belongs to
 * @property \App\Models\Menu $menu The menu item ordered
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Detail_Pesanan where(string $column, mixed $value)
 * @method static \Illuminate\Database\Eloquent\Builder|Detail_Pesanan wherePesananId(int $value)
 * @method static \Illuminate\Database\Eloquent\Builder|Detail_Pesanan whereMenuId(int $value)
 * @method static \Illuminate\Database\Eloquent\Builder|Detail_Pesanan whereJumlah(int $value)
 * @method static Detail_Pesanan create(array $attributes = [])
 */
class Detail_Pesanan extends Model
{
    protected $table = 'detail_pesanan';
    protected $guarded = [];

    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'pesanan_id');
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id', 'id_menu');
    }
}
