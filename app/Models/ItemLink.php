<?php
    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;

    class ItemLink extends Model
    {
        use HasFactory;

        protected $table = 'item_links';
        
        protected $fillable = [
            'parent_itemid',
            'child_itemid',
            'link_type',
            'quantity',
            'active'
        ];

        protected $casts = [
            'parent_itemid' => 'string',
            'child_itemid' => 'string',
            'quantity' => 'integer',
            'active' => 'boolean'
        ];

        public function parentItem()
        {
            return $this->belongsTo(inventtables::class, 'parent_itemid', 'itemid');
        }

        public function childItem()
        {
            return $this->belongsTo(inventtables::class, 'child_itemid', 'itemid');
        }
    }