<?php

namespace App\Http\Controllers;

use App\Mail\ShoppingListMail;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class ShoppingListController extends Controller
{
    /**
     * Show the logged in user's shopping list.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $items = $user->items()->get();

        return Inertia::render('ShoppingList', [
            'items' => $items,
            'total' => $user->shoppingListTotal(),
            'spendingLimit' => $user->spending_limit,
        ]);
    }

    /**
     * Add an item to the end of the shopping list.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($user) {
                    $alreadyOnList = $user->items()
                        ->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($value))])
                        ->exists();

                    if ($alreadyOnList) {
                        $fail('That item is already on your list.');
                    }
                },
            ],
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999'],
        ]);

        $validated['name'] = trim($validated['name']);

        // Max will get the last position on the list, putting the new one to the end
        $validated['position'] = $user->items()->max('position') + 1;

        $user->items()->create($validated);

        return back();
    }

    /**
     * Cross an item off the list, or put it back on.
     */
    public function toggle(Request $request, Item $item)
    {
        $this->authorizeItem($request, $item);

        $item->update(['purchased' => ! $item->purchased]);

        return back();
    }

    /**
     * Remove an item from the list.
     */
    public function destroy(Request $request, Item $item)
    {
        $this->authorizeItem($request, $item);

        $item->delete();

        return back();
    }

    /**
     * Swap an item with the one above or below it.
     */
    public function move(Request $request, Item $item)
    {
        $this->authorizeItem($request, $item);

        // @TODO - Move validation into a request
        $request->validate([
            // Must be 'up' or 'down'
            'direction' => ['required', 'in:up,down'],
        ]);

        // Reorder resets the order
        $query = $request->user()->items()->reorder();

        // Find that neighbour that will be affected by the move
        if ($request->input('direction') === 'up') {
            $neighbour = $query->where('position', '<', $item->position)->orderByDesc('position')->first();
        } else {
            $neighbour = $query->where('position', '>', $item->position)->orderBy('position')->first();
        }

        if ($neighbour) {
            // Get the item current position
            $itemPosition = $item->position;

            // Swap the item's position with it's neighbour
            $item->update(['position' => $neighbour->position]);
            $neighbour->update(['position' => $itemPosition]);
        }

        return back();
    }

    /**
     * Set or clear the spending limit for the shop.
     */
    public function updateLimit(Request $request)
    {
        // @TODO - Move validation into a request
        $validated = $request->validate([
            'spending_limit' => ['nullable', 'numeric', 'min:0', 'max:999999'],
        ]);

        $request->user()->update(['spending_limit' => $validated['spending_limit']]);

        return back();
    }

    /**
     * Email the shopping list to someone else.
     */
    public function share(Request $request)
    {
        // @TODO - Move validation into a request
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = $request->user();
        $items = $user->items()->get();

        Mail::to($validated['email'])->send(new ShoppingListMail($user, $items, $user->shoppingListTotal()));

        return back()->with('flash.banner', 'Shopping list sent to '.$validated['email'].'.');
    }

    /**
     * Stop users from changing items on someone else's list.
     */
    private function authorizeItem(Request $request, Item $item)
    {
        // @TODO - Move this into a request
        abort_unless($item->user_id === $request->user()->id, 403);
    }
}
