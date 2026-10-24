<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ShoppingListMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The user sharing their list.
     *
     * @var User
     */
    public $user;

    /**
     * The items on the list.
     *
     * @var Collection
     */
    public $items;

    /**
     * The total cost of the shop.
     *
     * @var float
     */
    public $total;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Collection $items, float $total)
    {
        $this->user = $user;
        $this->items = $items;
        $this->total = $total;
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->subject($this->user->name.' shared their shopping list with you')
            ->view('mail.shopping-list');
    }
}
