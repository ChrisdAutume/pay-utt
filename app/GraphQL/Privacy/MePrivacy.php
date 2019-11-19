<?php
use Auth;
use Rebing\GraphQL\Support\Privacy;

class MePrivacy extends Privacy
{
    public function validate(array $queryArgs): bool
    {
        if(\Illuminate\Support\Facades\Auth::user() instanceof  \App\Models\Service )
        {
            return $queryArgs['id'] == Auth::id();
        }

        return false;
    }
}
