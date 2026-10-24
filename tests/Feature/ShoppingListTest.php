<?php

namespace Tests\Feature;

use App\Mail\ShoppingListMail;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ShoppingListTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_user_sees_their_own_items_in_order_with_total(): void
    {
        $user = User::factory()->create(['spending_limit' => 5]);
        Item::factory()->for($user)->create(['name' => 'Bread', 'position' => 2, 'price' => 1.50, 'quantity' => 2]);
        Item::factory()->for($user)->create(['name' => 'Milk', 'position' => 1, 'price' => 1.25, 'quantity' => 1]);
        Item::factory()->create(['name' => 'Someone elses']);

        $this->actingAs($user)->get('/')->assertInertia(function (Assert $page) {
            $page->component('ShoppingList')
                ->has('items', 2)
                ->where('items.0.name', 'Milk')
                ->where('items.1.name', 'Bread')
                ->where('total', 4.25)
                ->where('spendingLimit', 5);
        });
    }

    public function test_user_can_add_an_item(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/items', ['name' => 'Eggs', 'quantity' => 12, 'price' => 0.25])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('items', ['user_id' => $user->id, 'name' => 'Eggs', 'quantity' => 12, 'position' => 1]);
    }

    public function test_user_cannot_add_an_item_already_on_their_list(): void
    {
        $user = User::factory()->create();
        Item::factory()->for($user)->create(['name' => 'Eggs']);

        $this->actingAs($user)->post('/items', ['name' => ' eggs ', 'quantity' => 1, 'price' => 1])
            ->assertSessionHasErrors('name');

        $this->assertSame(1, $user->items()->count());
    }

    public function test_user_can_cross_off_and_remove_an_item(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->for($user)->create();

        $this->actingAs($user)->patch('/items/'.$item->id);
        $this->assertTrue($item->fresh()->purchased);

        $this->actingAs($user)->delete('/items/'.$item->id);
        $this->assertModelMissing($item);
    }

    public function test_user_can_move_an_item(): void
    {
        $user = User::factory()->create();
        $first = Item::factory()->for($user)->create(['position' => 1]);
        $second = Item::factory()->for($user)->create(['position' => 2]);

        $this->actingAs($user)->patch('/items/'.$second->id.'/move', ['direction' => 'up']);

        $this->assertSame(1, $second->fresh()->position);
        $this->assertSame(2, $first->fresh()->position);
    }

    public function test_user_cannot_change_another_users_item(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $this->actingAs($user)->patch('/items/'.$item->id)->assertForbidden();
        $this->actingAs($user)->delete('/items/'.$item->id)->assertForbidden();
        $this->assertModelExists($item);
    }

    public function test_user_can_set_a_spending_limit(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch('/spending-limit', ['spending_limit' => 50]);

        $this->assertEquals(50, $user->fresh()->spending_limit);
    }

    public function test_user_can_email_their_list(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        Item::factory()->for($user)->create(['name' => 'Bananas']);

        $this->actingAs($user)->post('/share', ['email' => 'partner@example.com'])
            ->assertSessionHasNoErrors();

        Mail::assertSent(ShoppingListMail::class, function (ShoppingListMail $mail) {
            return $mail->hasTo('partner@example.com') && str_contains($mail->render(), 'Bananas');
        });
    }
}
