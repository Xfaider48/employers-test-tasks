<?php


namespace App\Http\Controllers;


use App\Models\User;

/**
 * Class AbstractController
 *
 * @package App\Http\Controllers
 *
 * @property-read \App\Models\User $user
 */
abstract class AbstractController
{
    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        switch ($name) {
            case 'user':
                // no auth
                return User::query()->first();
        }

        throw new \InvalidArgumentException("Undefined property {$name}");
    }
}