<?php

namespace App\Http\Requests;

/**
 * Mêmes règles qu'à la création : un appel corrigé doit rester aussi valide qu'un
 * appel enregistré. L'autorisation, elle, diffère — seul l'agent qui a traité
 * l'appel peut le modifier, ce que porte la CallPolicy.
 */
class UpdateCallRequest extends StoreCallRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('call'));
    }
}
