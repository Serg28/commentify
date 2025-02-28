<?php

namespace Usamamuneerchaudhary\Commentify\Models;

use App\Models\OrderProducts;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Usamamuneerchaudhary\Commentify\Database\Factories\CommentFactory;
use Usamamuneerchaudhary\Commentify\Models\Presenters\CommentPresenter;
use Usamamuneerchaudhary\Commentify\Scopes\CommentScopes;
use Usamamuneerchaudhary\Commentify\Scopes\HasLikes;

class Comment extends Model
{

    use CommentScopes, SoftDeletes, HasFactory, HasLikes;

    /**
     * @var string
     */
    protected $table = 'comments';

    /**
     * @var string[]
     */
    protected $fillable = ['body', 'rating', 'name', 'email'];

    protected $withCount = [
        'likes',
    ];

    /**
     * @return CommentPresenter
     */
    public function presenter(): CommentPresenter
    {
        return new CommentPresenter($this);
    }

    /**
     * @return bool
     */
    public function isParent(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * @return BelongsTo
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany
     */
    public function children(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')->oldest();
    }

    /**
     * @return MorphTo
     */
    public function commentable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return CommentFactory
     */
    protected static function newFactory(): CommentFactory
    {
        return CommentFactory::new();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    /**
     * Проверяет, был ли товар, связанный с комментарием, куплен пользователем.
     *
     * @return bool Возвращает true, если товар куплен пользователем, иначе false.
     */
    public function isProductPurchasedByUser(): bool
    {
        // Проверяем, связан ли комментарий с товаром
        if ($this->commentable_type === 'product') {
            // Получаем ID товара, к которому относится комментарий
            $productId = $this->commentable_id;

            // Проверяем, есть ли у комментария пользователь
            if ($this->user_id) {
                // Проверяем, куплен ли товар пользователем хотя бы в одном заказе
                return OrderProducts::where('user_id', $this->user_id)
                    ->where('product_id', $productId)
                    ->exists();
            }
        }

        return false; // Если комментарий не связан с товаром или пользователь пустой
    }
}
