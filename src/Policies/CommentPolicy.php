<?php

namespace Usamamuneerchaudhary\Commentify\Policies;

use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Usamamuneerchaudhary\Commentify\Models\Comment;

class CommentPolicy
{
    use HandlesAuthorization;

    /**
     * @param $user
     * @param  Comment  $comment
     * @return Response
     */
    public function update($user, Comment $comment): Response
    {
        $user = $user ?: Sentinel::check();
        return $user->id === $comment->user_id
            ? Response::allow()
            : Response::denyWithStatus(401);
    }


    /**
     * @param $user
     * @param  Comment  $comment
     * @return Response
     */
    public function destroy($user, Comment $comment): Response
    {
        $user = $user ?: Sentinel::check();
        return $user->id === $comment->user_id
            ? Response::allow()
            : Response::denyWithStatus(401);
    }
}
