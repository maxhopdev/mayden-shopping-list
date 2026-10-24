<script setup>
import { computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

// Props passed from controller
const props = defineProps({
    items: Array,
    total: Number,
    spendingLimit: Number,
});

const money = (amount) => new Intl.NumberFormat('en-GB', { style: 'currency', currency: 'GBP' }).format(amount);

const numberInputClass = 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm';

const addForm = useForm({ name: '', quantity: 1, price: '' });

const addItem = () => {
    addForm.post(route('items.store'), {
        preserveScroll: true,
        onSuccess: () => addForm.reset(),
    });
};

const toggleItem = (item) => router.patch(route('items.toggle', item.id), {}, { preserveScroll: true });

const removeItem = (item) => router.delete(route('items.destroy', item.id), { preserveScroll: true });

const moveItem = (item, direction) => router.patch(route('items.move', item.id), { direction }, { preserveScroll: true });

const limitForm = useForm({ spending_limit: props.spendingLimit });

const saveLimit = () => limitForm.patch(route('spending-limit.update'), { preserveScroll: true });

const shareForm = useForm({ email: '' });

const shareList = () => {
    shareForm.post(route('share'), {
        preserveScroll: true,
        onSuccess: () => shareForm.reset(),
    });
};

const purchasedCount = computed(() => props.items.filter((item) => item.purchased).length);

const isOverLimit = computed(() => props.spendingLimit !== null && props.total > props.spendingLimit);
</script>

<template>
    <AppLayout title="Shopping List">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Shopping List
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Add item -->
                <form class="bg-white shadow sm:rounded-lg p-6" @submit.prevent="addItem">
                    <div class="flex flex-wrap gap-4 items-end">
                        <div class="flex-1 min-w-40">
                            <InputLabel for="name" value="Item" />
                            <TextInput id="name" v-model="addForm.name" type="text" class="mt-1 block w-full" placeholder="e.g. Milk" />
                        </div>
                        <div class="w-20">
                            <InputLabel for="quantity" value="Qty" />
                            <input id="quantity" v-model="addForm.quantity" type="number" min="1" :class="numberInputClass" class="mt-1 block w-full">
                        </div>
                        <div class="w-28">
                            <InputLabel for="price" value="Price (£)" />
                            <input id="price" v-model="addForm.price" type="number" min="0" step="0.01" :class="numberInputClass" class="mt-1 block w-full" placeholder="0.00">
                        </div>
                        <PrimaryButton :disabled="addForm.processing">
                            Add
                        </PrimaryButton>
                    </div>
                    <InputError :message="addForm.errors.name" class="mt-2" />
                    <InputError :message="addForm.errors.quantity" class="mt-2" />
                    <InputError :message="addForm.errors.price" class="mt-2" />
                </form>

                <!-- Items -->
                <div class="bg-white shadow sm:rounded-lg">
                    <p v-if="items.length === 0" class="p-6 text-gray-500">
                        Your shopping list is empty. Add something above.
                    </p>

                    <ul v-else class="divide-y divide-gray-100">
                        <li v-for="(item, index) in items" :key="item.id" class="flex items-center gap-4 px-6 py-3">
                            <Checkbox :checked="item.purchased" @update:checked="toggleItem(item)" />

                            <div class="flex-1" :class="{ 'line-through text-gray-400': item.purchased }">
                                <span class="font-medium">{{ item.name }}</span>
                                <span class="text-sm text-gray-500 ms-2">{{ item.quantity }} × {{ money(item.price) }}</span>
                            </div>

                            <span class="w-20 text-right" :class="{ 'line-through text-gray-400': item.purchased }">
                                {{ money(item.price * item.quantity) }}
                            </span>

                            <div class="flex gap-1">
                                <button type="button" class="px-2 text-gray-500 hover:text-gray-900 disabled:opacity-25" :disabled="index === 0" title="Move up" @click="moveItem(item, 'up')">
                                    ↑
                                </button>
                                <button type="button" class="px-2 text-gray-500 hover:text-gray-900 disabled:opacity-25" :disabled="index === items.length - 1" title="Move down" @click="moveItem(item, 'down')">
                                    ↓
                                </button>
                                <button type="button" class="px-2 text-red-500 hover:text-red-700" title="Remove" @click="removeItem(item)">
                                    ✕
                                </button>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Total and spending limit -->
                <div class="bg-white shadow sm:rounded-lg p-6 space-y-4">
                    <div class="flex justify-between text-lg">
                        <span>Total <span class="text-sm text-gray-500">({{ purchasedCount }} of {{ items.length }} picked up)</span></span>
                        <span class="font-semibold" :class="isOverLimit ? 'text-red-600' : 'text-gray-900'">{{ money(total) }}</span>
                    </div>

                    <div v-if="isOverLimit" class="rounded-md bg-red-100 border border-red-300 text-red-800 px-4 py-3 font-semibold">
                        Over budget by {{ money(total - spendingLimit) }}!
                    </div>
                    <div v-else-if="spendingLimit !== null" class="rounded-md bg-green-50 border border-green-200 text-green-800 px-4 py-3">
                        Within budget. {{ money(spendingLimit - total) }} left to spend.
                    </div>

                    <form class="flex items-end gap-4" @submit.prevent="saveLimit">
                        <div>
                            <InputLabel for="spending_limit" value="Spending limit (£)" />
                            <input id="spending_limit" v-model="limitForm.spending_limit" type="number" min="0" step="0.01" :class="numberInputClass" class="mt-1 block w-40" placeholder="No limit">
                        </div>
                        <PrimaryButton :disabled="limitForm.processing">
                            Save limit
                        </PrimaryButton>
                    </form>
                    <InputError :message="limitForm.errors.spending_limit" />
                </div>

                <!-- Share -->
                <form class="bg-white shadow sm:rounded-lg p-6" @submit.prevent="shareList">
                    <InputLabel for="email" value="Email this list to someone" />
                    <div class="flex gap-4 mt-1">
                        <TextInput id="email" v-model="shareForm.email" type="email" class="block flex-1" placeholder="partner@example.com" />
                        <PrimaryButton :disabled="shareForm.processing">
                            Send
                        </PrimaryButton>
                    </div>
                    <InputError :message="shareForm.errors.email" class="mt-2" />
                </form>
            </div>
        </div>
    </AppLayout>
</template>
