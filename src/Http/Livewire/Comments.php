<?php

namespace Usamamuneerchaudhary\Commentify\Http\Livewire;


use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Comments extends Component
{
    use WithPagination;

    public $model;

    public $users = [];

    public $showDropdown = false;

    protected $numberOfPaginatorsRendered = [];

    public $newCommentState = [
        'body' => '',
        'name' => '',
        'email' => ''
    ];

    protected $listeners = [
        'refresh' => '$refresh'
    ];

    protected $validationAttributes = [
        'newCommentState.body' => 'comment'
    ];

    protected string $paginationTheme = 'simple-bootstrap-commentify';

    /**
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application|null
     */
    public function render(
    ): \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application|null
    {

        $this->newCommentState['name'] = $this->newCommentState['name'] ?: app('user')?->first_name ?? '';
        $this->newCommentState['email'] = $this->newCommentState['email'] ?: app('user')?->email ?? '';

        $comments = $this->model
            ->comments()->where('is_active', 1)
            ->with('user', 'children.user', 'children.children')
            ->parent()
            ->latest()
            ->paginate(config('commentify.pagination_count',10));

        return view('commentify::livewire.comments', [
            'comments' => $comments
        ]);
    }

    /**
     * @return void
     */
    #[On('refresh')]
    public function postComment(): void
    {
        $this->validate([
            'newCommentState.body' => 'required',
            'newCommentState.email' => 'required',
            'newCommentState.name' => 'required',
        ]);

        $comment = $this->model->comments()->make($this->newCommentState);
        $comment->user()->associate(app('user'));
        $comment->save();

        $this->newCommentState = [
            'body' => ''
        ];
        $this->users = [];
        $this->showDropdown = false;

        $this->resetPage();
        session()->flash('message', 'Комментарий успешно добавлен и будет опубликован после одобрения модератором');
        $this->notify(__t("Комментарий успешно добавлен и будет опубликован после одобрения модератором"), __t('Спасибо'), 'success');
    }

}
