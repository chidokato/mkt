<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'code', 'description', 'user_id', 'parent'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parentChannel()
    {
        return $this->belongsTo(Channel::class, 'parent');
    }

    public function children()
    {
        return $this->hasMany(Channel::class, 'parent');
    }
    /**
     * Lấy tất cả id của department con + chính nó
     */
    public static function getChildIds($id)
    {
        $ids = [$id];
        $children = self::where('parent', $id)->get();

        foreach ($children as $child) {
            $ids = array_merge($ids, self::getChildIds($child->id));
        }

        return $ids;
    }
}
