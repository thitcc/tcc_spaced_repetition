<script setup>
import { ref, onMounted } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import axios from 'axios';
import { defineProps } from 'vue';

const props = defineProps({
  user: Object,
});
const showingNavigationDropdown = ref(false);

const notifications = ref([]);
const unreadNotifications = ref([]);
const dropdownOpen = ref(false);
const modalOpen = ref(false);
const modalMessage = ref("");

const toggleDropdown = () => {
    dropdownOpen.value = !dropdownOpen.value;
};

const toggleModal = () => {
    modalOpen.value = !modalOpen.value;
};

const fetchNotifications = async () => {
    try {
        const response = await axios.get('/notifications');
        notifications.value = response.data;
        unreadNotifications.value = notifications.value.filter(notification => !notification.read_at);
    } catch (error) {
        console.error('Ocorreu um erro ao buscar as notificações.', error);
    }
};

const markAsRead = async (notificationId) => {
    try {
        await axios.post(`/notifications/${notificationId}/markAsRead`);
        fetchNotifications();
    } catch (error) {
        console.error('Ocorreu um erro ao marcar a notificação como lida.', error);
    }
};

const openDailyReviewModal = () => {
    modalMessage.value = "Você tem 5 minutinhos para fazer a revisão do dia?";
    modalOpen.value = true;
};

const showModalBasedOnLastLogin = () => {
    const lastLoginAt = props.user.last_login_at ? new Date(props.user.last_login_at) : null;
    const now = new Date();

    if (lastLoginAt && lastLoginAt.toDateString() !== now.toDateString()) {
        openDailyReviewModal();
    }
};

onMounted(() => {
    fetchNotifications();
    showModalBasedOnLastLogin();
});
</script>

<style>
.bell-icon-container {
    position: relative;
    margin-left: auto
}

.notification-dropdown {
    position: absolute;
    right: 0;
    top: 100%;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    width: 300px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    z-index: 1000;
}

.notification-item {
    padding: 10px;
    border-bottom: 1px solid #eee;
    cursor: pointer;
}

.notification-item div:last-child {
    margin-top: 5px;
    font-size: 0.8em;
}

.notification-item:hover {
    background-color: #f6f6f6;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
}

.modal {
  background: white;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  width: auto;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  margin-top: 20px;
}

.btn-blue {
  background-color: #3490dc;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  margin-right: 10px;
  cursor: pointer;
}

.btn-grey {
  background-color: #b8c2cc;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

</style>

<template>
    <div>
        <div class="min-h-screen bg-gray-100">
            <nav class="bg-white border-b border-gray-100">
                <!-- Primary Navigation Menu -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                    Início
                                </NavLink>
                            </div>
                        </div>

                          <!-- Modal -->
                        <div v-if="modalOpen" class="modal-overlay">
                            <div class="modal">
                                <p>{{ modalMessage }}</p>
                                <div class="modal-actions">
                                    <button class="btn-blue" @click="toggleModal">Sim</button>
                                    <button class="btn-grey" @click="toggleModal">Ignorar</button>
                                </div>
                            </div>
                        </div>

                        <div class="bell-icon-container mr-2 flex items-center">
                            <button @click="toggleDropdown">
                                <font-awesome-icon icon="bell" />
                                <span v-if="unreadNotifications.length">({{ unreadNotifications.length }})</span>
                            </button>
                            <div v-if="dropdownOpen" class="notification-dropdown">
                                <div v-for="notification in notifications" :key="notification.id" class="notification-item" @click="markAsRead(notification.id)">
                                    <div>{{ notification.content }}</div>
                                    <div class="text-sm text-gray-500">
                                        {{ new Date(notification.created_at).toLocaleDateString() }}
                                    </div>
                                </div>
                            </div>                        
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ms-6">
                            <!-- Settings Dropdown -->
                            <div class="ms-3 relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"
                                            >
                                                {{ $page.props.auth.user.name }}

                                                <svg
                                                    class="ms-2 -me-0.5 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>
                                    <template #content>
                                        <DropdownLink :href="route('profile.edit')"> Perfil </DropdownLink>
                                        <DropdownLink :href="route('logout')" method="post" as="button">
                                            Sair
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out"
                            >
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex': !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex': showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }"
                    class="sm:hidden"
                >
                    <div class="pt-2 pb-3 space-y-1">
                        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                            Dashboard
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-gray-200">
                        <div class="px-4">
                            <div class="font-medium text-base text-gray-800">
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="font-medium text-sm text-gray-500">{{ $page.props.auth.user.email }}</div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')"> Perfil </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('logout')" method="post" as="button">
                                Sair
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header class="bg-white shadow" v-if="$slots.header">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
