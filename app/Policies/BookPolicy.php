<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BookPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool { return true; }
    public function view(User $user, Book $book): bool { return true; }

    public function create(User $user): bool { return $user->hasPermissionTo('gestionar libros'); }
    public function update(User $user, Book $book): bool { return $user->hasPermissionTo('gestionar libros'); }
    public function delete(User $user, Book $book): bool { return $user->hasPermissionTo('gestionar libros'); }
}